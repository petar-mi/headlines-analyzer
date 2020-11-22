<?php

namespace App;

use App\Models\RememberedLogin;
use \App\Models\User;

class Auth
{
    public static function login($user, $remember_me)
    {
        /*
        Cookies and Sessions are used to store information. Cookies are only stored on the client-side machine, while sessions get stored on the client as well as a server.
        Session
           - A session creates a txt file in a temporary directory on the server where registered session variables and their values are stored. This data will be available 
             to all pages on the site during that visit.
             A session ends when the user closes the browser or after leaving the site, the server will terminate the session after a predetermined period of time, 
             commonly 30 minutes duration.
        Cookies
           - Cookies are text files stored on the client computer and they are kept of use tracking purpose. Server script sends a set of cookies to the browser.
             For example name, age, or identification number etc. The browser stores this information on a local machine for future use.
             When next time browser sends any request to web server then it sends those cookies information to the server and server uses that information to identify the user.*/

        session_regenerate_id(true); // for security reasons (to prevent session fixation attacks) we're changing session id after logging in, 
                                     // although the date itself that would be written in the cookie under that session id stays the same (we're saving user id from db)
        $_SESSION['user_id'] = $user->id; // saves session (which is .txt file) on server (not in db!) if authentification was successful by writting user id (from db) into a file (not writting the whole  
                                          // user object into session, but only id to lessen the data volume and cut the time needed. So, session has it's id and it is in the name of the cookie file, 
                                          // but in the file itself user id (from db) is stored. this data stays on the server, doesn't end up in the broswer and is safe to use.
        // echo session_save_path(); prints out the location on server where session cookies are stored

        if ($remember_me) {
            if ($user->rememberLogin()) { // session token is created, added as a property the user object (used in next line for setting a cookie) and saved to user record in a separate db table
                setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/'); // cookie to be sent to the browser - 1st arg is cookie (file)name, 2nd & 3rd are values that cookie file is going to 
                                                                                               // contain (previously created in $user->rememberLogin() method); 4th makes the cookies made in subdirectories available from the root
            };
        }
    }

    public static function logout() // code was copied from php documentation: https://www.php.net/manual/en/function.session-destroy.php
    {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        static::forgetLogin(); // we are erasing cookies from the browser as well as token in db after which the cookie was made 
    }

    public static function rememberRequestedPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI']; // saves the requested location in $_SESSION
    }

    public static function getReturnToPage()
    {
        return $_SESSION['return_to'] ?? '/'; // returns either location recorded in session or a homepage
    }                                         // returns $_SESSION['return_to'] if it exists, and is not NULL. If it does not exist, or is NULL, '/' is returned. Introduced in PHP 7  

    public static function getUser()
    {
        if (isset($_SESSION['user_id'])) { // // checks if user is logged in
            return User::findByID($_SESSION['user_id']);  // returns user obj received after a new request to db based on user_id, obtained during initial request ie logging-in
        } else {
            return static::loginFromRememberCookie();
        }
    }

    protected static function loginFromRememberCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false; // check if the cookie is present in the browser, returns false otherwise

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);
            if ($remembered_login && ! $remembered_login->hasExpired()) { // if token exists in cookie and if hasn't expired
                $user = $remembered_login->getUser();
                static::login($user, false);
                return $user;
            }
        }
    }

    protected static function forgetLogin()
    {
        $cookie = $_COOKIE['remember_me'] ?? false; // check if the cookie is present in the browser

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);
            if ($remembered_login) {
                $remembered_login->delete(); // deletes the token in db
            }

            setcookie('remember_me', '', time() - 3600); // deletes the cookie from the browser by setting its timestamp in the past (arbitrarily set to 3600ms)
        }
    }
}
