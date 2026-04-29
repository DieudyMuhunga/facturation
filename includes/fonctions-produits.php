<?php
    require_once __DIR__ . '/../config/config.php';

    function lireProduit(){
        $data = file_get_contents(PRODUITS_FILE);
        return json_decode($data, true);
    }

    function enregistreProduits($produits){
        file_get_contents(PRODUITS_FILE, json_encode($produits, JSON_PRETTY_PRINT));
    }
?>