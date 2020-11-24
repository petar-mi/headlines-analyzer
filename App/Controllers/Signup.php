<?php

namespace App\Controllers; // namespace that coresponds to folder organization
use App\Models\User;
use \Core\View; // we use "use" so that we wouldn't have to name the whole namespace each time 

class Signup extends \Core\Controller
{
    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }
    public function createAction()
    {
        
        $user = new User($_POST); // although technically we could access request data from the model through $_POST superglobal, still we're instantiating an User class object passing it request params that would be handled in the constructor
        if ($user->save()) {; // saving to db
            $user->sendActivationEmail();
            // redirection is done by calling another action which changes url address in the browser, so that another address renders the page. 
            // If render success.html would be executed here without redirection, url in the browser stays on signup/create and by refreshing the page same date will be sent again to the server
            $this->redirect('/signup/success'); // same as Controller::redirect('/') since Signup extends Controller
        } else {
            // var_dump($user->errors); // displays errors stored in User class as an array
        
            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]); // reloads Signup page since validation errors are present and passes User class object in an associative array
        }
        
    }

    public function successAction()
    {
        View::renderTemplate('Signup/success.html'); 
    }

    public function activateAction()
    {
        User::activate($this->route_params['token']); // 'token' in route_params is enabled by manually defined route in index.php that catches token at the end of url string using regex. route_params themselves are enabled as properties by constructor function execution in abstract Controller class

        $this->redirect('/signup/activated');        
    }

    public function activatedAction() //  Show the activation success page
    {
        View::renderTemplate('Signup/activated.html');
    }

    // protected function before()
    // {
    //     echo "(before) ";
    //     // return false; // if we return false the main method (that comes after before() method) won't be executed thanks to evaluation __call method of the abstract Controler class that is a parent class
    // }

    // protected function after()
    // {
    //     echo " (after)";
    // }
}
