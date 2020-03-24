<?php

namespace App;

class Token 
{
    protected $token;

    public function __construct($token_value = null) // argumentu je data vrednost u slucaju da se ne prosledjuje token (to je kada zelimo da kreiramo novi)
    {
        if ($token_value) { // u slucaju da je prosledjen postojeci token njega memorisemo
            $this->token = $token_value;
        } else { // ili stvaramo novi
        $this->token = bin2hex(random_bytes(16)); // kreiramo token i odmah ga iz binarnih pretvaramo u hexadecimalne
        }                                         // 16 bytes = 128bits = 32 hex characters
    }

    public function getValue()
    {
        return $this->token;
    }

    public function getHash()
    {
        return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY); // posebnom metodom, razlicitom od password_hash hashujemo token, treci arg. je key koji je preporuceno da bude 32 karaktera i koji se na dublji nacin od salt-ovanja mesa sa tokenom
    }
}