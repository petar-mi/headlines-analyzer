 <?php
   // localhost points to "/opt/lampp/htdocs/public" 
   // the above is defined in /opt/lampp/etc/httpd.conf as:  DocumentRoot "/opt/lampp/htdocs/public"
   // .htaccess file makes pretty URLs w/o the need to use ? sign in the request

   //echo 'Requested URL address= "' . $_SERVER['QUERY_STRING'] . '"'; 


   // require the controller class
   // require '../App/Controllers/Posts.php'; // commented-out because we use class autoloading

   // *** Routing ***
   // require '../Core/Router.php'; // commented-out because we use class autoloading

   // twig (or any other php package) is installed in project by opening terminal in project root folder and then enter: composer require "twig/twig:~1.0"
   // composer will then automatically create vendor folder and place twing (or other requested package) into it
   // there is also an option to make a .json file in root folder in which we name packages and then we only type composer install
   // require_once dirname(__DIR__) . '/vendor/twig/twig/lib/Twig/Autoloader.php'; // this way we have to manually find the location of autoloader.php for each package we want to use in a project
   // require dirname(__DIR__) . '/vendor/autoload.php'; // this way composer autoloads ALL packages installed in a project!

   require '../vendor/autoload.php'; // same as the upper line, does the same thing
   // require_once dirname(__DIR__) . '/vendor/test/vendor/autoload.php'; // had to be added manually for pepipost package to work (folder vendor/test)
                                                                          // temporaryly commented out for heroku deployment
   Twig_Autoloader::register(); // deprecated, but since we're using old 1.0 version it is necessary for twig to work

   // *** CLASS AUTOLOADER *** is converting name namespaced class name into a directory
   // *** commented-out because autoload was performed using composer (5th chapter, video 048), classes are listed in composer.json under "psr-4", and then we type in terminal: composer dump-autoload 
   // to refresh edited json file, when psr-4 is used then as a key in json-u we put namespace, and path to class files as value, 
   // both relative to project root. If composer is used it is not necessary for folders and namespaces to have the same names

   // spl_autoload_register(function ($class) { // when class is needed this function passes it as an argument argument so that the class could be found and loaded
   //     $root = dirname(__DIR__); // get the parent directory (of the public folder)
   //     $file = $root . '/' . str_replace('\\', '/', $class) . '.php'; // finds a file in which a class is located (file must have the same name as the class!)
   //     if (is_readable($file)) { // is_readable() is used to check whether the specified file exists and is readable or not
   //         require $root . '/' . str_replace('\\', '/', $class) . '.php'; // requires class that has previously been located
   //     }
   // });


   // Error and Exception handling
   error_reporting(E_ALL);
   set_error_handler('Core\Error::errorHandler'); // calling these two methods from Core\Error.php class
   set_exception_handler('Core\Error::exceptionHandler');

   session_start(); // starts/resumes a session on every request
   // by default session cookie is erased only after the browser is closed - it does not expire

   // Routing
   $router = new Core\Router(); // Core namespace added before Router class, the object of which is being instantiated
   // althugh we haven't previously required Router.php file, here autoload function will be executed
   // and string "Core\Router" will be passed to it, and since namespaces reflect folder structure, 
   // a and classes reflect filenames, a class that is needed will be located and a file containing it will be required

   // Add the routes
   // manually addes routes:
   $router->add('', ['controller' => 'Home', 'action' => 'index']); // this is manually added home route (although there is no need for it since we also will be using automatically detecting controllera and action using regex)
   $router->add('login', ['controller' => 'Login', 'action' => 'new']); // manually entered so that landing on login (and not only on login/new) would render login page
   $router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);

   


   // $router->add('posts', ['controller' => 'Posts', 'action' => 'index']); // basic route for controller
   // $router->add('posts/new', ['controller' => 'Posts', 'action' => 'new']); // and this for controller/action

   // using regex 
   $router->add('{controller}/{action}'); // if we request: http://localhost/signup/new signup will automatically be recognized as a controller, 
                                          // and new as action and the requested page will be rendered but, but only if it was handled in the controller and if view file exists as well
   $router->add('{controller}/{id:\d+}/{action}'); // adding the optin to have an id param between cotroller and action
   $router->add('admin/{controller}/{action}', ['namespace' => 'Admin']); // passing an array consisting of one elemenet

   $router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']); // special route that handles links for password change that were sent to emails. Links have a form similar to this: http://localhost/password/reset/4523ade1ef20c01fe7cd35c318db45dd
                                                                                                       // at the end there's a hexadecimal token containing digits and characters from "a" to "f"   
                                                                                                       // that's why we have the {token:[\da-f]+} at the end that makes 'token' param available to Password controller in reset method 
                                                                                                       // regex [\da-f]+ cathes hexadecimalni part of url that comes after last slash /                         
   $router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']); // same as previous route but for account activation links from email
   // Display the routing table
   //echo "<div>";
   //echo '<pre>';
   //var_dump($router->getRoutes());
   //echo htmlspecialchars(print_r($router->getRoutes(), true));// htmlspecialchars is used if we echo or print a string in PHP that contains some html tags, because without it
                                                                // php tries to execute those tags as html, (if they are not valid they are just skipped)
                                                               

   //echo '</pre>';
   //echo "</div>";



   // just a very basic example
   // Match the requested route
   // $url = $_SERVER['QUERY_STRING'];

   // if ($router->match($url)) {
   //     echo '<pre>';
   //     var_dump($router->getParams());
   //     echo '</pre>';
   // } else {
   //     echo "No route found for URL '$url'";
   // }

   $router->dispatch($_SERVER['QUERY_STRING']);
