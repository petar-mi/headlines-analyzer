<?php

namespace App;
// vecina framework-a ima flash messages(notifications) koje se prenose preko session-a
class Flash
{
    const SUCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';

    public static function addMessage($message, $type = 'success') // drugi arg je opcionalan, i ako ne bude prosledjen bice 'success'
    {
        if(! isset($_SESSION['flash_notifications'])) { // ukoliko vec ne postoji kreira niz za notifikacije
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
            $messages = $_SESSION['flash_notifications']; // memorise niz u promenjivoj $messages
            unset($_SESSION['flash_notifications']); // brise ovaj clan iz niza (recimo ako se korisknik ulogovao nema potrebe da vise stoji obavestenje da treba da se uloguje)
                                                     // poruka se na taj nacin prikazuje samo za jedan request, vec refresh iste stranice je brise
            return $messages; // vraca prethodno memorisani niz u promenjivoj
        }
    }
}