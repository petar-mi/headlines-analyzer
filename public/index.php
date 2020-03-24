 <?php
    // localhost points to "/opt/lampp/htdocs/public" 
    // the above is defined in /opt/lampp/etc/httpd.conf as:  DocumentRoot "/opt/lampp/htdocs/public"
    // .htaccess file makes pretty URLs w/o the need to use ? sign in the request

    //echo 'Requested URL address= "' . $_SERVER['QUERY_STRING'] . '"'; 


    // require the controller class
    // require '../App/Controllers/Posts.php'; // zakomentarisano jer koristimo Autoloading klasa

    // *** Routing ***
    // require '../Core/Router.php'; // // zakomentarisano jer koristimo Autoloading klasa

    // twig (ili bilo koji drugi php paket) se instalira u projektu tako sto se otvori terminal u root folderu projekta i kuca: composer require "twig/twig:~1.0"
    // kompozer ce onda automatski napraviti folder vendor i u njega smestiti twig ili drugi trazeni paket
    // takodje postoji i opcija da se u root folderu napravi .json fajl u kome se navedu paketi i zatim u root folderu projekta kuca samo composer install
    // require_once dirname(__DIR__) . '/vendor/twig/twig/lib/Twig/Autoloader.php'; // na ovaj nacin moramo rucno pronalaziti lokaciju autoloader.php za svaki paket koji zelimo da upotrebimo u projektu
    // require dirname(__DIR__) . '/vendor/autoload.php'; // na ovaj nacin composer vrsi autoload SVIH paketa instaliranih u projektu!
    
    require '../vendor/autoload.php'; // isto sto i gornji red, svejedno
    require_once dirname(__DIR__) . '/vendor/test/vendor/autoload.php'; // moralo rucno da se doda kako bi radio pepipost paket za slanje email-ova (folder vendor/test)
    Twig_Autoloader::register(); // ovo je deprecated metoda, ali posto koristim stariju verziju 1.0, neophodna je da bi radio twig

    // *** CLASS AUTOLOADER *** is converting name namespaced class name into a directory
    // *** zakomentarisano jer su izvrsen autoload pomocu composer-a (5. poglavlje, video 048), klase su navedene u composer.json pod "psr-4", a zatim se u terminalu kuca: composer dump-autoload 
    // kako bi se osvezio editovan json fajl, inace kada se koristi psr-4 onda se kao key u json-u navodi namespace, a kao value path to class files, 
    // i jedno i drugo relativno u odnosu na root projekta, ako se koristi composer autoloader nije nuzno da folderi i namespace-ovi imaju ista imena
    
    // spl_autoload_register(function ($class) { // ukoliko se zatrazi klasa ova funkcija je prosledjuje kao argument kako bi klasa bila pronadjena i ucitana
    //     $root = dirname(__DIR__); // get the parent directory (of the public folder)
    //     $file = $root . '/' . str_replace('\\', '/', $class) . '.php'; // pronalazi file u kome se klasa nalazi (file mora nositi isti naziv kao i klasa!!!)
    //     if (is_readable($file)) { // is_readable() is used to check whether the specified file exists and is readable or not
    //         require $root . '/' . str_replace('\\', '/', $class) . '.php'; // vrsi require odgovarajuce klase
    //     }
    // });


    // Error and Exception handling
    error_reporting(E_ALL);
    set_error_handler('Core\Error::errorHandler'); // pozivamo ove dve metode iz klase Core\Error.php
    set_exception_handler('Core\Error::exceptionHandler');

    session_start(); // starts/resumes a session on every request
                     // po defaultu session cookie se brise tek kada se zatvori browser- it does not expire

    // Routing
    $router = new Core\Router(); // dodat Core namespace ispred Router klase ciji se objekat instancira
                                 // iako nismo prethodno izvrsili require za Router.php file, ovde ce se aktivirati autoload funkcija kojoj
                                 // ce kao argument biti prosledjen string "Core\Router" i posto namespace odgovaraju strukturi foldera, 
                                 // a klase nazivima fajlova, klasa ce biti locirana i bice izvrsen require trazenog fajla
    
    // Add the routes
    $router->add('', ['controller' => 'Home', 'action' => 'index']); // rucno dodavanje ruta, ova je za home, mada nema potrebe s obzirom da dole imamo automatsko prepoznavanje controllera i action-a pomocu regexa
    $router->add('login', ['controller' => 'Login', 'action' => 'new']); // uneseno rucno cisto da bi i landing na url: login (a ne samo na login/new) ispisao login stranicu
    $router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
    $router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']); // ovo je specijalna ruta koja obradjuje linkove na koje se klikne iz mail-a za promenu sifre i ciji url je u obliku slicnom ovom: http://localhost/password/reset/4523ade1ef20c01fe7cd35c318db45dd
                                                                                                        // gde je na kraju token koji hexadecimal te se sastoji iz brojeva i slova od a do f   
                                                                                                        // zato je ovde na kraju dodato {token:[\da-f]+} sto znaci da ce u Password kontroleru, u metodi reset biti dostupan i parametar 'token' pri cemu je ovde ostavljen regex [\da-f]+ koji sluzi da uhvati hexadecimalni deo url-a nakon poslednje kose crte /                         
    $router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']); // isto kao prethodni red samo za aktivaciju naloga
   
    // $router->add('posts', ['controller' => 'Posts', 'action' => 'index']); // ovo je osnovna ruta za kontroler
    // $router->add('posts/new', ['controller' => 'Posts', 'action' => 'new']); // a ovo kontroler/akcija
    $router->add('{controller}/{action}'); // ako u browseru kucamo npr: http://localhost/signup/new automatski ce signup biti prepoznato kao controller, a new kao action i prikazace se odgovarajuca stranica ali mora biti hendlovana u kontroleru i mora postojati u views
    $router->add('{controller}/{id:\d+}/{action}');
    $router->add('admin/{controller}/{action}', ['namespace' => 'Admin']); // prosledjuje se i niz od jednog clana


    // Display the routing table
    //echo "<div>";
    //echo '<pre>';
    //var_dump($router->getRoutes());
    //echo htmlspecialchars(print_r($router->getRoutes(), true));// ako se radi print ili echo stringa u PHP a string sadrzi neke html tagove, 
    // php te tagove pokusava da izvrsi kao html, a ako nisu validni samo ih preskoci
    // da bi se odstampao realni sadrzaj stringa potrebno je staviti htmlspecialchars

    //echo '</pre>';
    //echo "</div>";




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
