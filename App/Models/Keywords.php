<?php

namespace App\Models;

use PDO, PDOException;

require_once("/opt/lampp/htdocs/Core/Model.php"); // mora da se uradi require rucno da bi radio cronjob

class Keywords extends \Core\Model
{
    public static function test()
    {
        return "Keywords test";
    }
    public static function saveKeywords($website, $keywords)
    {
        try {

            $sql = 'INSERT INTO news_keywords (website, keywords)
                        VALUES (:website, :keywords)';

            $db = static::getDB(); // posto class Post extends Model onda imamo ovakav poziv staticke metode bez navodjenja imena klase kojoj pripada
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':website', $website, PDO::PARAM_STR);
            $stmt->bindValue(':keywords', $keywords, PDO::PARAM_STR);

            return $stmt->execute(); // ujedno izvrsava snimanje u bazu ali i vraca true ako je uspelo i false ukoliko nije
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        //return false; // if validation failed
    }

    public static function saveTitlesUrls($website, $title, $url)
    {
        try {

            $sql = 'INSERT INTO news_titles_and_urls (website, title, url)
                        VALUES (:website, :title, :url)';

            $db = static::getDB(); // posto class Post extends Model onda imamo ovakav poziv staticke metode bez navodjenja imena klase kojoj pripada
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':website', $website, PDO::PARAM_STR);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':url', $url, PDO::PARAM_STR);


            return $stmt->execute(); // ujedno izvrsava snimanje u bazu ali i vraca true ako je uspelo i false ukoliko nije
        } catch (PDOException $e) {
            //echo $e->getMessage();
        }
        // return false; // if validation failed
    }

    public static function findByWebsite($websites) // not used anymore
    {
        // echo empty($websites);
        // if (!empty($websites)) {

        // }
        $sqlPrefix = "SELECT * FROM news_keywords 
                WHERE";
        $sqlSuffix = "";

        for ($a = 0; $a < count($websites); $a++) {
            if ($a < count($websites) - 1) {
                $sqlSuffix .= ' website = :' . $a . " OR"; // morali su da se stave brojevi ($a) umesto stringova ($websites[$a]) zato sto tacka u stringu pravi problem
            } else {
                $sqlSuffix .= ' website = :' . $a;
            }
        }

        $sql = $sqlPrefix . $sqlSuffix;

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        for ($a = 0; $a < count($websites); $a++) {
            $stmt->bindParam(':' . $a, $websites[$a], PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 2); // fetch() vraca false ako nije pronadjeno nista u bazi
    }

    public static function findByKeyword($keyword, $fromDate, $toDate)
    {
        // SQL LIKE here looks only for exact $keyword match (anticipating that a blank space precedes/follows the keyword, although it could be any other sign as well)
        $sql = "SELECT * FROM news_titles_and_urls 
                WHERE snapshot_date BETWEEN :fromDate AND :toDate
                AND (title LIKE '% $keyword %' 
                OR title LIKE '% $keyword'
                OR title LIKE '$keyword %'
                OR title LIKE '% $keyword!%'
                OR title LIKE '% $keyword?%'
                OR title LIKE '% $keyword-%'
                OR title LIKE '% $keyword:%'
                OR title LIKE '% $keyword,%'
                OR title LIKE '% $keyword.%'
                OR title LIKE '% $keyword\"%'
                OR title LIKE '%\"$keyword%'
                OR title LIKE '%-$keyword%')";
                
        // SQL LIKE works as case insensitive regex search 
        // $sql = "SELECT * FROM news_titles_and_urls 
        //         WHERE title LIKE '%$keyword%'";
        //         AND snapshot_date BETWEEN '2020-03-01' AND '9999-12-31'";

        // pomocu RLIKE mogu se koristiti regex izrazi, ovo npr vraca sve substringove "ljudi" koji se nalaze na kraju stringa
        // $sql = "SELECT * FROM news_titles_and_urls 
        //         WHERE title RLIKE 'ljudi$'";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        //$stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR); // razlika izmedju bindValue i bindParam je sto bindParam salje vrednost tek u trenutku izvrsavanja $stmt->execute()
        $stmt->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
        $stmt->bindParam(':toDate', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(); // fetch() vraca false ako nije pronadjeno nista u bazi
    }

    public static function findTitleByWebsite($website, $fromDate, $toDate)
    {   // treba nam samo title zaradi poredjenja sa trenutnim naslovima
        $sql = "SELECT title FROM news_titles_and_urls 
                WHERE website = :website
                AND snapshot_date BETWEEN :fromDate AND :toDate";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':website', $website, PDO::PARAM_STR); // razlika izmedju bindValue i bindParam je sto bindParam salje vrednost tek u trenutku izvrsavanja $stmt->execute()
        $stmt->bindValue(':fromDate', $fromDate, PDO::PARAM_STR);
        $stmt->bindValue(':toDate', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // argument PDO::FETCH_COLUMN, 0 uzima prvu kolonu i od nje pravi indexed umesto associative array (key "title" biva izostavljen)
    }
}
