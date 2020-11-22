<?php

namespace App\Controllers; // namespace that corresponds to folder organization

use \Core\View; // we use "use" so that we wouldn't have to name the whole namespace each time


class Items extends Authenticated // Items is a child of the Authenticated class since we need the user to be logged in 
                                  // for all action methods and this way we don't have to repeat that code in each of them
{    
    public function indexAction() 
    {
        // $this->requireLogin(); // located at \Core\Controller which is the parent class and redirects to login page if user is not logged in
        // ↑↑↑ commented-out because we would have to repeat the code for each action method in this controller, instead we've moved the code to before method 
        // located in the abstract Authenticated class which is a parent of this class and a child of the abstract Controller class
        View::renderTemplate('Items/index.html'); // only if the user is logged-in the page is rendered
    }

    public function newAction() // just some random actions
    {
        echo "new action";
    }

    public function showAction() // just some random actions
    {
        echo "show action";
    }
}