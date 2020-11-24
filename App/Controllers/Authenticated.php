<?php

namespace App\Controllers;

abstract class Authenticated extends \Core\Controller
{
    protected function before() // so called action before filter, method that runs automatically before any other action method. Most frameworks have such action filters
    {                           // it runs automatically because it is defined in the abstract Controller class (although it was left empty there) and from which this abstract class inherits
        $this->requireLogin(); // located at \Core\Controller which is the parent class and redirects to login page if user is not logged in
    }
}