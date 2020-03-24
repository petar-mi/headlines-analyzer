<?php

namespace App\Controllers; // namespace koji korespondira sa organizacijom foldera

use \Core\View; // koristimo use kako ne bismo morali da navodimo za pozivanje staticke klase (ili instanciranje objekata) ceo namespace svaki put


class Items extends Authenticated // ova klasa Items je dete klase Authenticated jer zahtevamo logovanje za sve action metode pa da ne bismo morali da to pisemo u svakoj action metodi
{
    public function indexAction() 
    {
        // $this->requireLogin(); // nalazi se u \Core \Controller tj. u osnovnoj klasi i preusmerava na logovanje ako korisnik nije ulogovan
        // gornji red je zakomentarisan jer bismo morali pozivati metodu za svaku akciju u ovom controller-u, umesto toga izmestili smo je u before metodu koja se nalazi u apstraktnoj klasi Authenticated od koje nasledjuju ovaj controller i svi koji bi zahtevali obavezno logovanje
        View::renderTemplate('Items/index.html'); // ako jeste ulogovan prikazuje stranicu
    }

    public function newAction() // just some random actions
    {
        echo "new action";
    }

    public function showAction() // just some random actions
    {
        echo "show action";
    }
}