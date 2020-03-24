<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use App\Flash;
use App\Models\Keywords;
use App\Models\User;
use App\Config;
use App\Scrap;




class Collections extends Authenticated // moramo biti ulogovani da bi smo videli stranicu za podesavanja profila
{
    protected function before()
    {
        parent::before(); // poziva metodu before() iz Authenticated klase koja zahteva da korisnik bude ulogovan
        // bez parent::before() metoda before() iz klase Profile pregazila bi (overriding) metodu iz parent klase i ne bi bilo potrebno da korisnik bude ulogovan da bi se prikazao i editovao profil
        $this->user = Auth::getUser(); // posto se u svakoj metodi u ovom, Profile.php controller-u pozivala Auth::getUser() metoda, na ovaj nacin ona se automatski izvrsava prilikom poziva bilo koje metode iz Profile.php
    }

    public function showAction()
    {
        $websitesToQuery = array();

        foreach (Config::WEBSITES_TO_SCRAP as $name => $urlImgArr) {
            if (isset($_POST[$name])) {
                $websitesToQuery = array_merge($websitesToQuery, array($urlImgArr[0]=>$urlImgArr[1]));
            }
        }

        $fromDate = '1000-01-01';
        $toDate = '9999-12-31';

        if (isset($_POST["from-date"]) && $_POST["from-date"] != "") {
            $fromDate = $_POST["from-date"];
        }

        if (isset($_POST["to-date"]) && $_POST["to-date"] != "") {
            $toDate = $_POST["to-date"];
        }

        if (!empty($websitesToQuery)) {

            $allTitles = array();
            $logoBase64Arr = array();
            foreach ($websitesToQuery as $site => $imgUrl) {
                $allTitles = array_merge($allTitles, Keywords::findTitleByWebsite($site, $fromDate, $toDate));
                
                $img_file = $imgUrl;
                $imgData = base64_encode(file_get_contents($img_file));
                // Format the image SRC:  data:{mime};base64,{data};
                $src = 'data: ' . mime_content_type($img_file) . ';base64,' . $imgData;
                $logoBase64Arr = array_merge($logoBase64Arr, array($site=>$src));
            }

            if (!empty($allTitles)) { // only if there are titles in db between given dates

                $keywordsFromTitles = array();

                foreach ($allTitles as $title) {
                    $keywordsFromTitles = array_merge($keywordsFromTitles, preg_split("/ |:|!|\?|\.|,|;|-|–|\"|\(|\)|\.\.\./", $title));
                }

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

                $keywordsFromTitlesFilteredCounted = array_slice($keywordsFromTitlesFilteredCounted, 0, 100); // cuts the array to the first 100 elements (keywords)

                $counter = 0;
                $limit = 2;
                foreach ($keywordsFromTitlesFilteredCounted as $key => $value) {
                    if ($counter < $limit) {
                        $keywordsFromTitlesFilteredCounted[$key] = array("quantity" => $value, "url" => Scrap::getImageFromGoogle($key));
                    } else {
                        $keywordsFromTitlesFilteredCounted[$key] = array("quantity" => $value, "url" => "");
                    }
                    $counter++;
                }

                // ****************************

                /*
            $retrievedArr = Keywords::findByWebsite($websitesToQuery);

            $keywordsArrayFromDb = array();
            // foreach ($retrievedArr as $keywordsString) {
            //     $keywordsArrayFromDb = array_merge($keywordsArrayFromDb, unserialize($keywordsString));
            // }

            foreach ($retrievedArr as $keywordsString) {
                $keywordsArrayFromDb = array_merge_recursive($keywordsArrayFromDb, unserialize($keywordsString));
            }

            $keywordsArrayFromDbUniqueCounted = array();
            // foreach ($keywordsArrayFromDb as $keyword => $count) {
            //     if (array_key_exists($keyword, $keywordsArrayFromDbUniqueCounted)) {
            //         $keywordsArrayFromDbUniqueCounted[$keyword] += $count;
            //     } else {
            //         $keywordsArrayFromDbUniqueCounted[$keyword] = $count;
            //     }
            // }

            foreach ($keywordsArrayFromDb as $keyword => $value) {
                if (is_array($value)) {
                    $keywordsArrayFromDbUniqueCounted[$keyword] = array_sum($value);
                } else {
                    $keywordsArrayFromDbUniqueCounted[$keyword] = $value;
                }
            }

            arsort($keywordsArrayFromDbUniqueCounted);     

            $keywordsArrayFromDbUniqueCounted = array_slice($keywordsArrayFromDbUniqueCounted, 0, 50 ); // cuts the array to the first 50 elements (keywords)

            $counter = 0;
            $limit = 2;
            foreach ($keywordsArrayFromDbUniqueCounted as $key => $value) {
                if ($counter < $limit) {
                    $keywordsArrayFromDbUniqueCounted[$key] = array("quantity" => $value, "url" => Scrap::getImageFromGoogle($key));
                } else {
                    $keywordsArrayFromDbUniqueCounted[$key] = array("quantity" => $value, "url" => "");
                }
                $counter++;
            }
*/
                // * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
                // the following code repeats itself - compare to second part of profile/collectUserInfoAction 
                // * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
                // this part creates title suggestions


                $retrievedKeywordsFromDb = unserialize($this->user->readPreferredKeywords()['preferred_keywords']); // loads keywords counter from db
                $clickedTitlesFromDb = unserialize($this->user->readClickedTitles()['clicked_titles']); // loads previously clicked titles counter from db

                if ($retrievedKeywordsFromDb && $clickedTitlesFromDb) {
                    $totalResultingArr = array();
                    foreach ($retrievedKeywordsFromDb as $keyword => $quantity) {
                        $n = Keywords::findByKeyword($keyword, $fromDate, $toDate); // check if 2nd and 3rd arg can go like this

                        $totalResultingArr = array_merge($n, $totalResultingArr);
                    }


                    $tempArr = array_unique(array_column($totalResultingArr, 'title')); // these two lines filter arrays in an array that have the same 'title' key value
                    $totalResultingArr = array_intersect_key($totalResultingArr, $tempArr);

                    $totalResultingArr = array_values($totalResultingArr); // this normalizes numeric indexes in natural order (0,1,2,3,4) because some indexes were missing after previous filtering

                    for ($a = 0; $a < count($totalResultingArr); $a++) {

                        // unsetting arrays in an array that have numeric keys 
                        // had to be done like this, because we are modifying inner arrays and that could only be done by referencing to appropriate keys
                        $innerKeys = array_keys($totalResultingArr[$a]);
                        for ($x = 0; $x < count($innerKeys); $x++) {
                            if (is_int($innerKeys[$x])) {
                                unset($totalResultingArr[$a][$innerKeys[$x]]); // a reference must from the outer array inwards, otherwise unseting only by inner array reference would not be memorized
                            }
                        }

                        // counting the total number of all keywords in each title and adding the counter as a new key to the inner arrays
                        // here we are also using array_keys approach, otherwise a new key 'keywordCounter' couldn't be set
                        $titleArr = preg_split("/ |:|!|\?|\.|,|;|-|–|\(|\)|\.\.\./", $totalResultingArr[$a]['title']);
                        $totalCounter = 0;
                        // $containingKeywords = array(); // just for testing
                        foreach ($retrievedKeywordsFromDb as $keyword => $quantity) {
                            $counter = 0;
                            for ($y = 0; $y < count($titleArr); $y++) {
                                $titleArr[$y] = ucwords(strtolower($titleArr[$y]));
                                $titleArr[$y] = preg_replace(Config::CHARS_TO_LOOK_FOR, Config::CHARS_TO_CHANGE, $titleArr[$y]);
                                if ($titleArr[$y] == $keyword) {
                                    $counter += $quantity;
                                    $totalCounter++;
                                    // array_push($containingKeywords, $keyword); // just for testing
                                }
                            }
                            $totalResultingArr[$a][$keyword] = $counter; // adding new key-value pair 
                        }
                        $totalResultingArr[$a]['totalKeywordCounter'] = $totalCounter;
                        // $totalResultingArr[$a]['containingKeywords'] = $containingKeywords; // just for testing
                    }

                    //echo json_encode($totalResultingArr);

                    $totalResultingArrPositiveCounter = array();
                    foreach ($totalResultingArr as $innerArray) {
                        $foundOnePositive = false;
                        foreach ($retrievedKeywordsFromDb as $keyword => $quantity) {

                            if ($innerArray[$keyword] > 0 && !$foundOnePositive && !in_array($innerArray['title'], $clickedTitlesFromDb, true)) { // we don't need arrays with titles not matching any of the keywords (if there is only one matching keyword it qualifies the title to be suggested to the user) neither we titles that were already clicked before and saved to db
                                $totalResultingArrPositiveCounter[] = $innerArray; // adding to indexed array (prefered way of adding array to an array (instead of array_push))
                                $foundOnePositive = true;
                            }
                        }
                    }


                    usort($totalResultingArrPositiveCounter, function ($a, $b) { // sorts array of arrays according to innerarray value of 'keywordCounter' key 
                        return $b['totalKeywordCounter'] <=> $a['totalKeywordCounter']; // in descending order. for ascending order it would be: $a['keywordCounter'] <=> $b['keywordCounter'])
                    });

                    $totalResultingArrPositiveCounter = array_slice($totalResultingArrPositiveCounter, 0, 3); // cuts the array so that only first 6 elements remain
                }

                //echo json_encode($totalResultingArrPositiveCounter);
                // * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
                // * * * * * * * * * * * * * * * * * * * * * * * * * * * * 

                // $img_file = "/opt/lampp/htdocs/App/Images/luftika_logo.png";
                // $imgData = base64_encode(file_get_contents($img_file));
                // // Format the image SRC:  data:{mime};base64,{data};
                // $src = 'data: ' . mime_content_type($img_file) . ';base64,' . $imgData;

                View::renderTemplate('Collections/show.html', [

                    //'keywordsArrayFromDb' => $keywordsArrayFromDbUniqueCounted,
                    'keywordsArrayFromDb' => $keywordsFromTitlesFilteredCounted,
                    'alreadyRendered' => $limit,
                    'suggestedTitles' => $totalResultingArrPositiveCounter,
                    'toDate' => $toDate,
                    'fromDate' => $fromDate,
                    'logoBase64Arr' => $logoBase64Arr
                ]);
            } else {
                Flash::addMessage('Nothing found for selected websites/dates. Please, try again.', Flash::INFO);
                View::renderTemplate('Profile/show_collected_choice.html');
            }
        } else {
            View::renderTemplate('Profile/show_collected_choice.html');
        }
    }

