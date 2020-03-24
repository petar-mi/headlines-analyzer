<?php
// controllers are classes that contain actions which are methods of these classes

namespace Core;

class Router
{
    protected $routes = []; // associative array of routes (routing table)
    protected $params = []; // to save the parameters from the matched route

    public function add($route, $params = []) // ukoliko se navede vrednost paramatera (kao sto je navedeno ovde za $params) onda to postaje opcioni arg/param koji se moze i ne mora proslediti
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
        $this->routes[$route] = $params; // add a route to the routing table, keys u nizu imaju vrednost $route, a value za svaki od tih keys je prazan niz
        // taj prazan niz bice populisan params-ima tek nakon sto bude uporedjena unesena url adresa sa regex-om iz routing table-a
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
    public function match($url) // prima unesenu url adresu kao parametar
    {
        // simple matching the url string to the routes in the routing table, setting the $params property if a route is found
        // foreach ($this->routes as $route => $params) {
        //     if ($url == $route) {
        //         return $this->params = $params; // setuje vrednost propertija $params i samim tim vraca true
        //     }
        // }

        // $reg_exp = "/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/"; // match to the fixed URL format /controller/action
        foreach ($this->routes as $route => $params) {
            // echo "ispitujem: " . htmlspecialchars($route);
            // echo "<br />";
            if (preg_match($route, $url, $matches)) { // ispituje da li postoje poklapanja unetog url-a sa keys-ovima niza $this->routes koji su zapravo regexi

                foreach ($matches as $key => $match) { // izlistava poklapanja, gde je $key named captured group iz regexa, a $match deo url-a
                    // echo "<br />";
                    // echo "key= " . $key . "    " . "match= " . $match;
                    // echo "<br />";
                    if (is_string($key)) {
                        $params[$key] = $match; // dodaje matcheve u asoc. niz pod keys-om koji ima vrednost named captured group-a iz regexa
                        // echo "<br />";
                        // var_dump($params);
                        // echo "<br />";
                    }
                }
                // *** VAZNO (sledeci red!) ***
                // $this->routes[$route] = $params; // ovo je deo koji sam ja dodao jer trebalo bi da se niz parametara dodaju u niz $this->routes kao vrednost za odgovarajuci key, a ne u zaseban niz $this->params 

                // echo "<div>";
                // echo "<pre>";
                // echo htmlspecialchars(var_dump($this->routes));
                // echo "</pre>";
                // echo "</div>";

                $this->params = $params; // dodaje asoc. niz $params (koji je vrednost u nizu $routes) u zaseban niz $params
                return true; // vraca true u index.php ne bi li se ispisale rute, izvorno je u redu iznad pisalo: return $this->params = $params; tako da ovaj red nije bio potreban, ali to je bilo pomalo zbunjujuce
            } else {
                // echo "<br />";
                // echo "Nije pronadjen match za ispitivanu rutu!";
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
        // echo htmlspecialchars(print_r($_GET, true)) . '</pre></p>'; // query parametri su dostupni sve vreme nezavisno od toga da li smo pozvali funkciju koja cisti query parametre (ona sluzi samo da bi router detektovao kontroler i akciju)
        $url = $this->removeQueryStringVariables($url); // poziva funkciju koja uklanja query string sa kraja url-a (ako postoji) kako bi se detektovali kontroler i akcija

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            // $controller = "App\Controllers\\$controller"; // hardcoded controller namespace
            $controller = $this->getNamespace() . $controller; // poziva se funkcija koja odredjuje namespace u zavisnosti od parametara url-a i mathcing table routes-a

            if (class_exists($controller)) {
                // echo "<br />";
                // echo $controller;
                // echo "<br />";
                $controller_object = new $controller($this->params); // instancira se objekat klase Posts i prosledjuju mu se route parametri (zato sto kroz konstruktor apstraktne parent klase (Controller) prima to kao argument)
                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (preg_match('/action$/i', $action) == 0) { // pretrazuje da li action dobijen iz url-a sadrzi rec action 
                    $controller_object->$action(); // ukoliko ne sadrzi poziva metod 
                } else { // drugo resenje bilo bi da smo action metode u klasama odredili kao private pa im se ne bi moglo pristupiti spolja
                    throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method"); // baca gresku ako pokusamo da direktno pristupimo metodi
                }
            } else {
                // echo "Controller class $controller not found";
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            // echo 'No route matched.';
            throw new \Exception('No route matched.', 404); // drugi arg je status code 404 (page not found)
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

            if (strpos($parts[0], '=') === false) { // ne znam cemu tacno sluzi ovo ispitivanje, tj. ne vidim kako bi znak = zavrsio u prvom delu query stringa nakon izvrsene explode funkcije
                $url = $parts[0];                   // osim ako neko ne bi kucao u query = ispred prvog znata pitanja... ovde se to kontrolise tako sto se izvrsava index akcija home controllera 
            } else {
                $url = '';
            }
        }

        return $url;
    }

    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\'; // ovo je defaultni, pocetni namespace

        if (array_key_exists('namespace', $this->params)) { // ukoliko postoji i parametar namespace dobijen iz url-a 
            $namespace .= $this->params['namespace'] . '\\'; // nazivu namespace dodaje se i vrednost namespace parametra (ovde konkretno Admin) kako bi se pronasla action class u odgovarajucem folderu
        }
        return $namespace;
    }
}


