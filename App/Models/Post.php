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
        // $host = 'localhost';  // zakomentarisano jer je konektovanje na bazu prebaceno u Core\Model.php
        // $dbname = 'mvc';
        // $username = 'root';
        // $password = '';
    
        try {
            // $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", // // zakomentarisano jer je konektovanje na bazu prebaceno u Core\Model.php
            //               $username, $password);

            $db = static::getDB(); // posto class Post extends Model onda imamo ovakav poziv staticke metode bez navodjenja imena klase kojoj pripada

            $stmt = $db->query('SELECT id, title, content FROM posts
                                ORDER BY created_at');
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
            
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}