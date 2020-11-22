<?php

namespace App\Controllers; // ideally, namespaces correspond to folder organization (like it is here) for autoload to be working

use \Core\View;
use App\Models\Post;

class Posts extends \Core\Controller
{
    public function indexAction() // Action suffix was added so the methods would not be executed directly but rather through __call method defined in parent abstract Controller class
    {
        $posts = Post::getAll();
        View::renderTemplate("Posts/index.html", [
            'posts' => $posts
        ]);
    }
    public function addNewAction() // Action suffix was added so the methods would not be executed directly but rather through __call method defined in parent abstract Controller class
    {
        echo "Hello from the addNew action in the Posts controller!";
    }

    public function editAction() // Action suffix was added so the methods would not be executed directly but rather through __call method defined in parent abstract Controller class
    {
        echo "Hello from the edit action in the Posts controller!";
        echo '<p>Route parameters: <pre>' .
             htmlspecialchars(print_r($this->route_params, true)) . '</pre></p>'; // prints out route params saves as properties of the parent Controller class
    }
}
