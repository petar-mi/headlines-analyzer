<?php

namespace App;

use \App\Models\Keywords;
use App\Config;

class Scrap
{

   private $linksTitlesArr = array();
   private $titlesArr = array();
   public $scrapedData = array();

   function __construct($websitesToScrap = [])
   {
      foreach ($websitesToScrap as $key => $value) { // iterates associative array
         $this->$key = $value[0]; // and memorizes the values as class properties

         $this->titlesArr[$key] = Keywords::findTitleByWebsite($value[0], '1000-01-01', '9999-12-31'); // loads all titles for each websites that are stored in db (to be compared to during scraping)
         
      };
      
   }

   public function ScrapWebsite($websitesToScrap)
   {
      foreach ($websitesToScrap as $key => $value) {

         $file = file_get_contents("https://" . $value[0] . "/");
         $doc = new \DOMDocument();
         libxml_use_internal_errors(true); // must be here (otherwise throws an error)
         $doc->loadHTML($file);
         libxml_use_internal_errors(false); // must be here (otherwise throws an error)

         $this->$key($doc, $key); // calls private functions that execute the scraping itself  

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
                  }
               }
            }
         } else {
            if (preg_match('/https/', $item->parentNode->attributes[0]->nodeValue)) {
               if (!in_array($item->textContent, $this->titlesArr[$key])) {
                  $this->linksTitlesArr[$key][$item->textContent] = $item->parentNode->attributes[0]->nodeValue;
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
      libxml_use_internal_errors(true); // must be here (otherwise throws an error)
      $doc->loadHTML($file);
      libxml_use_internal_errors(false); // must be here (otherwise throws an error)

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

}
