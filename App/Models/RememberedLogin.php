<?php

namespace App\Models;

use App\Token;
use PDO;

class RememberedLogin extends \Core\Model
{
    public static function findByToken($token)
    {
        $token = new Token($token);
        $token_hash = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins
                WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        // we must bind requesta param values that were previously stored in User class to according placeholders (that is how prepared statements work)
        $stmt->bindParam(':token_hash', $token_hash, PDO::PARAM_STR); // we use PDO::PARAM_INT instead of PDO::PARAM_STR because id was saved as an integer in db
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // instead of an array (default) we we create an User class obj with data from db. 2nd arg finds a namespace for the called class (User in this case).
        $stmt->execute();
        return $stmt->fetch(); // fetch() returns false if nothing was found in db
    }

    public function getUser()
    {
        return User::findByID($this->user_id);
    }

    public function hasExpired()
    {
        return strtotime($this->expires_at) < time(); // we convert data token expiry time that come in string from db to time format and compare it with current time() value
    }

    public function delete() // to enable logout we erase from db the token that we got from the client browser cookie  
    {
        $sql = 'DELETE FROM remembered_logins
              WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $this->token_hash, PDO::PARAM_STR);
        $stmt->execute();
    }
}
