<?php

function verifierUtilisateur($username, $password) {
    return ($username === "admin" && $password === "1234");
}

function genererOTP() {
    return rand(1000, 9999);
}

function lireUtilisateurs() {
    $data = file_get_contents(__DIR__ . '/../data/utilisateurs.json');
    return json_decode($data, true) ?? [];
}

function enregistrerUtilisateurs($users) {
    file_put_contents(__DIR__ . '/../data/utilisateurs.json', json_encode($users, JSON_PRETTY_PRINT));
}
?>