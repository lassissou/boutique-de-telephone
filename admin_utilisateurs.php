<?php
// filepath: c:\wamp64\www\vente téléphone\admin_utilisateurs.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de la modification du rôle d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $utilisateur_id = intval($_POST['utilisateur_id']);
    $nouveau_role = trim($_POST['role']);

    if (!empty($utilisateur_id) && !empty($nouveau_role)) {
        try {
            $requete = $connexion->prepare("
                UPDATE utilisateurs 
                SET role = :role 
                WHERE id = :id
            ");
            $requete->execute([
                ':role' => $nouveau_role,
                ':id' => $utilisateur_id
            ]);
            $message = "Rôle de l'utilisateur mis à jour avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour du rôle : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez sélectionner un rôle valide.";
    }
}

// Gestion de la suppression d'un utilisateur
if (isset($_GET['delete_id'])) {
    $utilisateur_id = intval($_GET['delete_id']);

    try {
        $requete = $connexion->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $requete->execute([':id' => $utilisateur_id]);
        $message = "Utilisateur supprimé avec succès.";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
    }
}

// Récupération de la liste des utilisateurs
try {
    $req = $connexion->query("SELECT id, nom, email, role, date_inscription FROM utilisateurs ORDER BY date_inscription DESC");
    $utilisateurs = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des utilisateurs - Administration</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label { 
            display: block; 
            font-weight: bold; 
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
            margin-bottom: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h2>Gestion des utilisateurs</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3>Liste des utilisateurs</h3>
    <?php if (!empty($utilisateurs)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($utilisateur['id']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['nom']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['role']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['date_inscription']); ?></td>
                        <td>
                            <form method="POST" action="admin_utilisateurs.php" style="display:inline;">
                                <input type="hidden" name="utilisateur_id" value="<?php echo $utilisateur['id']; ?>">
                                <select name="role" required>
                                    <option value="utilisateur" <?php echo $utilisateur['role'] === 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                                    <option value="admin" <?php echo $utilisateur['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                </select>
                                <button type="submit" name="update_role">Mettre à jour</button>
                            </form>
                            <a href="admin_utilisateurs.php?delete_id=<?php echo $utilisateur['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php endif; ?>
</body>
</html>