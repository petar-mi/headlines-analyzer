<?php

namespace App\Controllers;

abstract class Authenticated extends \Core\Controller
{
    protected function before() // tzv action before filter, metoda koja se pokrece automatski pre svake druge action metode, vecina frameworka ima takav action filter
    {                           // ona se pokrece automatski jer je definisana (iako ostavljena prazna) u apstraktnoj klasi Controller od koje nasledjuje ova klasa Items
        $this->requireLogin(); // nalazi se u \Core\Controller tj. u osnovnoj klasi i preusmerava na logovanje ako korisnik nije ulogovan
    }
}