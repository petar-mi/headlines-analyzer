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
        if ($code != 404) { // we simplify it by evaluating only two options: 404 page not found, while all the other errors we turn into 500 (server error)
            $code = 500; 
        }
        http_response_code($code);
        if (\App\Config::SHOW_ERRORS) { // if const SHOW_ERRORS = true in Config.php ie if it is a dev environment
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " .
                $exception->getLine() . "</p>";
        } else { // if it is production env
            $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt'; // creates a path for .txt file that will contain a log
            ini_set('error_log', $log); // instead hardcoding it directly into php.ini file, we programaticallt set path value for error_log
                                        // (previous line was nevertheless ignored and log was saved to /op/lampp/logs/error_log)
                                        // (in php.ini however it says:  error_log="/opt/lampp/logs/php_error_log")
            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            error_log($message);
            
            View::renderTemplate("$code.html");
        }
    }
}
