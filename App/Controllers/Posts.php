<?php

namespace App\Controllers; // idealno, nazivi namespace-ova treba da korespondiraju organizaciji foldera (kao sto je ovde slucaj) kako bi radio autoload za klase!

use \Core\View;
use App\Models\Post;

class Posts extends \Core\Controller
{
    public function indexAction() // dodat sufiks Action kako se metode ne bi izvrsavale direktno nego da bi isle preko __call metode definisane u parent abstract Controller class
    {
        //echo "Hello from the index action in the Posts controller!";
        //echo '<p>Query string parameters: <pre>' .
        //htmlspecialchars(print_r($_GET, true)) . '</pre></p>'; // ispisuje query parametetre iz request-a
        $posts = Post::getAll();
        View::renderTemplate("Posts/index.html", [
            'posts' => $posts
        ]);
    }
    public function addNewAction() // // dodat sufiks Action kako se metode ne bi izvrsavale direktno nego da bi isle preko __call metode definisane u parent abstract Controller class
    {
        echo "Hello from the addNew action in the Posts controller!";
    }

    public function editAction() // // dodat sufiks Action kako se metode ne bi izvrsavale direktno nego da bi isle preko __call metode definisane u parent abstract Controller class
    {
        echo "Hello from the edit action in the Posts controller!";
        echo '<p>Route parameters: <pre>' .
             htmlspecialchars(print_r($this->route_params, true)) . '</pre></p>'; // ispisuje route parametetre koji su sacuvani kao properti parent klase Controller
    }
}
