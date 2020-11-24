<?php

namespace Core;

class View 
{
    // this is if template engine (twig) is not used
    public static function render($view, $args = []) // static classes do not instantiate objects but are called directly, we are passing file name that we want to display 
    {                                                // optionally it accepts an array as well (stays empty if not provided)
        $file = '../App/Views/' . $view; // relative to Core folder 
        extract($args, EXTR_SKIP); // extract turns associative array members into variables, EXTR_SKIP flag means that if there is a collision, don't overwrite the existing variable.

        if(is_readable($file)) {
            require $file; // loads required file
        } else {
            throw new \Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template  The template file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function renderTemplate($template, $args = [])
    {
        echo static::getTemplate($template, $args); // renders requested template
    }



    public static function getTemplate($template, $args = []) // method that only returns template (doesn't render it so it can be called by method above (renderTemplate) that would render it, but also by email sending method where rendering is not needed)
    {                                                         
        static $twig = null;

        if ($twig === null) {
            //$loader = new \Twig_Loader_Filesystem('../App/Views');
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);
            $twig->addGlobal('current_user', \App\Auth::getUser()); // we have to manually make this function is available to twig
                                                                    // this way username is available on all levels and a request to db doensn't have to be made for each view (it is made only once here)
            $twig->addGlobal('flash_messages', \App\Flash::getMessages());                                                                    
        }

        return $twig->render($template, $args);
    }
}