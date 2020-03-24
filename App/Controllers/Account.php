<?php

namespace App\Controllers;

use App\Models\User;
// action koji sluzi za validaciju email-a iz frontenda pomocu javascripta 
class Account extends \Core\Controller
{
    public function validateEmailAction()
    {
        $is_valid = !User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null); // proverava u bazi koristeci ne post request koji dobijamo kada stisnemo sign up nego koji se salje vec dok upisujemo adresu u polje pomocu jquery validation. drugi argument je ako se radi samo o editovanju vec postojeceg profila (null je ako jquery ni ne posalje user id u slucaju kreiranja novog profila)
        header('Content-Type: application/json'); // ovo je response header
        echo json_encode($is_valid); // a ovo je telo odgovora, iskreno svaka cast kako ovo izgleda i radi, is_valid ce biti uvek samo true ili false
    }
}
