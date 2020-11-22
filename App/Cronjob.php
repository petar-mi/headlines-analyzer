<?php

use \App\Models\Keywords;
use \App\Scrap;
use App\Config;

require_once('/opt/lampp/htdocs/App/Scrap.php');
require_once("/opt/lampp/htdocs/App/Models/Keywords.php"); // -||-
require_once('/opt/lampp/htdocs/App/Config.php'); // -||-

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
