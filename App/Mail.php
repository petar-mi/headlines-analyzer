<?php

namespace App;
use PepipostAPILib; // da bi uvezao ovaj namespace morao sam rucno da dodam red za autoload u index.php, a glasi ovako: require_once dirname(__DIR__) . '/vendor/test/vendor/autoload.php'; 

class Mail
{
    public static function send($to, $subject, $text, $html) // u pepipost nema opcija za slanje plain text-a, poruka se salje samo u okviru html-a
    {
        $client = new PepipostAPILib\PepipostAPIClient();
        $emailController = $client->getEmail();

        // Your Pepipost API Key
        $apiKey = Config::PEPIPOST_API_KEY; #add apikey from panel here  https://app.pepipost.com/index.php/    login: bobanabg@mail.com sifra: Novasifra123

        $body = new PepipostAPILib\Models\EmailBody();

        // List of Email Recipients
        $body->personalizations = array();
        $body->personalizations[0] = new PepipostAPILib\Models\Personalizations;
        $body->personalizations[0]->recipient = $to;               #To/Recipient email address

        // Email Header
        $body->from = new PepipostAPILib\Models\From;
        $body->from->fromEmail = Config::PEPIPOST_DOMAIN;   #Sender Domain. Note: The sender domain should be verified and active under your Pepipost account.
        $body->from->fromName = 'Pera Peric';       #Sender/From name

        //Email Body Content
        $body->subject = $subject;               #Subject of email
        //$body->content = '<html><body>Hello, Email testing is successful. <br> Hope you enjoyed this integration. <br></html>'; #HTML content which need to be send in the mail body
        $body->content = $html;

        // Email Settings
        $body->settings = new PepipostAPILib\Models\Settings;
        $body->settings->clicktrack = 1;    #clicktrack for emails enable=1 | disable=0
        $body->settings->opentrack = 1;     #opentrack for emails enable=1 | disable=0
        $body->settings->unsubscribe = 1;   #unsubscribe for emails enable=1 | disable=0

        $response = $emailController->createSendEmail($apiKey, $body);   #function sends email
        // print_r(json_encode($response));

    }
}
