<?php

use \App\Models\Keywords;
use \App\Scrap;
use App\Config;

// require '/opt/lampp/htdocs/App/Scrap.php'; // mora se rucno raditi require sa navodjenjem full path-a, jer autoload uzima relative paths, a cron zahteva full path da bi radio
require_once('/opt/lampp/htdocs/App/Scrap.php');
require_once("/opt/lampp/htdocs/App/Models/Keywords.php"); // -||-
require_once('/opt/lampp/htdocs/App/Config.php'); // -||-

/*
$websitesToScrap = array("mondo" => "mondo.rs", "luftika" => "luftika.rs", "noizz" => "noizz.rs");
$file = dirname(__FILE__) . '/Test/output2.txt';

echo dirname(__FILE__);
echo "<br />";
$zaUpis = "HAHAHA";
echo getcwd();
echo "<br />";
echo get_current_user();
echo "<br />";
echo fileperms("/opt/lampp/htdocs/App/Cronjob.php");
echo "<br />";
//$zaUpis = Keywords::test();

file_put_contents($file, $zaUpis, FILE_APPEND);

$scrap = new Scrap($websitesToScrap);

file_put_contents($file, $scrap->test, FILE_APPEND);
$zaUpis = Scrap::testtest3();
file_put_contents($file, $zaUpis, FILE_APPEND);
file_put_contents($file, $scrap->mondo, FILE_APPEND);

//echo Scrap::testtest3();
*/


$scrap = new Scrap(Config::WEBSITES_TO_SCRAP);
$scrap->ScrapWebsite(Config::WEBSITES_TO_SCRAP);

foreach (Config::WEBSITES_TO_SCRAP as $key => $value) {
    if (isset($scrap->scrapedData[$key])) {
        // $keywordString = serialize($scrap->scrapedData[$key]["keywords"]); // not needed anymore
        // Keywords::saveKeywords($value, $keywordString); // // not needed anymore

        foreach ($scrap->scrapedData[$key]["linksTitles"] as $title => $url) {
            Keywords::saveTitlesUrls($value[0], $title, $url);
        }
    }
}
