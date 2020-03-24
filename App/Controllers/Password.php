<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

class Password extends \Core\Controller
{
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.html'); 
    }

    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']); // prethodno nismo radili ni frontend ni backend validaciju jer nema nuzne potrebe za tim kada je email u pitanju, tek kada kasnije korisnik bude uneo novu sifru radimo validacije
        View::renderTemplate('Password/reset_requested.html'); // samo prikazuje poruku da je zahtev prihvacen
    }

    public function resetAction()
    {
        $token = $this->route_params['token']; // dostupnost hexadecimalnog tokena sa kraja url-a je omogucena dodavanjem posebne rute u index.php
        
        $user = $this->getUserOrExit($token);
        
        View::renderTemplate('Password/reset.html', [ // izvrsava se samo ako je pronadjen user posto je inace izvrsen exit u metodi getUserOrExit
                'token' => $token // prosledjuje se token u forntend putem twiga kako bi onda prilikom submitovanja forme za reset passworda bio poslat ovdasnjoj metodi resetPasswordAction() 
        ]);
        
    }

    public function resetPasswordAction() 
    {
        $token = $_POST['token']; // stiglo iz skrivenog input polja u formi za reset u frontendu

        $user = $this->getUserOrExit($token); // ponovo trazimo usera u bazi sa odgovarajucim tokenom
        
        if ($user->resetPassword($_POST['password'])) { // true je ako izvrsena metoda rezultira nizom errors koji je prazan
            View::renderTemplate('Password/reset_success.html');
        } else {
            View::renderTemplate('Password/reset.html', [ 
                'token' => $token,
                'user'  => $user // ovaj put prosledjujemo i user objekat za ponovni pokusaj reseta
        ]);
        }

    }

    protected function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);
        if($user){
            return $user;
        } else { // u slucaju da je istekao token ili da nije ni pronadjen u bazi
            View::renderTemplate('Password/token_expired.html');
            exit;
        };
    }
}