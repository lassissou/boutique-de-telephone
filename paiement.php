<?php
session_start();
require_once 'gestionnaire.php';

$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Vérifier si les informations de commande sont présentes dans la session
if (!isset($_SESSION['commande']) || empty($_SESSION['commande'])) {
    header('Location: checkout.php');
    exit;
}

// Calculer le total de la commande
$total_commande = 0;
foreach ($_SESSION['panier'] as $produit_id => $quantite) {
    $requete = $connexion->prepare("SELECT prix FROM produits WHERE id = :id");
    $requete->execute([':id' => $produit_id]);
    $produit = $requete->fetch(PDO::FETCH_ASSOC);

    if ($produit) {
        $total_commande += $produit['prix'] * $quantite;
    }
}

// Mettre à jour le total dans la session
$_SESSION['commande']['total_commande'] = $total_commande;

// Gestion de la validation du paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_paiement'])) {
    try {
        // Simuler le traitement du paiement (à remplacer par une intégration réelle)
        $mode_paiement = $_POST['mode_paiement'];
        // Ici, vous devriez valider les informations de paiement spécifiques au mode choisi

        // Enregistrer la commande dans la base de données
        $requete = $connexion->prepare("
            INSERT INTO commandes (utilisateur_id, adresse, mode_paiement, montant_total, statut, date_commande)
            VALUES (:utilisateur_id, :adresse, :mode_paiement, :montant_total, 'payée', NOW())
        ");
        $requete->execute([
            ':utilisateur_id' => $_SESSION['commande']['utilisateur_id'],
            ':adresse' => $_SESSION['commande']['adresse'],
            ':mode_paiement' => $mode_paiement,
            ':montant_total' => $_SESSION['commande']['total_commande']
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

        // Vider le panier et les informations de commande
        unset($_SESSION['panier']);
        unset($_SESSION['commande']);

        // Rediriger vers la page de confirmation
        header('Location: confirmation.php');
        exit;
    } catch (PDOException $e) {
        $message = "Erreur lors du traitement du paiement : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement</title>
    <link rel="stylesheet" href="paiement.css">
    <style>
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .error {
            background-color: #fdd;
            border: 1px solid #f44;
            color: #333;
        }
        .payment-method {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .payment-method h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        .form-group button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        #payment-options {
            margin-bottom: 20px;
        }
        #payment-options label {
            margin-right: 15px;
        }
        #payment-options input[type="radio"] {
            margin-right: 5px;
        }
        .hidden {
            display: none;
        }
    </style>
    <script>
        function afficherFormulairePaiement(mode) {
            document.querySelectorAll('.payment-method-form').forEach(form => {
                form.classList.add('hidden');
            });
            document.getElementById(mode + '-form').classList.remove('hidden');
            document.getElementById('selected-payment-mode').value = mode;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Paiement</h1>

        <?php if (!empty($message)): ?>
            <p class="message error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <p>Montant total à payer : <strong><?php echo number_format($_SESSION['commande']['total_commande'], 2); ?> Fcfa</strong></p>

        <div id="payment-options">
            <label>
                <input type="radio" name="mode_paiement_choix" value="carte_bancaire" onclick="afficherFormulairePaiement('carte_bancaire')" required> Carte Bancaire
            </label>
            <label>
                <input type="radio" name="mode_paiement_choix" value="mobile_money" onclick="afficherFormulairePaiement('mobile_money')" required> Mobile Money
            </label>
            <label>
                <input type="radio" name="mode_paiement_choix" value="paypal" onclick="afficherFormulairePaiement('paypal')" required> PayPal
            </label>
            <label>
                <input type="radio" name="mode_paiement_choix" value="carte_credit" onclick="afficherFormulairePaiement('carte_credit')" required> Carte de Crédit
            </label>
        </div>

        <form method="POST" action="paiement.php">
            <input type="hidden" name="mode_paiement" id="selected-payment-mode" value="">

            <div id="carte_bancaire-form" class="payment-method-form hidden payment-method">
                <h2>Paiement par Carte Bancaire</h2>
                <div class="form-group">
                    <label for="carte_numero">Numéro de carte</label>
                    <input type="text" id="carte_numero" name="carte_numero" placeholder="XXXX-XXXX-XXXX-XXXX">
                </div>
                <div class="form-group">
                    <label for="carte_expiration">Date d'expiration</label>
                    <input type="text" id="carte_expiration" name="carte_expiration" placeholder="MM/AA">
                </div>
                <div class="form-group">
                    <label for="carte_cvv">CVV</label>
                    <input type="number" id="carte_cvv" name="carte_cvv" placeholder="XXX">
                </div>
                <div class="form-group">
                    <button type="submit" name="confirmer_paiement">Confirmer le paiement par Carte Bancaire</button>
                </div>
            </div>

            <div id="mobile_money-form" class="payment-method-form hidden payment-method">
                <h2>Paiement par Mobile Money</h2>
                <div class="form-group">
                    <label for="mobile_numero">Numéro de téléphone</label>
                    <input type="text" id="mobile_numero" name="mobile_numero" placeholder="Votre numéro de téléphone">
                </div>
                <div class="form-group">
                    <label for="mobile_operateur">Opérateur</label>
                    <select id="mobile_operateur" name="mobile_operateur">
                        <option value="mtn">MTN</option>
                        <option value="orange">Orange</option>
                        <option value="moov">Moov</option>
                        </select>
                </div>
                <div class="form-group">
                    <button type="submit" name="confirmer_paiement">Confirmer le paiement par Mobile Money</button>
                </div>
            </div>

            <div id="paypal-form" class="payment-method-form hidden payment-method">
                <h2>Paiement par PayPal</h2>
                <p>Vous allez être redirigé vers PayPal pour finaliser votre paiement.</p>
                <div class="form-group">
                    <button type="submit" name="confirmer_paiement">Payer avec PayPal</button>
                </div>
                </div>

            <div id="carte_credit-form" class="payment-method-form hidden payment-method">
                <h2>Paiement par Carte de Crédit</h2>
                <div class="form-group">
                    <label for="credit_numero">Numéro de carte de crédit</label>
                    <input type="text" id="credit_numero" name="credit_numero" placeholder="XXXX-XXXX-XXXX-XXXX">
                </div>
                <div class="form-group">
                    <label for="credit_expiration">Date d'expiration</label>
                    <input type="text" id="credit_expiration" name="credit_expiration" placeholder="MM/AA">
                </div>
                <div class="form-group">
                    <label for="credit_cvv">CVV</label>
                    <input type="number" id="credit_cvv" name="credit_cvv" placeholder="XXX">
                </div>
                <div class="form-group">
                    <button type="submit" name="confirmer_paiement">Confirmer le paiement par Carte de Crédit</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>