<?php

namespace Core;

/**
 * Error and exception handler
 *
 * PHP version 5.4
 */
class Error
{

    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $level  Error level
     * @param string $message  Error message
     * @param string $file  Filename the error was raised in
     * @param int $line  Line number in the file
     *
     * @return void
     */
    public static function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {  // to keep the @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler.
     *
     * @param Exception $exception  The exception
     *
     * @return void
     */
    public static function exceptionHandler($exception)
    {
        $code= $exception->getCode(); // gets the code from $exception obj (we have to previously set it as a 2nd arg when throwing the exception)
        if ($code != 404) { // pojednostavljujemo tako to razmatrazmo samo dve opcije, 404 page not found, dok sve druge greske pretvaramo u 500 (server error)
            $code = 500; 
        }
        http_response_code($code);
        if (\App\Config::SHOW_ERRORS) { // ukoliko je const SHOW_ERRORS = true u Config.php tj. ako se radi o dev okruzenju
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";
        } else { // ukoliko se radi o production env
            $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt'; // kreira path za .txt fajl koji ce sadrzati log
            ini_set('error_log', $log); // umesto direktno u php.ini fajl, programski zadajemo vrednost path-a za error_log
                                        // u sustini ovo se ignorise i log se snima u fajl /op/lampp/logs/error_log
                                        // jos vise zbunjuje sto u php.ini stoji da bi trebalo da se snima u: error_log="/opt/lampp/logs/php_error_log"
            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            error_log($message);
            //echo $log;
            //echo "<h1>An error occured</h1>"; // korisnik ce videti samo ovaj red
            //echo ($code == 404) ? "<h1>Page not found</h1>" : "<h1>An error occured</h1>"; // umesto ovoga renderovacemo odgovarajucu stranicu
            View::renderTemplate("$code.html");
        }
    }
}
