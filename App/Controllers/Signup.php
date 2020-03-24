<?php

namespace App\Controllers; // namespace koji korespondira sa organizacijom foldera

use App\Models\User;
use \Core\View; // koristimo use kako ne bismo morali da navodimo za pozivanje staticke klase (ili instanciranje objekata) ceo namespace svaki put


class Signup extends \Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }
    public function createAction()
    {
        // echo '<pre>';
        // var_dump($_POST);
        // echo '</pre>';
        $user = new User($_POST); // iako bi tehnicki i iz modela mogli direktno pristupiti onome sto je poslato u requestu posto je superglobal $_POST dostupan svuda, ipak ovde
                                  // instanciramo objekat klase User i prosledjujemo mu parametre zahteva kao argument koji ce biti obradjen u konstruktoru
        if ($user->save()) {; // snimamo u bazu 
            $user->sendActivationEmail();
            // radi se redirekcija tako sto se poziva drugi action cime se menja adresa u browseru, pa tek ta druga adresa renderuje stranicu, ukoliko se render success.html izvrsi odmah ovde URL u browseru ostaje signup/create i prilikom refresha salju se na server ponovo isti podaci sto je lose naravno
            $this->redirect('/signup/success'); // isto sto i Controller::redirect('/');
        } else {
            // var_dump($user->errors); // ispisuje greske koje su sacuvane u nizu errors klase User kojem objekat user ima pristup
        
            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]); // ponovo ucitava Signup stranicu posto postoje greske u validaciji i prosledjuje $user objekat kao vrednost u asoc. nizu
        }
        
    }

    public function successAction()
    {
        View::renderTemplate('Signup/success.html'); 
    }

    public function activateAction()
    {
        User::activate($this->route_params['token']); // 'token' u route_params je omogucen rucno definisanom rutom u index.php koja pomocu regexa hvata token u poslednjem delu url-a, dok su route_params omoguceni izvrsavanjem konstruktorske funkcije u apstraktnoj klasi Controller

        $this->redirect('/signup/activated');        
    }

    public function activatedAction() //  Show the activation success page
    {
        View::renderTemplate('Signup/activated.html');
    }

    // protected function before()
    // {
    //     echo "(before) ";
    //     // return false; // ukoliko vratimo false, nece se izvrsiti ni glavna ni metoda after zahvaljujuci ispitivanju u __call metodi apstraktne klase Controler koja je parent class za Home
    // }

    // protected function after()
    // {
    //     echo " (after)";
    // }
}
