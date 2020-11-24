<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use App\Flash;
use App\Models\Keywords;
use App\Models\User;
use App\Config;
use App\Scrap;




class Collections extends Authenticated // we have to be logged-in to be able to see profile edit page
{
    protected function before()
    {
        parent::before(); // calls before() method from Authenticated class that requires for user to be logged-in
        // without parent::before() method before() from Profile class would override method from parent class & it wouldn't be needed for user to be logged-in to render and edit profile
        $this->user = Auth::getUser(); // because in each method of this Profile.php controller Auth::getUser() method was called, this way it automatically executes when calling any of Profile.php methods
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
                // to be used locally:
                // $src = 'data: ' . mime_content_type($img_file) . ';base64,' . $imgData;
                // to be used for heroku:
                $src = 'data: ' . 'image/png' . ';base64,' . $imgData;
                $logoBase64Arr = array_merge($logoBase64Arr, array($site=>$src));
            }

            if (!empty($allTitles)) { // only if there are titles in db between given dates

                $keywordsFromTitles = array();

                // ****** THIS IS A CODE REPEATING ITSELF - COMPARE TO Profile.php collectUserInfoAction() function *************

                foreach ($allTitles as $title) {
                    $keywordsFromTitles = array_merge($keywordsFromTitles, preg_split("/ |:|!|\?|\.|,|;|-|–|\"|\(|\)|\.\.\./", $title));
                }

                
                $keywordsFromTitles = array_filter(
                    $keywordsFromTitles,
                    function ($arrayEntry) {
                        return !preg_match('/\d/', $arrayEntry); // returns only non-numeric array elements
                    }
                );

                foreach ($keywordsFromTitles as $keyword => $value) {

                    $keywordsFromTitles[$keyword] = ucwords(strtolower($value)); // converts each array element to title case exept those that contain diacritical marks
                    $keywordsFromTitles[$keyword] = preg_replace(Config::CHARS_TO_LOOK_FOR, Config::CHARS_TO_CHANGE, $keywordsFromTitles[$keyword]); // corrects words with diacritical marks by converting each Config::CHARS_TO_LOOK_FOR element found in a string using regex into appropriate Config::CHARS_TO_CHANGE element

                }

                $keywordsFromTitlesFiltered = array_diff($keywordsFromTitles, Config::EXCLUDE_WORDS); // returns the difference of arrays

                $keywordsFromTitlesFilteredCounted = array();
                foreach ($keywordsFromTitlesFiltered as $item) {  // could probably be done by built-in method array_count_values(array)
                    if (array_key_exists($item, $keywordsFromTitlesFilteredCounted)) {
                        $keywordsFromTitlesFilteredCounted[$item] += 1;
                    } else {
                        $keywordsFromTitlesFilteredCounted[$item] = 1;
                    }
                }

                // ****** THE END OF CODE REPEATING ITSELF - COMPARE TO Profile.php collectUserInfoAction() function *************


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


                View::renderTemplate('Collections/show.html', [

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

}
