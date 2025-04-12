<?php
// filepath: c:\wamp64\www\vente téléphone\admin_parametres.php

// Inclure le fichier gestionnaire.php pour la connexion à la base de données
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de la mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $nomSite = trim($_POST['nom_site']);
    $emailContact = trim($_POST['email_contact']);
    $telephoneContact = trim($_POST['telephone_contact']);
    $adresseContact = trim($_POST['adresse_contact']);
    $paiementActif = isset($_POST['paiement_actif']) ? 1 : 0;
    $livraisonActif = isset($_POST['livraison_actif']) ? 1 : 0;

    try {
        // Mettre à jour les paramètres dans la base de données
        $requete = $connexion->prepare("
            UPDATE parametres 
            SET nom_site = :nom_site, 
                email_contact = :email_contact, 
                telephone_contact = :telephone_contact, 
                adresse_contact = :adresse_contact, 
                paiement_actif = :paiement_actif, 
                livraison_actif = :livraison_actif
            WHERE id = 1
        ");
        $requete->execute([
            ':nom_site' => $nomSite,
            ':email_contact' => $emailContact,
            ':telephone_contact' => $telephoneContact,
            ':adresse_contact' => $adresseContact,
            ':paiement_actif' => $paiementActif,
            ':livraison_actif' => $livraisonActif
        ]);
        $message = "Les paramètres ont été mis à jour avec succès.";
    } catch (PDOException $e) {
        $message = "Erreur lors de la mise à jour des paramètres : " . $e->getMessage();
    }
}

// Récupérer les paramètres actuels
try {
    $requete = $connexion->query("SELECT * FROM parametres WHERE id = 1");
    $parametres = $requete->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des paramètres : " . $e->getMessage();
    $parametres = [
        'nom_site' => '',
        'email_contact' => '',
        'telephone_contact' => '',
        'adresse_contact' => '',
        'paiement_actif' => 0,
        'livraison_actif' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des paramètres - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-top: 30px;
        }
        .message {
            max-width: 800px;
            margin: 20px auto;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
        }
        .form-group textarea {
            height: 80px;
        }
        button {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        /* Ajoutez ce style dans votre fichier CSS ou dans un bloc <style> de la page */
.btn-retour {
    display: inline-block;
    margin: 20px;
    padding: 10px 20px;
    background-color: #007BFF;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.btn-retour:hover {
    background-color: #0056b3;
}
    </style>
</head>
<body>
    <!-- Insérez ce code dans votre page à l'endroit désiré, par exemple juste avant le footer -->
<a href="javascript:history.back()" class="btn-retour">Retour</a>

    <h2>Gestion des paramètres</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="admin_parametres.php">
        <div class="form-group">
            <label for="nom_site">Nom du site :</label>
            <input type="text" id="nom_site" name="nom_site" 
                   value="<?php echo htmlspecialchars($parametres['nom_site']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email_contact">Email de contact :</label>
            <input type="email" id="email_contact" name="email_contact" 
                   value="<?php echo htmlspecialchars($parametres['email_contact']); ?>" required>
        </div>
        <div class="form-group">
            <label for="telephone_contact">Téléphone de contact :</label>
            <input type="text" id="telephone_contact" name="telephone_contact" 
                   value="<?php echo htmlspecialchars($parametres['telephone_contact']); ?>" required>
        </div>
        <div class="form-group">
            <label for="adresse_contact">Adresse de contact :</label>
            <textarea id="adresse_contact" name="adresse_contact" required><?php echo htmlspecialchars($parametres['adresse_contact']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="paiement_actif">Activer les paiements :</label>
            <input type="checkbox" id="paiement_actif" name="paiement_actif" <?php echo $parametres['paiement_actif'] ? 'checked' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="livraison_actif">Activer les livraisons :</label>
            <input type="checkbox" id="livraison_actif" name="livraison_actif" <?php echo $parametres['livraison_actif'] ? 'checked' : ''; ?>>
        </div>
        <button type="submit" name="update_settings">Mettre à jour</button>
    </form>
</body>
</html>