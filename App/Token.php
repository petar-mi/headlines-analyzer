<?php

namespace App;

class Token 
{
    protected $token;

    public function __construct($token_value = null) // value is provided to the param in case a token has not been passed to the constructor function (when we're creating a new one)
    {                                                
        if ($token_value) { // in case an existing token was passed we store it
            $this->token = $token_value;
        } else { // otherwise we create a new one
        $this->token = bin2hex(random_bytes(16)); // we create a token and convert it to hexadecimal (from binary)
        }                                         // 16 bytes = 128bits = 32 hex characters
    }

    public function getValue()
    {
        return $this->token;
    }

    public function getHash()
    {
        return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY); // we hash a tokeb using separate method, 3rd arg is a key 
                                                                           // recomended to be 32 characters long and which mixes with a token on a deeper level than using salt
    }
}