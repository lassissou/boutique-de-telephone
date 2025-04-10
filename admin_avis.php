<?php
// filepath: c:\wamp64\www\vente téléphone\admin_avis.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de la suppression d'un avis
if (isset($_GET['delete_id'])) {
    $avis_id = intval($_GET['delete_id']);

    try {
        $requete = $connexion->prepare("DELETE FROM avis WHERE id = :id");
        $requete->execute([':id' => $avis_id]);
        $message = "Avis supprimé avec succès.";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression de l'avis : " . $e->getMessage();
    }
}

// Gestion de l'approbation d'un avis
if (isset($_GET['approve_id'])) {
    $avis_id = intval($_GET['approve_id']);

    try {
        $requete = $connexion->prepare("UPDATE avis SET approuve = 1 WHERE id = :id");
        $requete->execute([':id' => $avis_id]);
        $message = "Avis approuvé avec succès.";
    } catch (PDOException $e) {
        $message = "Erreur lors de l'approbation de l'avis : " . $e->getMessage();
    }
}

// Récupération de la liste des avis
try {
    $req = $connexion->query("
        SELECT a.id, a.utilisateur_id, a.produit_id, a.commentaire, a.note, a.approuve, u.nom AS utilisateur_nom, p.nom AS produit_nom
        FROM avis a
        JOIN utilisateurs u ON a.utilisateur_id = u.id
        JOIN produits p ON a.produit_id = p.id
        ORDER BY a.id DESC
    ");
    $avis = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des avis : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des avis - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }

        h2, h3 {
            text-align: center;
            color: #007bff;
            margin-top: 20px;
        }

        .message {
            margin: 20px auto;
            padding: 10px;
            max-width: 800px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .actions a {
            text-decoration: none;
            color: #007bff;
            margin-right: 10px;
            transition: color 0.3s ease;
        }

        .actions a:hover {
            color: #0056b3;
        }

        .actions a.danger {
            color: #dc3545;
        }

        .actions a.danger:hover {
            color: #c82333;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #343a40;
            color: #fff;
        }
    </style>
</head>
<body>
    <h2>Gestion des avis</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Liste des avis</h3>
    <?php if (!empty($avis)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilisateur</th>
                    <th>Produit</th>
                    <th>Commentaire</th>
                    <th>Note</th>
                    <th>Approuvé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avis as $avis_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($avis_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($avis_item['utilisateur_nom']); ?></td>
                        <td><?php echo htmlspecialchars($avis_item['produit_nom']); ?></td>
                        <td><?php echo htmlspecialchars($avis_item['commentaire']); ?></td>
                        <td><?php echo htmlspecialchars($avis_item['note']); ?>/5</td>
                        <td><?php echo $avis_item['approuve'] ? 'Oui' : 'Non'; ?></td>
                        <td class="actions">
                            <?php if (!$avis_item['approuve']): ?>
                                <a href="admin_avis.php?approve_id=<?php echo $avis_item['id']; ?>">Approuver</a>
                            <?php endif; ?>
                            <a href="admin_avis.php?delete_id=<?php echo $avis_item['id']; ?>" class="danger" onclick="return confirm('Voulez-vous vraiment supprimer cet avis ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">Aucun avis trouvé.</p>
    <?php endif; ?>

    <footer>
        <p>&copy; 2025 Vente de téléphones - Administration</p>
    </footer>
</body>
</html>