<?php

namespace Core;

use PDO, PDOException;
use App\Config;

require_once("/opt/lampp/htdocs/App/Config.php"); // had to be required separately for cronjob to work

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

        if ($db === null) { // checks if connection is already established, that's why the logic for connection is extracted in a separate class so that we would not have to connect for each query to db
            // $host = 'localhost'; // constants from Config.php will be used instead
            // $dbname = 'mvc';
            // $username = 'root';
            // $password = '';
    
            try {
                $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // enables exception to be thrown for errors that occur in db

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        return $db;
    }
}
