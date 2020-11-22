<?php

namespace App\Controllers;

use Core\Controller;
use \Core\View;
use \App\Models\User; // we use "use" so that we wouldn't have to name the whole namespace each time
use App\Auth;
use App\Flash;
// use App\Mail; // just for testing email sending service Mail::send();



class Login extends Controller
{
    public function newAction()
    {
        View::renderTemplate('Login/new.html');
    }

    public function createAction()
    {
        $user = User::authenticate($_POST['email'], $_POST['password']); // first looks in the db based on email and then checks if the passwords (hashes) match, if not false is returned

        $ip = $_POST['ip'];

        $remember_me = isset($_POST['remember_me']);

        if ($user) {
            Auth::login($user, $remember_me);

            // Mail::send(); // just for testing email sending service

            Flash::addMessage("Login successful.\n Logged from: $ip "); // no 2nd arg (type of message) because in Flash.php type is set to success by default
            
            $this->redirect(Auth::getReturnToPage()); // if the user has been found in db namely if logging-in was performed, we're redirecting to requested page which has been 
                                                      // previously stored in a session file or if it wasn't we're redirecting to the homepage. same as Controller::redirect('/');
            
        } else { // if ther's no user in db or password does not match one stored in db false is returned and we're loading login page again 
            Flash::addMessage('Login unsuccessful, please try again', Flash::WARNING); // 2nd arg passes the type of flash message

            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email'], // we're passing the email and whether the user opted to be remembered so the same date doesn't have to be reentered
                'remember_me' => $remember_me
            ]); 
        }
    }

    public function destroyAction() 
    {                               
        Auth::logout(); // method to log out user

        $this->redirect('/login/show-logout-message'); // we had to make new url request so that new session would be generated which enables flash message to show 
                                                       // that we've been logged-out successfully, and that we could not do without redirecting first because during Auth::logout() 
                                                       // session destroy has been performed and there's no more session file 
    }

    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout successful');

        $this->redirect('/'); // after the user has logged out we're redirecting to the homepage
    }
}
