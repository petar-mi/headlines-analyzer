<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use App\Flash;
use App\Models\Keywords;
use App\Config;

class Profile extends Authenticated // moramo biti ulogovani da bi smo videli stranicu za podesavanja profila
{
    protected function before()
    {
        parent::before(); // poziva metodu before() iz Authenticated klase koja zahteva da korisnik bude ulogovan
        // bez parent::before() metoda before() iz klase Profile pregazila bi (overriding) metodu iz parent klase i ne bi bilo potrebno da korisnik bude ulogovan da bi se prikazao i editovao profil
        $this->user = Auth::getUser(); // posto se u svakoj metodi u ovom, Profile.php controller-u pozivala Auth::getUser() metoda, na ovaj nacin ona se automatski izvrsava prilikom poziva bilo koje metode iz Profile.php
    }

    public function showAction()
    {
        View::renderTemplate('Profile/show.html', [
            'user' => $this->user // prosledjujemo objekat korisnika, s obzirom da smo vec ulogovani
        ]);                       // $this->user je definisan u before() funkciji gore
    }

    public function editAction()
    {
        View::renderTemplate('Profile/edit.html', [
            'user' => $this->user // prosledjujemo objekat korisnika, s obzirom da smo vec ulogovani
        ]);                       // $this->user je definisan u before() funkciji gore
    }

    public function updateAction()
    {
        if ($this->user->updateProfile($_POST)) { // $this->user je definisan u before() funkciji gore

            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show');
        } else { // ako nije prosla validacija ponovo prikazujemo formu za edit

            View::renderTemplate('Profile/edit.html', [
                'user' => $this->user // $this->user je definisan u before() funkciji gore
            ]);
        };
    }

    public function showCollectedChoiceAction() // *** akcija koju sam ja dodao ***
    {
        View::renderTemplate('Profile/show_collected_choice.html', [
            'user' => $this->user // prosledjujemo objekat korisnika, s obzirom da smo vec ulogovani
        ]);                       // $this->user je definisan u before() funkciji gore
    }

    public function collectUserInfoAction()
    {
        // !!! VAZNO !!!
        $postReqData = file_get_contents('php://input'); // kada PHP prima post request a nije u URLencoded formatu onda se na ovaj nacin dolazi do requesta!!! 
        $postReqDataObj = json_decode($postReqData);
        
        $retrievedKeywordsFromDb = unserialize($this->user->readPreferredKeywords()['preferred_keywords']); // loads keywords counter from db
        $clickedTitlesFromDb = unserialize($this->user->readClickedTitles()['clicked_titles']); // loads previously clicked titles counter from db

        if ($clickedTitlesFromDb) {
            if (!in_array($postReqDataObj->title, $clickedTitlesFromDb, true)) { // adds newly clicked title if it has not been previously (clicked and) saved to db
                array_push($clickedTitlesFromDb, $postReqDataObj->title);
            }
        } else {
            $clickedTitlesFromDb = array($postReqDataObj->title); // adds the title that has been just received from the client as the 1st one to the array
        }

        $this->user->updateClickedTitles(serialize($clickedTitlesFromDb)); // saves updated clicked titles array to DB


        
        $keyWordsArr = preg_split("/ |:|!|\?|\.|,|;|-|\"|\(|\)|\.\.\./", $postReqDataObj->title);

        $keyWordsArr = array_filter( // izbacuje clanove niza koji sadrze brojevne vrednosti
            $keyWordsArr,
            function ($arrayEntry) {
                return !preg_match('/\d/', $arrayEntry);
            }
        );

        foreach ($keyWordsArr as $keyword => $value) {

            $keyWordsArr[$keyword] = ucwords(strtolower($value)); // pretvara svaki clan niza u title case osim onih koji sadrze karaktere sa dijaktritickim znacima
            $keyWordsArr[$keyword] = preg_replace(Config::CHARS_TO_LOOK_FOR, Config::CHARS_TO_CHANGE, $keyWordsArr[$keyword]); // ispravlja reci sa dijakritickim znakovima tako sto svakog clan niza $karakteriZaPretragu pronadjenim u stringu pomocu regexa pretvara u odgovarajuci clan niza $karakteriZaZamenu
        };

        $keyWordsFiltered = array_diff($keyWordsArr, Config::EXCLUDE_WORDS); // vraca razliku skupova
        // $keyWordsFilteredUnique = array_unique($keyWordsFiltered);
        // $keyWordsFilteredUnique = array_values($keyWordsFilteredUnique); // this normalizes numeric indexes in natural order (0,1,2,3,4) because some indexes were missing after previous filtering

        $nizSvihReciCounted = array();
        foreach ($keyWordsFiltered as $item) {  // counting how many occurances there are of each keyword contained in the title that was clicked on
            if (array_key_exists($item, $nizSvihReciCounted)) {
                $nizSvihReciCounted[$item] += 1;
            } else {
                $nizSvihReciCounted[$item] = 1;
            }
        }

        if ($retrievedKeywordsFromDb) {
            foreach ($retrievedKeywordsFromDb as $key => $count) { // if keyword exists in db its count is increased
                foreach ($nizSvihReciCounted as $key2 => $count2) {
                    if ($key == $key2) {
                        $retrievedKeywordsFromDb[$key] = $count + $count2;
                    }
                    if (!in_array($key2, array_keys($retrievedKeywordsFromDb))) { // if keyword doesn't exist in db, its key-value pair is created
                        $retrievedKeywordsFromDb[$key2] = $count2;
                    }
                }
            }

            arsort($retrievedKeywordsFromDb); // sorts according to the value from highest to lowest

            if (count($retrievedKeywordsFromDb) > 100) { // if there are more than 100 keywords, cuts the array to 100
                $retrievedKeywordsFromDb = array_slice($retrievedKeywordsFromDb, 0, 100);
            }

            // if (!in_array($postReqDataObj->title, $clickedTitlesFromDb, true)) { // adds newly clicked title if it has not been previously (clicked and) saved to db
            //     array_push($clickedTitlesFromDb, $postReqDataObj->title);
            // }

            $this->user->updatePreferredKeywords(serialize($retrievedKeywordsFromDb)); // saves updated keywords counter to DB
            
        } else { // if there was no previously saved data for the user in db
            arsort($nizSvihReciCounted);
            $this->user->updatePreferredKeywords(serialize($nizSvihReciCounted)); // saves keywords counter to DB
        }

        // * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
        // * * * * * * * * * * * * * * * * * * * * * * * * * * * * 



        !$retrievedKeywordsFromDb ? $keywordsCounterArray = $nizSvihReciCounted : $keywordsCounterArray = $retrievedKeywordsFromDb; // switches to the array sent from the client if there was nothing previously saved in db

        $totalResultingArr = array();
        foreach ($keywordsCounterArray as $keyword => $quantity) {
            $n = Keywords::findByKeyword($keyword, $postReqDataObj->fromDate, $postReqDataObj->toDate);

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
            $titleArr = preg_split("/ |:|!|\?|\.|,|;|-|\(|\)|\.\.\./", $totalResultingArr[$a]['title']);
            $totalCounter = 0;
            // $containingKeywords = array(); // just for testing
            foreach ($keywordsCounterArray as $keyword => $quantity) {
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
            foreach ($keywordsCounterArray as $keyword => $quantity) {

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

        echo json_encode($totalResultingArrPositiveCounter);
        
    }
    
}
