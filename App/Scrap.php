<?php

namespace App;

use \App\Models\Keywords;
use App\Config;

//require_once("/opt/lampp/htdocs/App/Models/Keywords.php");

class Scrap
{

   //private $nizSvihReci = array(); // not needed anymore
   private $linksTitlesArr = array();
   private $titlesArr = array();
   public $scrapedData = array();

   function __construct($websitesToScrap = [])
   {
      foreach ($websitesToScrap as $key => $value) { // iterira asoc. niz sajtova
         $this->$key = $value[0]; // i memorise kao propertije klase

         $this->titlesArr[$key] = Keywords::findTitleByWebsite($value[0], '1000-01-01', '9999-12-31'); // loads all titles for each websites that are stored in db (to be compared to during scraping)

         //$this->nizSvihReci[$key] = array(); // not needed anymore               // dodaje clanove niza class propertiju $nizSvihReci koji su i sami nizovi
         
      };
      
   }

   public function ScrapWebsite($websitesToScrap)
   {
      foreach ($websitesToScrap as $key => $value) {

         $file = file_get_contents("https://" . $value[0] . "/");
         $doc = new \DOMDocument();
         libxml_use_internal_errors(true); // bez ovoga izbacuje neku gresku
         $doc->loadHTML($file);
         libxml_use_internal_errors(false); // bez ovoga izbacuje neku gresku

         $this->$key($doc, $key); // poziva pojedinacne private funcije koje vrse sam scraping  
         //$this->cleanAndSort($key); // not needed anymore

         if (isset($this->linksTitlesArr[$key])) {
            $this->scrapedData[$key]["linksTitles"] = $this->linksTitlesArr[$key];
         }
      }
   }

   private function luftika($doc, $key)
   {
      // scraping *** luftika.rs ***

      $linksArr = $doc->getElementsByTagName('article');
      foreach ($linksArr as $child) {
         foreach ($child->getElementsByTagName('div') as $item) {
            if ($item->attributes[0]->nodeValue == "entry-header") {
               foreach ($item->childNodes as $child) {
                  if ($child->nodeName == "h2") {
                     if (!in_array($child->firstChild->textContent, $this->titlesArr[$key])) {
                        $this->linksTitlesArr[$key][$child->firstChild->textContent] = $child->firstChild->attributes[0]->value;
                        //$this->nizSvihReci[$key] = array_merge($this->nizSvihReci[$key], preg_split("/ |:|!|\?|\.|,|;|-|–|\"|\(|\)|\.\.\./", $child->firstChild->textContent));
                     }
                  }
               }
            }
         }
      }
      // end of scraping *** luftika.rs *** 
   }

   private function mondo($doc, $key)
   {
      // scraping *** mondo.rs ***

      $linksArr = $doc->getElementsByTagName('h2');
      foreach ($linksArr as $item) {
         if ($item->parentNode->attributes[0]->nodeValue == "title-wrapper") {
            foreach ($item->childNodes as $child) {
               if ($child->nodeName == "a") {
                  if (!in_array($child->textContent, $this->titlesArr[$key])) {
                     $this->linksTitlesArr[$key][$child->textContent] = $child->attributes[0]->value;
                     //$this->nizSvihReci[$key] = array_merge($this->nizSvihReci[$key], preg_split("/ |:|!|\?|\.|,|;|-|–|\"|\(|\)|\.\.\./", $child->textContent));
                  }
               }
            }
         } else {
            if (preg_match('/https/', $item->parentNode->attributes[0]->nodeValue)) {
               if (!in_array($item->textContent, $this->titlesArr[$key])) {
                  $this->linksTitlesArr[$key][$item->textContent] = $item->parentNode->attributes[0]->nodeValue;
                  //$this->nizSvihReci[$key] = array_merge($this->nizSvihReci[$key], preg_split("/ |:|!|\?|\.|,|;|-|–|\"|\(|\)|\.\.\./", $item->textContent));
               }
            }
         }
      }
      // end of scraping *** mondo.rs *** 
   }

