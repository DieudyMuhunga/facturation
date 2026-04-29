<?php
    include("../../includes/header.php");
    $fichier = __DIR__ . '/../../data/produits.json';

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        // validation champs

        if(empty($_POST['code_barre']) || empty($_POST['nom']) || empty($_POST['prix']) || empty($_POST['quantite']) || empty($_POST['date'])){
            echo "Tous les champs sont obligatoire ! <br><br>";
            return; 
        }

        if($_POST['prix'] <= 0 || $_POST['quantite'] <= 0){
            echo "Le prix et la quantité doivent etre positifs ! <br><br>";
            return;
        }

        // lecture fichiers JSON

        $data = file_get_contents($fichier);
        $produits = json_decode($data, true);

        if($produits === null){
            $produits = [];
        }

        // verification code barre 

        $produitExiste = false;
        foreach($produits as $produit){
            if($produit['code_barre'] === $_POST['code_barre']){
                $produitExiste = true;
                break;
            }
        }

        if($produitExiste){
            echo "Le produit avec le code barre " . $_POST['code_barre'] . " existe déjà. <br><br>";
        } else { // ajout produit
            $produits[] = [
                'code_barre' => $_POST['code_barre'],
                'nom' => $_POST['nom'],
                'prix_unitaire_ht' => (float)$_POST['prix'],
                'date_expiration' => $_POST['date'],
                'quantite_stock' => (int)$_POST['quantite']
            ];

            // sauvegarde produit
            
            file_put_contents($fichier, json_encode($produits, JSON_PRETTY_PRINT));

            echo "Produit ajouté avec succès ! <br><br>";
        }

    }

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enregistrement produits</title>
    </head>
    

    <body>
        <form method="POST" action="">
            Code barre : <input type="text"  name="code_barre"><br><br>
            Nom : <input type=" text" name="nom"><br><br>
            Prix : <input type="text" name="prix"><br><br>
            date expiration : <input type="date" name="date"><br><br>
            Quantite : <input type="text" name="quantite"><br><br>
            <button type="submit">Ajouter</button>
        </form>
    </body>
</html>