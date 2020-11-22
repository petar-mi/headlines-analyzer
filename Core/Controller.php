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
        $method = $name . 'Action'; // since methods are named so that they contain Action suffix they cannot be executed since their name in client request does not have this suffix,
        // that's why this __call method is executed which adds suffix Action, and at the same time we have an opportunity to execute additional code before and after this method
        if (method_exists($this, $method)) {
            if ($this->before() !== false) { // if the called funct returns false, neither the main method or after() won't be executed
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            // echo "Method $method not found in controller " . get_class($this); // just logging, but we're throwing an exception instead in the next line
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
        // if left empty as it is, function could be overridden in a child class by the same name function, but the same name function DOES NOT necessarily need to be implemented in a child class
        // if this function was defined as abstract then it WOULD HAVE to be implemented in a child class
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */

    protected function after()
    {
        // if left empty as it is, function could be overridden in a child class by the same name function, but the same name function DOES NOT necessarily need to be implemented in a child class
        // if this function was defined as abstract that it WOULD HAVE to be implemented in a child class
    }

    public function redirect($url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303); // 2nd (true) and 3rd (303) argument should be placed, if missing it would be 302 by deafult
        exit;
    }

    public function requireLogin()
    {
        if (!Auth::getUser()) { // checks if the user is logged in
            Flash::addMessage('Please login to access that page', Flash::INFO); // we're passing the type of flash message through 2nd argument
            Auth::rememberRequestedPage(); // before redirecting we're saving the requested url to session file
            $this->redirect('/login'); // if not logged-in user is redirected to login page
        }
    }
}