   private function noizz($doc, $key)
   {
      // scraping *** noizz.rs *** 
      $linksArr = $doc->getElementsByTagName('a');

      foreach ($linksArr as $child) {
         if ($child->childNodes && $child->attributes) {
            foreach ($child->childNodes as $childNodes) {
               if ($childNodes->nodeName == "div") {
                  foreach ($childNodes->childNodes as $child) {
                     if (preg_match('/h/i', $child->nodeName)) {
                        foreach ($child->parentNode->parentNode->attributes as $attributes) {
                           if (!in_array($child->textContent, $this->titlesArr[$key])) {
                              $this->linksTitlesArr[$key][$child->textContent] = $attributes->value;
                           }
                        }
                        // if (!in_array($child->textContent, $this->titlesArr[$key])) {
                        //    $this->nizSvihReci[$key] = array_merge($this->nizSvihReci[$key], preg_split("/ |:|\!|\+|\=|\?|\.|\!\?|\?\!|,|;|-|–|\"|\(|\)|\.\.\./", $child->textContent));
                        // }
                     }
                  }
               }
            }
         }
      }
      // end of scraping *** noizz.rs ***
   }

   public static function getImageFromGoogle($key)
   {
      // scraping *** google images *** 

      $file = file_get_contents("https://www.google.com/search?q=" . $key . "&tbm=isch&source=univ&sa=X&ved=2ahUKEwiTm47qm-3nAhVBUhUIHcpmDAkQsAR6BAgGEAE&cshid=1582651398543535&biw=1920&bih=904");
      $doc = new \DOMDocument();
      libxml_use_internal_errors(true); // bez ovoga izbacuje neku gresku
      $doc->loadHTML($file);
      libxml_use_internal_errors(false); // bez ovoga izbacuje neku gresku

      $linksArr = $doc->getElementsByTagName('img');

      $thumbUrl = "";

      foreach ($linksArr as $child) {
         foreach ($child->attributes as $attributes) {

            if ($attributes->nodeName == "src" && strpos($attributes->nodeValue, "gstatic") && $thumbUrl == "") {
               $thumbUrl = $attributes->nodeValue;
            }
         }
      }

      return $thumbUrl;
   }

   private function cleanAndSort($key) // dead code, not called anymore
   {
      // this function is not called anymore
      $this->nizSvihReci[$key] = array_filter(
         $this->nizSvihReci[$key],
         function ($arrayEntry) {
            //return !preg_match("/(?![.=$'€%-])\p{P}/u", $arrayEntry) && !is_numeric($arrayEntry); //  ovde je izbacivalo i interpunkciju, ali onda je izbacivalo i reci zajedno sa njima
            return !preg_match('/\d/', $arrayEntry); // izbacuje clanove niza koji sadrze brojevne vrednosti
         }
      );

      foreach ($this->nizSvihReci[$key] as $keyword => $value) {

         $this->nizSvihReci[$key][$keyword] = ucwords(strtolower($value)); // pretvara svaki clan niza u title case osim onih koji sadrze karaktere sa dijaktritickim znacima
         $this->nizSvihReci[$key][$keyword] = preg_replace(Config::CHARS_TO_LOOK_FOR, Config::CHARS_TO_CHANGE, $this->nizSvihReci[$key][$keyword]); // ispravlja reci sa dijakritickim znakovima tako sto svakog clan niza $karakteriZaPretragu pronadjenim u stringu pomocu regexa pretvara u odgovarajuci clan niza $karakteriZaZamenu

      }

      $nizSvihReciFiltered = array_diff($this->nizSvihReci[$key], Config::EXCLUDE_WORDS); // vraca razliku skupova

      $nizSvihReciUniqueCounted = array();  
      foreach ($nizSvihReciFiltered as $item) {  // ovo prebrojavanje je verovatno moglo da se uradi i pomocu ugradjene metode array_count_values(array)
         if (array_key_exists($item, $nizSvihReciUniqueCounted)) {
            $nizSvihReciUniqueCounted[$item] += 1;
         } else {
            $nizSvihReciUniqueCounted[$item] = 1;
         }
      }

      arsort($nizSvihReciUniqueCounted);

      if (count($nizSvihReciUniqueCounted) > 0) {
         $this->scrapedData[$key]["keywords"] = $nizSvihReciUniqueCounted;
      }
      if (isset($this->linksTitlesArr[$key])) {
         $this->scrapedData[$key]["linksTitles"] = $this->linksTitlesArr[$key];
      }
   }
}
