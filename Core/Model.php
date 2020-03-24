<?php

namespace Core;

use PDO, PDOException;
use App\Config;

require_once("/opt/lampp/htdocs/App/Config.php"); // mora da se uradi require rucno da bi radio cronjob

/**
 * Base model
 *
 * PHP version 5.4
 */
abstract class Model
{

    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) { // proverava se da li je konekcija vec napravljena, zato je i ekstrahovana logika za konekciju u zasebnu klasu kako se ne bi vrsilo konektovanje za svaki query
            // $host = 'localhost'; // zakomentarisano jer ce biti koristene konstante definisane u Config.php
            // $dbname = 'mvc';
            // $username = 'root';
            // $password = '';
    
            try {
                $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // omogucava da bude bacen exception kada se pojavi greska u bazi

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        return $db;
    }
}
