<?php

namespace App\Controllers;

use Core\Controller;
use \Core\View;
use \App\Models\User; // da ne bi moralo da se navodi ceo namespace kada se poziva metoda iz klase User
use App\Auth;
use App\Flash;
// use App\Mail; koristeno samo za testiranje slanje maila Mail::send();



class Login extends Controller
{
    public function newAction()
    {
        View::renderTemplate('Login/new.html');
    }

    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        $ip = $_POST['ip'];

        $remember_me = isset($_POST['remember_me']);

        if ($user) {
            Auth::login($user, $remember_me);

            // Mail::send(); // stavljeno samo da bi se isporbalo automatsko slanje mailova

            Flash::addMessage("Login successful.\n Logged from: $ip "); // nema drugog arg za tip poruke jer je u Flash.php podeseno da po defaultu to success
            
            $this->redirect(Auth::getReturnToPage()); // ako je pronadjen user u bazi tj. ako je izvrseno logovanje, vrsimo redirect na trazenu stranicu koja je prethodno memorisana u session file ili na homepage ako nije. isto sto i Controller::redirect('/');
            
        } else {
            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING); // drugim arg prosledjujemo tip poruke

            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email'], // prosledjujemo email i da li je odabrano da korisnik bude zapamcen kako ne bi morao da unosi ponove te informacije
                'remember_me' => $remember_me
            ]); // ako usera nema u bazi ponovo ucitavamo login stranicu
        }
    }

    public function destroyAction() // kod je kopiran iz php dokumentacije: https://www.php.net/manual/en/function.session-destroy.php
    {                               // ovo je metod kojim izlogujemo korisnika
        Auth::logout();

        $this->redirect('/login/show-logout-message'); // ovde smo morali da napravimo nov url request kako bi se generisao novi session sto omogucuje da se upise flash message 
                                                       // da smo uspesno izlogovani, a sto ovde ne bi bilo moguce jer je prilikom Auth::logout() izvrsen session destroy i nema session file vise
    }

    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successful');

        $this->redirect('/'); // nakon sto je korisnik izlogovan vracamo se na homepage
    }
}
