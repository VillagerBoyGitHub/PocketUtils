<?php
namespace rob\projects\SecureOP;
class GenerateOPPassword {
  function generateOPPassword () {
    $alphabet = ["a","s","d","f","g","h","j","k","l","q","w","e","r","t","y","u","i","o","p","z","x","c","v","b","n","m","1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $password = "";

    for ($x = 0; $x < 24; $x++) {
        $random = rand(0, count($alphabet) - 1);
        $password .= $alphabet[$random];
    }

    return $password;

}

}
