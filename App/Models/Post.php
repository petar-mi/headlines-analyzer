<?php

namespace App\Models;

use PDO, PDOException;

/**
 * Post model
 *
 * PHP version 5.4
 */
class Post extends \Core\Model
{

    /**
     * Get all the posts as an associative array
     *
     * @return array
     */
    public static function getAll()
    {
        // $host = 'localhost';  // commented out because connecting to db has been transfered to Core\Model.php
        // $dbname = 'mvc';
        // $username = 'root';
        // $password = '';
    
        try {
            // $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", // commented out because connecting to db has been transfered to Core\Model.php
            //               $username, $password);

            $db = static::getDB(); // since Post class extends Model we have this way of calling a static method without naming the class it belongs to

            $stmt = $db->query('SELECT id, title, content FROM posts
                                ORDER BY created_at');
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
            
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}