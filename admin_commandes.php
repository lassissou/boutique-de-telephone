<?php
// filepath: c:\wamp64\www\vente téléphone\admin_commandes.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de la mise à jour du statut d'une commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $commande_id = intval($_POST['commande_id']);
    $nouveau_statut = trim($_POST['statut']);

    if (!empty($commande_id) && !empty($nouveau_statut)) {
        try {
            $requete = $connexion->prepare("
                UPDATE commandes 
                SET statut = :statut 
                WHERE id = :id
            ");
            $requete->execute([
                ':statut' => $nouveau_statut,
                ':id' => $commande_id
            ]);
            $message = "Statut de la commande mis à jour avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour du statut : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez sélectionner un statut valide.";
    }
}

// Récupération de la liste des commandes
try {
    $req = $connexion->query("
        SELECT c.id, c.date_commande, c.montant_total, c.statut, u.nom AS client_nom, u.email AS client_email
        FROM commandes c
        JOIN utilisateurs u ON c.utilisateur_id = u.id
        ORDER BY c.date_commande DESC
    ");
    $commandes = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des commandes : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des commandes - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        h2, h3 {
            text-align: center;
            color: #333;
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: 5px;
        }
        .form-group select, 
        .form-group input { 
            width: 100%; 
            padding: 5px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {  
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
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
        form.inline {
            display: inline;
        }
        form.inline select {
            margin-right: 5px;
        }
        form.inline button {
            padding: 5px 10px;
            border: none;
            background-color: #007BFF;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        form.inline button:hover {
            background-color: #0056b3;
        }
        a.action-link {
            margin-left: 8px;
            text-decoration: none;
            color: #007BFF;
        }
        a.action-link:hover {
            text-decoration: underline;
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

    <h2>Gestion des commandes</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Liste des commandes</h3>
    <?php if (!empty($commandes)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Montant (€)</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $commande): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($commande['id']); ?></td>
                        <td><?php echo htmlspecialchars($commande['date_commande']); ?></td>
                        <td><?php echo htmlspecialchars($commande['client_nom']); ?></td>
                        <td><?php echo htmlspecialchars($commande['client_email']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($commande['montant_total'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($commande['statut']); ?></td>
                        <td>
                            <form method="POST" action="admin_commandes.php" class="inline">
                                <input type="hidden" name="commande_id" value="<?php echo $commande['id']; ?>">
                                <select name="statut" required>
                                    <option value="en attente" <?php echo $commande['statut'] === 'en attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="en cours" <?php echo $commande['statut'] === 'en cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="expédiée" <?php echo $commande['statut'] === 'expédiée' ? 'selected' : ''; ?>>Expédiée</option>
                                    <option value="livrée" <?php echo $commande['statut'] === 'livrée' ? 'selected' : ''; ?>>Livrée</option>
                                    <option value="annulée" <?php echo $commande['statut'] === 'annulée' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                                <button type="submit" name="update_status">Mettre à jour</button>
                            </form>
                            <a class="action-link" href="admin_details_commande.php?id=<?php echo $commande['id']; ?>">Détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune commande trouvée.</p>
    <?php endif; ?>
</body>
</html>