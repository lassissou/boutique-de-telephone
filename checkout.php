<?php
session_start();
require_once 'gestionnaire.php';

$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Vérifier si le panier est vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur']) || !isset($_SESSION['utilisateur']['id'])) {
    $message = "Veuillez vous connecter pour valider votre commande.";
} else {
    // Gestion de la validation des informations de livraison
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_commande'])) {
        $utilisateur_id = $_SESSION['utilisateur']['id']; // ID de l'utilisateur connecté
        $adresse = trim($_POST['adresse']);
        $mode_paiement = trim($_POST['mode_paiement']);

        if (!empty($adresse) && !empty($mode_paiement)) {
            // Stocker les informations de livraison et de paiement dans la session
            $_SESSION['commande'] = [
                'utilisateur_id' => $utilisateur_id,
                'adresse' => $adresse,
                'mode_paiement' => $mode_paiement,
                'total_commande' => 0 // Le total sera calculé dans la page de paiement
            ];

            // Rediriger vers la page de paiement
            header('Location: paiement.php');
            exit;
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation de la commande</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <a href="javascript:history.back()" class="btn-retour">Retour</a>

    <div class="container">
        <h1>Validation de la commande</h1>

        <?php if (!empty($message)): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="checkout.php">
            <div class="form-group">
                <label for="adresse">Adresse de livraison :</label>
                <textarea id="adresse" name="adresse" required></textarea>
            </div>
            <div class="form-group">
                <label for="mode_paiement">Mode de paiement :</label>
                <select id="mode_paiement" name="mode_paiement" required>
                    <option value="carte">Carte bancaire</option>
                    <option value="paypal">PayPal</option>
                    <option value="virement">Virement bancaire</option>
                </select>
            </div>
            <button type="submit" name="valider_commande">Continuer vers le paiement</button>
        </form>
    </div>
</body>
</html>