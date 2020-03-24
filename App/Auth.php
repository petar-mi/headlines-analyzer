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
           - A session creates a txt file in a temporary directory on the server where registered session variables and their values are stored. This data will be available to all pages on the site during that visit.
             A session ends when the user closes the browser or after leaving the site, the server will terminate the session after a predetermined period of time, commonly 30 minutes duration.
        Cookies
           - Cookies are text files stored on the client computer and they are kept of use tracking purpose. Server script sends a set of cookies to the browser. For example name, age, or identification number etc. The browser stores this information on a local machine for future use.
             When next time browser sends any request to web server then it sends those cookies information to the server and server uses that information to identify the user.*/

        session_regenerate_id(true); // radi bezbednosti (da bismo sprecili session fixation attacks) menjamo session id nakon izvrsenog logovanja, iako su podaci koji ce u cookie pod tim session id-em biti upisani isti (upisujemo user id iz baze)
        $_SESSION['user_id'] = $user->id; // snima session (koji je .txt file) na server (ne u bazu!) i to ukoliko je uspela autentifikacija tako sto u fajl upisuje i id usera iz baze (ne upisujemo ceo user objekat u session vec samo id da bi bilo manje podataka i da bi sve islo brze). Znaci session ima svoj id i on se sadrzi u nazivu fajla cookie-a, ali u sam fajl upisuje se id usera iz baze! ovaj podatak ostaje na serveru, ne ide u browser i bezbedno ga je koristiti.
        // echo session_save_path(); ovo ispisuje lokaciju na serveru na kojoj se skladiste session cookies

        if ($remember_me) {
            if ($user->rememberLogin()) {
                setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/'); // prvi arg. je naziv cookie-a, drugi i treci su vredonsti koje ce se sadrzati, a 4. se odnosi na to da su iz root-a dostupni i cookies stvoreni u subdirectories
            };
        }
    }

    public static function logout()
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

        static::forgetLogin(); // brisemo i cookie iz browsera kao i token u bazi koji je na osnovu njega napravljen
    }

    public static function rememberRequestedPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI']; // snima trazenu stranicu u $_SESSION
    }

    public static function getReturnToPage()
    {
        return $_SESSION['return_to'] ?? '/'; // vraca ili url zabelezen u session filu ili ako homepage
    }

    public static function getUser()
    {
        if (isset($_SESSION['user_id'])) { // // provera da li je user ulogovan tako sto gleda da li je setovan user_id properti u session-u
            return User::findByID($_SESSION['user_id']);  // vracamo user objekat dobijen novim upitom u bazu na osnovu user_id-a dobijenog prilikom prvog upita tj. prilikom logovanja
        } else {
            return static::loginFromRememberCookie();
        }
    }

    protected static function loginFromRememberCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false; // check if the cookie is present in the browser

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);
            if ($remembered_login && ! $remembered_login->hasExpired()) { // ukoliko postoji token u cookie-u i ukoliko nije istekao
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
                $remembered_login->delete(); // brise token u bazi
            }

            setcookie('remember_me', '', time() - 3600); // brisemo cookie iz browsera tako sto mu zadajemo expiry time u proslosti (proizvoljno odabrano 3600ms)
        }
    }
}
