<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use App\Flash;
use App\Models\Keywords;
use App\Config;

class Profile extends Authenticated // we have to be logged-in to be able to see profile edit page
{
    protected function before()
    {
        parent::before(); // calls before() method from Authenticated class that requires for user to be logged-in
        // without parent::before() method before() from Profile class would override method from parent class & it wouldn't be needed for user to be logged-in to render and edit profile
        $this->user = Auth::getUser(); // because in each method of this Profile.php controller Auth::getUser() method was called, this way it automatically executes when calling any of Profile.php methods
    }

    public function showAction()
    {
        View::renderTemplate('Profile/show.html', [
            'user' => $this->user // we're passing user object since we are logged in already
        ]);                       // $this->user is defined in before() function above
    }

    public function editAction()
    {
        View::renderTemplate('Profile/edit.html', [
            'user' => $this->user // we're passing user object since we are logged in already
        ]);                       // $this->user is defined in before() function above
    }

    public function updateAction()
    {
        if ($this->user->updateProfile($_POST)) { // $this->user is defined in before() function above

            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show');
        } else { // if validation has failed we render edit profile page again

            View::renderTemplate('Profile/edit.html', [
                'user' => $this->user // $this->user is defined in before() function above
            ]);
        };
    }

    public function showCollectedChoiceAction() 
    {
        View::renderTemplate('Profile/show_collected_choice.html', [
            'user' => $this->user // we're passing user object since we are logged in already
        ]);                       // $this->user is defined in before() function above
    }

    public function collectUserInfoAction()
    {
        // !!! IMPORTANT !!!
        $postReqData = file_get_contents('php://input'); // when PHP receives post request and it's not in URLencoded format then this is the way to access the request !!! 
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

        
        // ****** THIS IS A CODE REPEATING ITSELF - COMPARE TO Collections.php showAction() function *************

        $keyWordsArr = preg_split("/ |:|!|\?|\.|,|;|-|\"|\(|\)|\.\.\./", $postReqDataObj->title);

        $keyWordsArr = array_filter( 
            $keyWordsArr,
            function ($arrayEntry) {
                return !preg_match('/\d/', $arrayEntry); // returns only non-numeric array elements
            }
        );

        foreach ($keyWordsArr as $keyword => $value) {

            $keyWordsArr[$keyword] = ucwords(strtolower($value)); // converts each array element to title case exept those that contain diacritical marks
            $keyWordsArr[$keyword] = preg_replace(Config::CHARS_TO_LOOK_FOR, Config::CHARS_TO_CHANGE, $keyWordsArr[$keyword]); // corrects words with diacritical marks by converting each Config::CHARS_TO_LOOK_FOR element found in a string using regex into appropriate Config::CHARS_TO_CHANGE element
        };

        $keyWordsFiltered = array_diff($keyWordsArr, Config::EXCLUDE_WORDS); // returns the difference of arrays
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

        // ****** END OF CODE REPEATING ITSELF - COMPARE TO Collections.php showAction() function *************


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