    public function getKeywordReferencesAction()
    {

        if (isset($_GET)) {
            $keywordReferences = Keywords::findByKeyword($_GET["key"], $_GET["fromDate"], $_GET["toDate"]);
            echo json_encode($keywordReferences);
        }
    }




    // consumed by /opt/lampp/htdocs/App/Views/Collections/show.html
    public function getSingleImageFromGoogleAction()
    {
        if (isset($_GET)) {
            $imageUrl = Scrap::getImageFromGoogle($_GET["key"]);
            echo $imageUrl;
        }
    }
    // not clear if used anywhere!
    // public function getKeywordImage()
    // {

    //     if (isset($_GET)) {
    //         $keywordReferences = Keywords::findByKeyword($_GET["key"]);
    //         echo json_encode($keywordReferences);
    //     }
    // }



    /*public function editAction()
    {
        View::renderTemplate('Profile/edit.html', [
            'user' => $this->user // prosledjujemo objekat korisnika, s obzirom da smo vec ulogovani
        ]);                       // $this->user je definisan u before() funkciji gore
    }

    public function updateAction()
    {
        if($this->user->updateProfile($_POST)) { // $this->user je definisan u before() funkciji gore

            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show'); 

        } else { // ako nije prosla validacija ponovo prikazujemo formu za edit

            View::renderTemplate('Profile/edit.html', [
                'user' => $this->user // $this->user je definisan u before() funkciji gore
            ]);
        };
    }

    public function showCollectedAction() // *** akcija koju sam ja dodao ***
    {
        View::renderTemplate('Profile/showCollected.html', [
            'user' => $this->user // prosledjujemo objekat korisnika, s obzirom da smo vec ulogovani
        ]);                       // $this->user je definisan u before() funkciji gore
    }
    */
}
