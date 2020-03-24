<?php

namespace App\Models;

use App\Token;
use PDO;

class RememberedLogin extends \Core\Model
{
    public static function findByToken($token)
    {
        $token = new Token($token);
        $token_hash = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins
                WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        // moramo da bajndujemo vrednosti parametara requesta koji su prethodno sacuvani u User klasi za odgovarajuce placeholdere, tako se radi kada se koriste prepared statements
        $stmt->bindParam(':token_hash', $token_hash, PDO::PARAM_STR); // ovde koristimo PDO::PARAM_INT umesto PDO::PARAM_STR jer id je u bazi sacuvan kao broj
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // ovim cinimo da umesto niza sto je default bude napravljen objekat klase User sa podacima iz baze. Drugi argument pronalazi namespace za pozvanu klasu (User u ovom slucaju).
        $stmt->execute();
        return $stmt->fetch(); // fetch() vraca false ako nije pronadjeno nista u bazi
    }

    public function getUser()
    {
        return User::findByID($this->user_id);
    }

    public function hasExpired()
    {
        return strtotime($this->expires_at) < time(); // konvertujemo podatke o vremenu isticanja tokena iz baze koji dolaze u stringu u time i poredimo ih sa trenutnom time() vrednosti
    }

    public function delete() // u bazi brisemo token koji smo dobili iz browser cookie-a kako bi bio moguc logout
    {
        $sql = 'DELETE FROM remembered_logins
              WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        // moramo da bajndujemo vrednosti parametara requesta koji su prethodno sacuvani u User klasi za odgovarajuce placeholdere, tako se radi kada se koriste prepared statements
        $stmt->bindValue(':token_hash', $this->token_hash, PDO::PARAM_STR); // ovde koristimo PDO::PARAM_INT umesto PDO::PARAM_STR jer id je u bazi sacuvan kao broj
        $stmt->execute();
    }
}
