<?php

namespace App\Models;

use App\Token;
use PDO, PDOException;
use App\Mail;
use Core\View;

/**
 * Post model
 *
 * PHP version 5.4
 */
class User extends \Core\Model
{
    public $errors = [];

    public function __construct($data = []) // izvrsava se kada se instancira novi $user objekat u Signup.php 
    // dajuci mu vrednost praznog niza nacinili smo argument opcionalnim jer kada se kreira objekat prilikom sign up-a prosledjuju se podaci, a kada se kreira prilikom login-a iz baze onda nema argumenta
    {
        foreach ($data as $key => $value) { // iterira objekat koji sadrzi parametre pretrage kao asocijativni niz key-value pairs 
            $this->$key = $value; // i memorise ih kao propertije klase User
        };
    }
    public function save()
    {
        // $host = 'localhost';  // zakomentarisano jer je konektovanje na bazu prebaceno u Core\Model.php
        // $dbname = 'mvc';
        // $username = 'root';
        // $password = '';

        $this->validate(); // prvo validiramo korisnicki unos

        if (empty($this->errors)) { // snimamo samo ukoliko nisu zabelezene greske 
            try {

                $password_hash = password_hash($this->password, PASSWORD_DEFAULT); // stvaramo password hash koji automatski dodaje i salt i to ce biti vrednost koju cemo cuvati na serveru
                $token = new Token();
                $hashed_token = $token->getHash(); // u bazi cuvamo samo hash-ovan token
                $this->activation_token = $token->getValue(); // token potreban za aktivaciju naloga koji ce biti poslat putem email-a ovde moramo uciniti da postane properti klase User (da bi se mogao koristiti iz metode sendActivationEmail() )
                $sql = 'INSERT INTO users (name, email, password_hash, activation_hash)
                        VALUES (:name, :email, :password_hash, :activation_hash)'; // ovde stavljamo placeholdere koji se oznacavaju sa : ispred, jer cemo koristiti prepared statements

                $db = static::getDB(); // posto class Post extends Model onda imamo ovakav poziv staticke metode bez navodjenja imena klase kojoj pripada
                $stmt = $db->prepare($sql);

                // moramo da bajndujemo vrednosti parametara requesta koji su prethodno sacuvani u User klasi za odgovarajuce placeholdere, tako se radi kada se koriste prepared statements
                $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
                $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

                return $stmt->execute(); // ujedno izvrsava snimanje u bazu ali i vraca true ako je uspelo i false ukoliko nije
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
            return false; // if validation failed
        }
    }
    public function validate()
    {
        if ($this->name == '') { // name
            $this->errors[] = 'Name is required';
        }
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) { // email
            $this->errors[] = 'Invalid email';
        }
        if (static::emailExists($this->email, $this->id ?? null)) { // drugi argument se salje samo ukoliko postoji, tj. ukoliko korisnik vec postoji u bazi (za slucaj kada se radi samo o promeni sifre)
            $this->errors[] = 'Email already taken';
        }
        if (isset($this->password)) { // da ne  validira password ukoliko se radi o editovanju profila bez unosenja nove sifre

            if (strlen($this->password) <= 6) { // pass lenght
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password must contain at least one letter';
            }
            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password must contain at least one number';
            }
        }
    }

    public static function emailExists($email, $ignore_id = null) // proverava da li vec postoji korisnik sa istim email-om, drugi parametar je opcionalan za slucajeve da se metod poziva prilikom promene samo sifre ili editovanja profila i predstavlja userid koji cemo ignorisati ako vec postoji
    {
        $user = static::findByEmail($email);
        if ($user) {
            if ($user->id != $ignore_id) {
                return true; // vraca true samo ako email postoji u bazi i pri tom se razlikuje od $ignore_id (jer u tom slucaju se radi samo o promeni sifre za vec postojeceg korisnika) 
            }
        }
        return false;
    }

    public static function findByEmail($email) // proverava da li vec postoji korisnik sa istim email-om
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        // moramo da bajndujemo vrednosti parametara requesta koji su prethodno sacuvani u User klasi za odgovarajuce placeholdere, tako se radi kada se koriste prepared statements
        $stmt->bindParam(':email', $email, PDO::PARAM_STR); // razlika izmedju bindValue i bindParam je sto bindParam salje vrednost tek u trenutku izvrsavanja $stmt->execute()
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // ovim cinimo da umesto niza sto je default bude napravljen objekat klase User sa podacima iz baze. Drugi argument pronalazi namespace za pozvanu klasu (User u ovom slucaju).
        $stmt->execute();
        return $stmt->fetch(); // fetch() vraca false ako nije pronadjeno nista u bazi
    }

    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);
        if ($user && $user->is_active) { // proverava i bool is_active koji je deo user recorda u bazi da vidi da li je nalog aktiviran posto je po defaultu prilikom kreiranja recorda u bazi setovan na 0 tj. false
            if (password_verify($password, $user->password_hash)) { // kada poredimo sa hashovanom sifrom u bazi koristimo password_verify umesto password_hash metodu
                return $user;
            }
        }
        return false;
    }

    public static function findByID($id)  // upit u bazu na osnovu user_id-a dobijenog prilikom prvog upita tj. prilikom logovanja
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        // moramo da bajndujemo vrednosti parametara requesta koji su prethodno sacuvani u User klasi za odgovarajuce placeholdere, tako se radi kada se koriste prepared statements
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // ovde koristimo PDO::PARAM_INT umesto PDO::PARAM_STR jer id je u bazi sacuvan kao broj
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // ovim cinimo da umesto niza sto je default bude napravljen objekat klase User sa podacima iz baze. Drugi argument pronalazi namespace za pozvanu klasu (User u ovom slucaju).
        $stmt->execute();
        return $stmt->fetch(); // fetch() vraca false ako nije pronadjeno nista u bazi
    }

    public function rememberLogin() // snimanje session tokena u bazu ukoliko je korisnik odabrao remember me opciju prilikom logovanja
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue(); // moramo da ga memorisemo kao properti klase User (tako sto stavljamo $this) kako bi nam bio dostupan i iz Auth.php
        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30; // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at) 
                VALUES (:token_hash, :user_id, :expires_at)'; // snima se u zasebnu tabelu remembered_logins zato sto je moguce da ce se cookies setovati na razlicitim uredjajima za istog korisnika

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR); // za snimanje u sql timpestamp se mora konvertovati u datum u ovom obliku

        return $stmt->execute();
    }

    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);
        if ($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
            };
        }
    }

    protected function startPasswordReset()
    {
        $token = new Token();
        $this->password_reset_token = $token->getValue(); // da bi kao obj property bilo dostupno i dole u sendPasswordResetEmail() salje se korisniku putem mail-a
        $hashed_token = $token->getHash(); // u bazi cuvamo samo ovu hashovanu verziju tokena
        $expiry_timestamp = time() + 60 * 60 * 2; // 2hr from now

        $sql = 'UPDATE users
               SET password_reset_hash = :token_hash,
                   password_reset_expires_at = :expires_at
               WHERE id = :id'; // useru prethodno identifikovanom po poslatom mailu popunjava i polja u kojima se cuva pass reset hashed token i vreme njegovog isticanja

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token; // kreiramo url koji cemo poslati korisniku na mail i koji ce imate otprilike ovakav izgled :
        // http://localhost/password/reset/4523ade1ef20c01fe7cd35c318db45dd
        // sledeca dva reda su zakomentarisani, jer to je (legitiman) hardcoded nacin, ali mi smo posle upotrebili twig template i View klasu za kreiranje teksta i html-a za slanje email-om
        //$text = "Please click on the following link to reset your password: $url"; // ovo ima u tutorijalu ali se ovde u pepipost ne koristi
        //$html = "Please click <a href=\"$url\">here</a> to reset your password";
        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]); // prosledjujemo i url kako bio dostupan twig-u
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]); // prosledjujemo i url kako bio dostupan twig-u

        Mail::send($this->email, "Password reset", $text, $html); // $text prosledjujemo ali za razliku od mailgun-a koji se koristi u kursu, pepipost koji mi koristimo ne zahteva text, vec samo html
    }

    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash(); // da bismo pronasli token u bazi moramo prvo da ga hashujemo jer se hashovan i cuva u bazi

        $sql = 'SELECT * FROM users
               WHERE password_reset_hash = :token_hash'; // vraca samo one korisnike kod kojih se sacuvani token hash poklapa sa onim koji je stigao putem mail-a

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // ovim cinimo da umesto niza sto je default bude napravljen objekat klase User sa podacima iz baze. Drugi argument pronalazi namespace za pozvanu klasu (User u ovom slucaju).
        $stmt->execute();
        $user = $stmt->fetch(); // fetch() vraca false ako nije pronadjeno nista u bazi

        if ($user) {
            if (strtotime($user->password_reset_expires_at) > time()) { // samo ako token nije istekao vraca $user objekat
                return $user;
            }
        } // ukoliko ne vracamo nista eksplicitno u php-u se po defaultu vraca NULL
    }

    public function resetPassword($password)
    {
        $this->password = $password; // ovim samo menjamo password property u objektu $user koji je prethodno instanciran i to tako sto je nakon upita u bazu rezultat umesto niza kreirao objekat klase User zahvaljujuci $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()) u ovdasnjoj metodi findByPasswordReset()
        $this->validate(); // kao i kod kreiranja usera vrsimo backend validaciju unete sifre
        if (empty($this->errors)) { // update user record (true ako je niz errors prazan)

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT); // stvaramo password hash koji automatski dodaje i salt i to ce biti vrednost koju cemo cuvati na serveru
            $sql = 'UPDATE users
                        SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                        WHERE id = :id'; // update-ujemo password, i istovremeno ponustavamo (setujemo na NULL) vrednosti hashovanog tokena za reset i vreme njegovog isticanja

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            return $stmt->execute();
        }

        return false; // when validation fails & we don't update the record
    }

    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]); // prosledjujemo i url kako bio dostupan twig-u
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]); // prosledjujemo i url kako bio dostupan twig-u

        Mail::send($this->email, "Account activation", $text, $html); // $text prosledjujemo ali za razliku od mailgun-a koji se koristi u kursu, pepipost koji mi koristimo ne zahteva text, vec samo html
    }

    public static function activate($value) // aktiviranje naloga (izmenom is_active atributa u bazi) sa prosledjenim tokenom (putem klika na link poslat za aktivaciju na mail) kroz parametar $value
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();
        $sql = 'UPDATE users
                        SET is_active = 1,
                        activation_hash = NULL
                        WHERE activation_hash = :hashed_token'; // pronalazi korisnika po hash-u token poslatog na mail, menja is_active na 1 (tj. true) i ponistava activation_hash na NULL

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateProfile($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        if ($data['password'] != '') { // samo ako je uneta nova sifra
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {
            $sql = 'UPDATE users
                    SET name = :name,
                        email = :email';

            if (isset($this->password)) {    // dodaje u sql statement deo za update passworda samo ako je unet novi password         
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            if (isset($this->password)) { // opet, samo ako je unet novi password u editovanju profila
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        return false; // ako je doslo do greske u validaciji
    }

    public function updatePreferredKeywords($keywords)
    {
        $sql = 'UPDATE users
                SET preferred_keywords = :preferred_keywords
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':preferred_keywords', $keywords, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function readPreferredKeywords()
    {
        $sql = 'SELECT preferred_keywords FROM users
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateClickedTitles($titles)
    {
        $sql = 'UPDATE users
                SET clicked_titles = :clicked_titles
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':clicked_titles', $titles, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function readClickedTitles()
    {
        $sql = 'SELECT clicked_titles FROM users
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch();
    }
}
