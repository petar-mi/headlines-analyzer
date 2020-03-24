<?php

namespace Core;

use App\Auth;
use App\Flash;
use App\Models\Keywords;
use Nesk\Puphpeteer\Puppeteer;

// Base Controller

abstract class Controller
{
    protected $route_params = []; // params from the matched route

    public function __construct($route_params) // route params will be passed to this constructor function when we instance an object
    {
        $this->route_params = $route_params;
    }

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @return void
     */
    public function __call($name, $args)
    {
        $method = $name . 'Action'; // posto su metode u klasi preimenovane tako da sadrze Action sufiks one ne mogu biti izvrsene jer se ne poklapaju sa onim sto je dobijeno iz requesta kao action
        // zbog toga se izvrsava ova __call metoda koja dodaje sufiks kako bi se te metode mogle izvrsiti, a imamo priliku da pre i posle pozivanja te akcije izvrsimo i neke dodatne metode
        if (method_exists($this, $method)) {
            if ($this->before() !== false) { // ovo ispitivanje omogucava da posto je ovo pozivanje funkcije, ukoliko u funkciji vratimo false, nece se izvrsiti ni glavna metoda niti metoda after()
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            // echo "Method $method not found in controller " . get_class($this); // umesto da samo logujemo bacamo exception
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
        // ako se ostavi ovako prazna, funkcija moze da se 'pregazi' istoimenom funkcijom u child class ali NE mora da se obavezno implementira i u child class
        // ukoliko bi ovde bilo navdedeno kao abstract function before(); onda bi obavezno morala da se implementira u child class
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */

    protected function after()
    {
        // ako se ostavi ovako prazna, funkcija moze da se 'pregazi' istoimenom funkcijom u child class ali NE mora da se obavezno implementira i u child class
        // ukoliko bi ovde bilo navdedeno kao abstract function before(); onda bi obavezno morala da se implementira u child class
    }

    public function redirect($url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303); // drugi (true) i treci (303) argument se valja staviti u ovom slucaju, ako se izostavi, bice 302 po deafultu
        exit;
    }

    public function requireLogin()
    {
        if (!Auth::getUser()) { // proverava da li je korisnik ulogovan
            Flash::addMessage('Please login to access that page', Flash::INFO); // drugim arg prosledjujemo tip poruke
            Auth::rememberRequestedPage(); // pre preusmeravanja belezimo trazeni url u session file
            // uncomment next line for tutorial code to work!
            $this->redirect('/login'); // ukoliko nije ulogovan korisnik se preusmerava na stranicu za logovanje
        }
    }
}
