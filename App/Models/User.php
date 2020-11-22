<?php

namespace App\Models;

use App\Token;
use PDO, PDOException;
use App\Mail;
use Core\View;

/**
 * Post model
 *
 * PHP version 5.4
 */
class User extends \Core\Model
{
    public $errors = [];

    public function __construct($data = []) // executes when new user object is instatiated in Signup.php 
    // by setting the value of the argument to an empty array we've made the arg optional, because new user object is instatiated in Signup.php data are passed, and when it is created during login from the db there are no data here and the array stays empty
    {
        foreach ($data as $key => $value) { // iterates through the object that contains request params as associative key-value pairs 
            $this->$key = $value; // and memorizes them as properties of the User class
        };
    }
    public function save()
    {
        // $host = 'localhost';  // commented out because connecting to db has been transfered to Core\Model.php
        // $dbname = 'mvc';
        // $username = 'root';
        // $password = '';

        $this->validate(); // 1st we're validating user input

        if (empty($this->errors)) { // we're only saving to db if there are no errors 
            try {

                $password_hash = password_hash($this->password, PASSWORD_DEFAULT); // we're creating a password hash that automatically adds unique salt and that would be the value we're going to store in db
                $token = new Token(); // creating  a token only for account activation purpose
                $hashed_token = $token->getHash(); // we're only storing hashed token in the db (after activation the value of the hashed token in db will be set to null in activate method of this class)
                $this->activation_token = $token->getValue(); // token needed to activate the account will be sent by email and here we're making it a User class property (so that it could be used from sendActivationEmail() method)
                $sql = 'INSERT INTO users (name, email, password_hash, activation_hash)
                        VALUES (:name, :email, :password_hash, :activation_hash)'; // here we're puttinf the placeholders marked by : preffix, because we're gonna be using prepared statements

                $db = static::getDB(); // since Post class extends Model class we have this way of calling a static method without naming the class it belongs to
                $stmt = $db->prepare($sql);

                // we have to bind request parameters values that were previously stored in a User class for corresponding placeholders (has to be done when using prepared statements)
                $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
                $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

                return $stmt->execute(); // at the same time saves to db and returns true if it was a success and false if it fails
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
            return false; // if validation failed
        }
    }
    public function validate()
    {
        if ($this->name == '') { // name
            $this->errors[] = 'Name is required';
        }
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) { // email
            $this->errors[] = 'Invalid email';
        }
        if (static::emailExists($this->email, $this->id ?? null)) { // 2nd argument is sent only if it exists, namely if user already exists in a db (which is the case if the user is only changing a password)
            $this->errors[] = 'Email already taken';
        }
        if (isset($this->password)) { // password is not validated if it is only profile edit without password change

            if (strlen($this->password) <= 6) { // pass lenght
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password must contain at least one letter';
            }
            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password must contain at least one number';
            }
        }
    }

    public static function emailExists($email, $ignore_id = null) // checks for a user with the same e-mail, 2nd param is optional for password changing/profile editing in which case there would be an already existing user id 
    {
        $user = static::findByEmail($email);
        if ($user) {
            if ($user->id != $ignore_id) {
                return true; // returns true only if email already exists in db and its user id differs from $ignore_id (in that case it is only about password changing or profile editing for an existing user) 
            }
        }
        return false;
    }

    public static function findByEmail($email) // checks for a user providing an email 
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':email', $email, PDO::PARAM_STR); // bindValue & bindParam differnce is that bindParam sends a value just at the moment of executing $stmt->execute()
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // instead of an array which is deafult the result will be an object of the User class populated by the data from db. 2nd param finds a namespace for the called class (User in this case).
        $stmt->execute();
        return $stmt->fetch(); // fetch() returns false if nothing was found in the db
    }

    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);
        if ($user && $user->is_active) { // also checks is_active bool which is part of the user record in db to see if the user account is activated since it has by default been set to 0 (false) at the moment of creation of user record
            if (password_verify($password, $user->password_hash)) { // when comparing with hashed password stored in db we use password_verify instead of password_hash method
                return $user;
            }
        }
        return false;
    }

    public static function findByID($id)  // request to db providing user_id that was retrieved on login
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // we're using PDO::PARAM_INT instead PDO::PARAM_STR because id is stored as a integer in db
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // instead of an array which is deafult the result will be an object of the User class populated by the data from db. 2nd param finds a namespace for the called class (User in this case).
        $stmt->execute();
        return $stmt->fetch(); // fetch() returns false if nothing was found in the db
    }

    public function rememberLogin() // saving session token in db if user opted for "remember me" when logging
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue(); // we're storing token value as a User class property as well (by using $this) so that we can access it from Auth.php
        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30; // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at) 
                VALUES (:token_hash, :user_id, :expires_at)'; // saving to separate table remembered_logins because it is possible that cookies will be set on different devices for the same user

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR); // timestamp must be converted in this way to be stored in sql

        return $stmt->execute();
    }

    public static function sendPasswordReset($email)
    {        $user = static::findByEmail($email);
        if ($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
            };
        }
    }

    protected function startPasswordReset()
    {
        $token = new Token();
        $this->password_reset_token = $token->getValue(); // stored as an User object property so to be available in sendPasswordResetEmail() and sent to the user via email
        $hashed_token = $token->getHash(); // we're only storing hashed token in the db
        $expiry_timestamp = time() + 60 * 60 * 2; // 2hr from now

        $sql = 'UPDATE users
               SET password_reset_hash = :token_hash,
                   password_reset_expires_at = :expires_at
               WHERE id = :id'; // saves additional data to user record (user was previously identified by email) - pass reset hashed token and expiry time

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token; // creating url to be sent to the user via email and that would have a form similar to this:
        // http://localhost/password/reset/4523ade1ef20c01fe7cd35c318db45dd
        // next 2 lines are commented out (although it is a legitimate though hardcoded way), but we will be using twig and View class insteadto create text and html to send via email
        //$text = "Please click on the following link to reset your password: $url"; // not used in pepipost
        //$html = "Please click <a href=\"$url\">here</a> to reset your password";
        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]); // also passing url to have it available in twig
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]); // also passing url to have it available in twig

        Mail::send($this->email, "Password reset", $text, $html); // we're passing text although pepipost does not require it (mailgun does though)
    }

    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash(); // to find hashed token in db we have to hash it first as well

        $sql = 'SELECT * FROM users
               WHERE password_reset_hash = :token_hash'; // returns only users which have the same stored token as the one received by email

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class()); // instead of an array which is deafult the result will be an object of the User class populated by the data from db. 2nd param finds a namespace for the called class (User in this case).
        $stmt->execute();
        $user = $stmt->fetch(); // fetch() returns false if nothing was found in the db

        if ($user) {
            if (strtotime($user->password_reset_expires_at) > time()) { // returning user object only if token has not expired
                return $user;
            }
        } // If we're not explicitely returning anything in PHP, NULL will be returned by default
    }

    public function resetPassword($password)
    {
        $this->password = $password; // we're changing password property in User object which has previously been instantiated by request to db which has returned an User object (instead of an array: $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class())
        $this->validate(); // just as when creating a user we're doing backend password validation
        if (empty($this->errors)) { // update user record (true if errors array is empty)

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT); // we're creating a password hash that automatically adds unique salt and that would be the value we're going to store in db
            $sql = 'UPDATE users
                        SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                        WHERE id = :id'; // password updating and setting to NULL both password_reset_hash and its expiry time

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            return $stmt->execute();
        }

        return false; // when validation fails & we don't update the record
    }

    public function sendActivationEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]); // also passing url to have it available in twig
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]); // also passing url to have it available in twig

        Mail::send($this->email, "Account activation", $text, $html); // we're passing text although pepipost does not require it (mailgun does though)
    }

    public static function activate($value) // account activation (changing is_active attributa in db) providing token value (through clicking on the activation link sent via email) 
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();
        $sql = 'UPDATE users
                        SET is_active = 1,
                        activation_hash = NULL
                        WHERE activation_hash = :hashed_token'; // looks up for user based on hash of a token sent via emial and sets is_active to 1 (true) while alse setting activation_hash to NULL

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateProfile($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        if ($data['password'] != '') { // updates password only when user has entered a new one
            $this->password = $data['password'];
        }

        $this->validate();

        if (empty($this->errors)) {
            $sql = 'UPDATE users
                    SET name = :name,
                        email = :email';

            if (isset($this->password)) {    // adds password changing part to sql statement only if new password has been set         
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            if (isset($this->password)) { // binding only if new password has been set
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        return false; // if there was an error in validation
    }

    public function updatePreferredKeywords($keywords)
    {
        $sql = 'UPDATE users
                SET preferred_keywords = :preferred_keywords
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':preferred_keywords', $keywords, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function readPreferredKeywords()
    {
        $sql = 'SELECT preferred_keywords FROM users
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateClickedTitles($titles)
    {
        $sql = 'UPDATE users
                SET clicked_titles = :clicked_titles
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':clicked_titles', $titles, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function readClickedTitles()
    {
        $sql = 'SELECT clicked_titles FROM users
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetch();
    }
}
