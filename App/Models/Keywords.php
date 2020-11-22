<?php

namespace App\Models;

use PDO, PDOException;

require_once("/opt/lampp/htdocs/Core/Model.php"); // has to be required manually for cron to work

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

            $db = static::getDB(); // since Post class extends Model we have this way of calling static method without naming the class it belongs to
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':website', $website, PDO::PARAM_STR);
            $stmt->bindValue(':keywords', $keywords, PDO::PARAM_STR);

            return $stmt->execute(); // saves to db and at the same time returns true if it was a success (and false if it failed)
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

            $db = static::getDB(); // since Post class extends Model we have this way of calling a static method without naming the class it belongs to
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':website', $website, PDO::PARAM_STR);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':url', $url, PDO::PARAM_STR);


            return $stmt->execute(); // since Post class extends Model we have this way of calling s static method without naming the class it belongs to
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
                $sqlSuffix .= ' website = :' . $a . " OR"; // had to be numbers ($a) instead of strings ($websites[$a]) because dot (.) in a string was making a problem
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
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 2); // fetch() returns false if nothing was found in db
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

        // with RLIKE it is possible to use regex, here fow example it returns all substrings "ljudi" that are found at the end of a string
        // $sql = "SELECT * FROM news_titles_and_urls 
        //         WHERE title RLIKE 'ljudi$'";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
        $stmt->bindParam(':toDate', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(); // fetch() returns false if nothing was found in db
    }

    public static function findTitleByWebsite($website, $fromDate, $toDate)
    {   // we only need title to compare it to current titles
        $sql = "SELECT title FROM news_titles_and_urls 
                WHERE website = :website
                AND snapshot_date BETWEEN :fromDate AND :toDate";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':website', $website, PDO::PARAM_STR); // difference between bindValue and bindParam is that bindParam sends a value in the very moment of executing $stmt->execute()
        $stmt->bindValue(':fromDate', $fromDate, PDO::PARAM_STR);
        $stmt->bindValue(':toDate', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // argument PDO::FETCH_COLUMN, 0 takes 1st column and makes indexed instead of associative array (key "title" is excluded)
    }
}
