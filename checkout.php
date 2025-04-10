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
    // Gestion de la validation de la commande
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_commande'])) {
        $utilisateur_id = $_SESSION['utilisateur']['id']; // ID de l'utilisateur connecté
        $adresse = trim($_POST['adresse']);
        $mode_paiement = trim($_POST['mode_paiement']);
        $total_commande = 0;

        if (!empty($adresse) && !empty($mode_paiement)) {
            try {
                // Calculer le total de la commande
                foreach ($_SESSION['panier'] as $produit_id => $quantite) {
                    $requete = $connexion->prepare("SELECT prix FROM produits WHERE id = :id");
                    $requete->execute([':id' => $produit_id]);
                    $produit = $requete->fetch(PDO::FETCH_ASSOC);

                    if ($produit) {
                        $total_commande += $produit['prix'] * $quantite;
                    }
                }

                // Enregistrer la commande
                $requete = $connexion->prepare("
                    INSERT INTO commandes (utilisateur_id, adresse, mode_paiement, montant_total, statut, date_commande)
                    VALUES (:utilisateur_id, :adresse, :mode_paiement, :montant_total, 'en attente', NOW())
                ");
                $requete->execute([
                    ':utilisateur_id' => $utilisateur_id,
                    ':adresse' => $adresse,
                    ':mode_paiement' => $mode_paiement,
                    ':montant_total' => $total_commande
                ]);

                // Récupérer l'ID de la commande
                $commande_id = $connexion->lastInsertId();

                // Enregistrer les détails de la commande
                foreach ($_SESSION['panier'] as $produit_id => $quantite) {
                    $requete = $connexion->prepare("
                        INSERT INTO details_commandes (commande_id, produit_id, quantite)
                        VALUES (:commande_id, :produit_id, :quantite)
                    ");
                    $requete->execute([
                        ':commande_id' => $commande_id,
                        ':produit_id' => $produit_id,
                        ':quantite' => $quantite
                    ]);
                }

                // Vider le panier
                unset($_SESSION['panier']);

                // Rediriger vers la page de confirmation
                header('Location: confirmation.php');
                exit;
            } catch (PDOException $e) {
                $message = "Erreur lors de la validation de la commande : " . $e->getMessage();
            }
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
            <button type="submit" name="valider_commande">Valider la commande</button>
        </form>
    </div>
</body>
</html>
