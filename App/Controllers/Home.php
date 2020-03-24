<?php

namespace App\Controllers; // namespace koji korespondira sa organizacijom foldera

use \Core\View; // koristimo use kako ne bismo morali da navodimo za pozivanje staticke klase (ili instanciranje objekata) ceo namespace svaki put
use \App\Scrap;
use \App\Config;
use \App\Stemmer;
use App\Models\Keywords;

class Home extends \Core\Controller
{
    public function indexAction() // dodat sufiks Action kako se metode ne bi izvrsavale direktno nego da bi isle preko __call metode definisane u parent abstract Controller class
    {
        // \Core\View::render('Home/index.php',[ 'name' => 'Dave', 'colours' => ['red', 'green', 'blue']]););  // ovako bi moralo da se pise da gore nismo koristili: use \Core\View;    
        // View::render('Home/index.php', // na ovaj nacin se u php-u pozivaju staticne metode (ovde je to metoda render iz klase View), prosledjujemo ime fajla koji zelimo da prikazemo
        //              [                 // takodje prosledjujemo i niz (koji ce u Core\View biti pretvoren u promenjive)
        //                 'name' => 'Dave',
        //                 'colours' => ['red', 'green', 'blue']
        //              ]); // na ovaj nacin se u php-u pozivaju staticne metode (ovde je to metoda render iz klase View), prosledjujemo ime fajla koji zelimo da prikazemo
        // View::renderTemplate('Home/index.html', [ // ovde je ubacena druga metoda kako bi sve islo preko twig template engine-a, i index.php zamenjen je index.html fajlom
        //     'user'    => Auth::getUser() // prosledjujemo user objekat dobijen novim upitom u bazu na osnovu user_id-a dobijenog prilikom prvog upita tj. prilikom logovanja
        // ]); // zakomentarisano jer smo u View.php metodom addglobal omogucili da twig ima na raspolaganju username tako sto poziva metodu Auth::getUser()
        // na taj nacin ne moramo da za svaki view pravimo upit u bazu

        // include('/opt/lampp/htdocs/App/Cronjob.php'); // samo za debugging crobjob-a

        View::renderTemplate('Home/index.html');


        // $retrievedArr = Keywords::findByWebsite(array("mondo.rs", "luftika.rs", "noizz.rs"));
/*
        $retrievedArr = array("mondo.rs", "luftika.rs", "noizz.rs");

        $allTitles = array();
        foreach ($retrievedArr as $site) {
            $allTitles = array_merge($allTitles, Keywords::findTitleByWebsite($site));
        }

        echo "<pre>";
        var_dump($allTitles);
        echo "</pre>";

        $keywordsFromTitles = array();

        foreach ($allTitles as $title) {
            $keywordsFromTitles = array_merge($keywordsFromTitles, preg_split("/ |:|!|\?|\.|,|;|-|–|\"|\(|\)|\.\.\./", $title));
        }

        echo "<pre>";
        var_dump($keywordsFromTitles);
        echo "</pre>";*/
        
/*
        $keywordsFromTitles = array_filter(
            $keywordsFromTitles,
            function ($arrayEntry) {
                //return !preg_match("/(?![.=$'€%-])\p{P}/u", $arrayEntry) && !is_numeric($arrayEntry); //  ovde je izbacivalo i interpunkciju, ali onda je izbacivalo i reci zajedno sa njima
                return !preg_match('/\d/', $arrayEntry); // izbacuje clanove niza koji sadrze brojevne vrednosti
            }
        );

        foreach ($keywordsFromTitles as $keyword => $value) {

            $keywordsFromTitles[$keyword] = ucwords(strtolower($value)); // pretvara svaki clan niza u title case osim onih koji sadrze karaktere sa dijaktritickim znacima
            $keywordsFromTitles[$keyword] = preg_replace(Config::CHARS_TO_LOOK_FOR, Config::CHARS_TO_CHANGE, $keywordsFromTitles[$keyword]); // ispravlja reci sa dijakritickim znakovima tako sto svakog clan niza $karakteriZaPretragu pronadjenim u stringu pomocu regexa pretvara u odgovarajuci clan niza $karakteriZaZamenu

        }

        $keywordsFromTitlesFiltered = array_diff($keywordsFromTitles, Config::EXCLUDE_WORDS); // vraca razliku skupova

        $keywordsFromTitlesFilteredCounted = array();  
        foreach ($keywordsFromTitlesFiltered as $item) {  // ovo prebrojavanje je verovatno moglo da se uradi i pomocu ugradjene metode array_count_values(array)
           if (array_key_exists($item, $keywordsFromTitlesFilteredCounted)) {
              $keywordsFromTitlesFilteredCounted[$item] += 1;
           } else {
              $keywordsFromTitlesFilteredCounted[$item] = 1;
           }
        }
  
        arsort($keywordsFromTitlesFilteredCounted);


        echo "<pre>";
        var_dump($keywordsFromTitlesFilteredCounted);
        echo "</pre>";
        */

        // $keywordsArrayFromDb = array();
        // foreach ($retrievedArr as $keywordsString) {
        //     $keywordsArrayFromDb = array_merge_recursive($keywordsArrayFromDb, unserialize($keywordsString));
        // }

        // $keywordsArrayFromDbUniqueCounted = array();

        // foreach ($keywordsArrayFromDb as $keyword => $value) {
        //     if (is_array($value)) {
        //         $keywordsArrayFromDbUniqueCounted[$keyword] = array_sum($value);
        //     } else {
        //         $keywordsArrayFromDbUniqueCounted[$keyword] = $value;
        //     }
        // }

        // arsort($keywordsArrayFromDbUniqueCounted);





        // echo "<pre>";
        // var_dump($keywordsArrayFromDbUniqueCounted);
        // echo "</pre>";

        // $keywordsArrayFromDbUniqueCounted = array_slice($keywordsArrayFromDbUniqueCounted, 0, 50 ); // cuts the array to the first 50 elements (keywords)
        // echo "<pre>";
        // echo var_dump($keywordsArrayFromDb);
        // echo "</pre>";

        // $counter = 0;
        // $limit = 2;
        // foreach ($keywordsArrayFromDbUniqueCounted as $key => $value) {
        //     if ($counter < $limit) {
        //         $keywordsArrayFromDbUniqueCounted[$key] = array("quantity" => $value, "url" => Scrap::getImageFromGoogle($key));
        //     } else {
        //         $keywordsArrayFromDbUniqueCounted[$key] = array("quantity" => $value, "url" => "");
        //     }
        //     $counter++;
        // }

        // echo "<pre>";
        // var_dump($keywordsArrayFromDbUniqueCounted);
        // echo "</pre>";

        // echo "<pre>";
        // var_dump(Keywords::findTitleByWebsite("mondo.rs"));
        // echo "</pre>";
        // echo "<pre>";
        // var_dump(Keywords::findTitleByWebsite("luftika.rs"));
        // echo "</pre>";
        // echo "<pre>";
        // var_dump(Keywords::findTitleByWebsite("noizz.rs"));
        // echo "</pre>";














        // echo Stemmer::stem("naslednik nasledniku , nasledstvo naslediti  nasledio"); // just for serbian stemmer testing

    }
    /*
    protected function before()
    {
        echo "(before) ";
        // return false; // ukoliko vratimo false, nece se izvrsiti ni glavna ni metoda after zahvaljujuci ispitivanju u __call metodi apstraktne klase Controler koja je parent class za Home
    }

    protected function after()
    {
        echo " (after)";
    }
    */
}
