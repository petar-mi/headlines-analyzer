<?php
// controllers are classes that contain actions which are methods of these classes

namespace Core;

class Router
{
    protected $routes = []; // associative array of routes (routing table)
    protected $params = []; // to save the parameters from the matched route

    public function add($route, $params = []) // if a value is provided a parameter becomes optional (2nd parameter here)
    {
        $route = preg_replace('/\//', '\\/', $route); // convert the route to a regex: escape forward slashes
        //echo "<p>";
        //echo htmlspecialchars($route);
        //echo "</p>";
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route); // convert variables e.g. {controller}
        //echo "<p>";
        //echo htmlspecialchars($route);
        //echo "</p>";
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        //echo "<p>";
        //echo htmlspecialchars($route);
        //echo "</p";
        $route = '/^' . $route . '$/i'; // add start and end delimiters and case insensitive flag
        //echo "                        ";
        //echo "<div>";
        //echo htmlspecialchars($route);
        //echo "</div>";
        $this->routes[$route] = $params; // add a route to the routing table, keys in the array have have a value of $route, and value for each of those keys is an empty array
        // that empty array will be populated with params only after the request url is compared to regex from routing table
        // echo "<br />";
        // echo "<br />";
        // echo "<div>";
        // echo '<pre>';
        // echo htmlspecialchars(var_dump($this->routes));
        // echo '</pre>';
        // echo "<div>";
    }
    public function getRoutes() // Get all the routes from the routing table
    {
        return $this->routes;
    }
    public function match($url) 
    {
        // simple matching the url string to the routes in the routing table, setting the $params property if a route is found
        // foreach ($this->routes as $route => $params) {
        //     if ($url == $route) {
        //         return $this->params = $params; // sets the value of the $params property so that it returns true
        //     }
        // }

        // $reg_exp = "/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/"; // match to the fixed URL format /controller/action
        foreach ($this->routes as $route => $params) {
            // echo "ispitujem: " . htmlspecialchars($route);
            // echo "<br />";
            if (preg_match($route, $url, $matches)) { // evaluates if there are matches between requested url and and keys from $this->routes array that are actally regexes

                foreach ($matches as $key => $match) { // iterates mathes where $key is named captured group from regex, a $match is part of url
                    // echo "<br />";
                    // echo "key= " . $key . "    " . "match= " . $match;
                    // echo "<br />";
                    if (is_string($key)) {
                        $params[$key] = $match; // adds matches to asoc. array under keys that have a value of a named captured group from regexa
                        // echo "<br />";
                        // var_dump($params);
                        // echo "<br />";
                    }
                }
               
                // echo "<div>";
                // echo "<pre>";
                // echo htmlspecialchars(var_dump($this->routes));
                // echo "</pre>";
                // echo "</div>";

                $this->params = $params; // adds asoc. array $params (which is a value in $routes array) to a separate $params array
                return true; // returns true to index.php
            } else {
                // echo "<br />";
                // echo "A match for the requested route has not been found!";
                // echo "<br />";
            }
        }
    }

    public function getParams()
    { // get currently matched params
        return $this->params;
    }

    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $url The route URL
     *
     * @return void
     */
    public function dispatch($url)
    {
        //echo "<p>";
        //echo "<pre>";
        // echo htmlspecialchars(print_r($_GET, true)) . '</pre></p>'; // query params are available all the time unrelated to calling the function that clears query parama 
                                                                       // (that function only servs for the router to detects controller and action)
        $url = $this->removeQueryStringVariables($url); // removes query string from the end of url (if it exists) for the controller and action to be detected

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            // $controller = "App\Controllers\\$controller"; // hardcoded controller namespace
            $controller = $this->getNamespace() . $controller; // function that sets sets a nemaspace according to params in url and mathcing table routes

            if (class_exists($controller)) {
                // echo "<br />";
                // echo $controller;
                // echo "<br />";
                $controller_object = new $controller($this->params); // Posts class obj is instanteated and route params are passed to it (receives this as an arg through a contstructor of the abstract Controller parent class)
                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (preg_match('/action$/i', $action) == 0) { // checks if the action received from the url contains the strign 'action' 
                    $controller_object->$action(); // if it does not it calls a method 
                } else { // the other solution would be to set the action methods in classes as private so they could not be accessed from the outside
                    throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method"); // throws an exception if we try to directly acces the method
                }
            } else {
                // echo "Controller class $controller not found";
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            // echo 'No route matched.';
            throw new \Exception('No route matched.', 404); // 2nd arg is status code 404 (page not found)
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) { // don't see the point of this evaluation, ie I don't see how the eqution char = could end up in 1st part of the query string after the explode function was executed
                $url = $parts[0];                   // only if someone would type = in a query before the first ? mark. Here it is controlled by executing index action of home controller
            } else {
                $url = '';
            }
        }

        return $url;
    }

    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\'; // this is default, starting namespace

        if (array_key_exists('namespace', $this->params)) { // if namespace param exists retrieved from url 
            $namespace .= $this->params['namespace'] . '\\'; // namespace parameter is added to the namespace (here it is Admin) so that the action class could be found in a specified namespace (folder)
        }
        return $namespace;
    }
}


