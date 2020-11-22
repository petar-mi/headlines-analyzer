<?php

namespace App;
// most of the frameworks have flash messages (notifications) which are carried using session 
class Flash
{
    const SUCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';

    public static function addMessage($message, $type = 'success') // 2nd arg is optional, if not provided it will be 'success'
    {
        if(! isset($_SESSION['flash_notifications'])) { // creates flash_notifications array if it does not exist already
           $_SESSION['flash_notifications'] = [];
        }
        $_SESSION['flash_notifications'][] = [
            'body' => $message,
            'type' => $type
        ]; // appends to the array
    }

    public static function getMessages()
    {
        if(isset($_SESSION['flash_notifications'])) {
            $messages = $_SESSION['flash_notifications']; // memorizes the array in $messages variable
            unset($_SESSION['flash_notifications']); // erases this array element from session array (e.g. if user has logged in there is no need anymore for a notification telling to log in)
                                                     // this way a message is only displayed once for one request, a page refresh already erases it 
            return $messages;
        }
    }
}