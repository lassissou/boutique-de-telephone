<?php
// filepath: c:\wamp64\www\vente téléphone\compte.php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur']) || empty($_SESSION['utilisateur'])) {
    header('Location: connexion.php'); // Rediriger vers la page de connexion
    exit;
}

// Inclure les fichiers nécessaires
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Récupérer les informations de l'utilisateur connecté
$utilisateur_id = $_SESSION['utilisateur']['id'];
try {
    $requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE id = :id");
    $requete->execute([':id' => $utilisateur_id]);
    $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des informations : " . $e->getMessage();
}

// Gestion de la mise à jour des informations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    if (!empty($nom) && !empty($email) && !empty($adresse)) {
        try {
            $requete = $connexion->prepare("
                UPDATE utilisateurs 
                SET nom = :nom, email = :email, adresse = :adresse 
                WHERE id = :id
            ");
            $requete->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':adresse' => $adresse,
                ':id' => $utilisateur_id
            ]);
            $message = "Informations mises à jour avec succès.";
            $_SESSION['utilisateur']['nom'] = $nom; // Mettre à jour la session
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// Récupérer les commandes de l'utilisateur
try {
    $requete = $connexion->prepare("
        SELECT c.id, c.date_commande, c.montant_total, c.statut 
        FROM commandes c 
        WHERE c.utilisateur_id = :id 
        ORDER BY c.date_commande DESC
    ");
    $requete->execute([':id' => $utilisateur_id]);
    $commandes = $requete->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des commandes : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }

        form {
            margin-top: 20px;
        }

        form div {
            margin-bottom: 15px;
        }

        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        form input, form textarea, form button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        form button {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e9ecef;
        }

        p {
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mon Compte</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <h2>Informations personnelles</h2>
        <form method="POST" action="compte.php">
            <div>
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
            </div>
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required>
            </div>
            <div>
                <label for="adresse">Adresse :</label>
                <textarea id="adresse" name="adresse" required><?php echo htmlspecialchars($utilisateur['adresse']); ?></textarea>
            </div>
            <button type="submit" name="update_account">Mettre à jour</button>
        </form>

        <h2>Mes Commandes</h2>
        <?php if (!empty($commandes)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($commande['id']); ?></td>
                            <td><?php echo htmlspecialchars($commande['date_commande']); ?></td>
                            <td><?php echo number_format($commande['montant_total'], 2); ?> Fcfa</td>
                            <td><?php echo htmlspecialchars($commande['statut']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune commande trouvée.</p>
        <?php endif; ?>
    </div>
</body>
</html>