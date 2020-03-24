<?php

namespace Core;

class View 
{
    // ovo je varijanta ako se ne koristi template engine (twig)
    public static function render($view, $args = []) // static klase ne instanciraju objekte vec se pozivaju direktno, prosledjuje se ime fajla koji zelimo da prikazemo kroz $view arg.
    {                                                // opciono prima i niz, koji ostaje prazan niz ako se ne prosledi
        $file = '../App/Views/' . $view; // relative to Core folder 
        extract($args, EXTR_SKIP); // extract pretvara clanove associative array-a u promenjive, flag EXTR_SKIP znaci da If there is a collision, don't overwrite the existing variable.

        if(is_readable($file)) {
            require $file; // ucitava trazeni fajl
        } else {
            // echo "$file not found";
            throw new \Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template  The template file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function renderTemplate($template, $args = [])
    {
        echo static::getTemplate($template, $args); // prikazuje vraceni template
    }



    public static function getTemplate($template, $args = []) // metoda koja samo vraca template (ne prikazuje ga i na taj nacin moze da bude pozvana i od metode (gore) koja ce ga prikazati ali i recimo za slanje emailova gde prikazivanje nije potrebno)
    {
        static $twig = null;

        if ($twig === null) {
            //$loader = new \Twig_Loader_Filesystem('../App/Views');
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);
            $twig->addGlobal('current_user', \App\Auth::getUser()); // moramo rucno da se postaramo da ova funkcija bude bude dostupna i twig-u
                                                                    // na ovaj nacin ime korisnika dostupno je na svim nivoima i ne mora se za svaki view praviti upit u bazu, vec se pravi samo ovde
            $twig->addGlobal('flash_messages', \App\Flash::getMessages());                                                                    
        }

        return $twig->render($template, $args);
    }
}