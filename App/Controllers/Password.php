<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

class Password extends \Core\Controller
{
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.html'); 
    }

    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']); // no need to do neither frontend nor backend validation because there is no need for that. Only after user enters a new password we will validate.
        View::renderTemplate('Password/reset_requested.html'); // just shows that the request was accepted
    }

    public function resetAction()
    {
        $token = $this->route_params['token']; // availability of hexadecimal token from the end part of url was enabed by adding special route in index.php
        
        $user = $this->getUserOrExit($token);
        
        View::renderTemplate('Password/reset.html', [ // executes only if the user was found (otherwise we exit in getUserOrExit method)
                'token' => $token // passing a token to the frontendusing twig which will then be sent to resetPasswordAction() method in this class after submitting the form 
        ]);
        
    }

    public function resetPasswordAction() 
    {
        $token = $_POST['token']; // received from a hidden input field in frontend reset form 

        $user = $this->getUserOrExit($token); // looking for user in db passing a token
        
        if ($user->resetPassword($_POST['password'])) { // true if the method results in an empty errors array
            View::renderTemplate('Password/reset_success.html');
        } else {
            View::renderTemplate('Password/reset.html', [ 
                'token' => $token,
                'user'  => $user // this time we pass user obj as well for another reset attempt
        ]);
        }

    }

    protected function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);
        if($user){
            return $user;
        } else { // in case the token has expired or was not found in db at all
            View::renderTemplate('Password/token_expired.html');
            exit; // exit program normally terminating the execution of the script
            // exit(); // same as just exit;
            // exit(0); // same as just exit; if there was an error we pass an integer number 0-254 as an error code
        };
    }
}