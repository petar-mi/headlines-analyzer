<?php

namespace App\Controllers; // namespace that corresponds to folder organization

use \Core\View; // we use "use" so that we wouldn't have to name the whole namespace each time

/* use \App\Scrap;
use \App\Config;
use \App\Stemmer;
use App\Models\Keywords; */

class Home extends \Core\Controller
{
    public function indexAction() // suffix Action is added so that methods are not executed directly, but through  __call method defined in the parent abstract Controller class
    {
        // \Core\View::render('Home/index.php',[ 'name' => 'Dave', 'colours' => ['red', 'green', 'blue']]););  // it would have to be written like this if we did not use:  use \Core\View;    
        // View::render('Home/index.php', // the way static methods are called in php (here it is render method from View class), we're passing name of the file we want to render
        //              [                 // we also pass an array (that would be turned to variables in Core\View)
        //                 'name' => 'Dave',
        //                 'colours' => ['red', 'green', 'blue']
        //              ]); 
        // View::renderTemplate('Home/index.html', [ // here another method is inserted so that everything would go through twig template engine and index.php is replaced by index.html file
        //     'user'    => Auth::getUser() // we're passing user object that we acquired by new request to db based on user_id that we retrieved after 1st request to db (during logging)
        // ]); // commented out because in View.php by using addglobal method we made possible for twig to have access to username by calling Auth::getUser() method
        // this way we don't have to make a req to db each time 

        // include('/opt/lampp/htdocs/App/Cronjob.php'); // for crobjob debugging purposes only

        View::renderTemplate('Home/index.html');

        // echo Stemmer::stem("naslednik nasledniku , nasledstvo naslediti  nasledio"); // just for serbian stemmer testing

    }
}
