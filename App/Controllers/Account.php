<?php

namespace App\Controllers;

use App\Models\User;
// action that validates email sent by javascript from the frontend 
class Account extends \Core\Controller
{
    public function validateEmailAction()
    {
        $is_valid = !User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null); // checks id email exists in db but not when we click on "sign up" but the one that is sent by jquery validation function as we type
                                                                                    // 2nd arg is determined if the case is about only updating existing profile (it's null if jquery doesn't send user id which is the case when creating new profile)
        header('Content-Type: application/json'); // this is response header
        echo json_encode($is_valid); // and this is response body, (congrats how this looks like and how it works!), is_valid can only be either true or false
    }
}
