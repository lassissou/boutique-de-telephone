<?php
// filepath: c:\wamp64\www\vente téléphone\admin_utilisateurs.php

require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";

// Gestion de la modification du rôle (et de l'adresse) d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_utilisateur'])) {
    $utilisateur_id = intval($_POST['utilisateur_id']);
    $nouveau_role = trim($_POST['role']);
    $nouvelle_adresse = trim($_POST['adresse']);

    if (!empty($utilisateur_id) && !empty($nouveau_role) && !empty($nouvelle_adresse)) {
        try {
            $requete = $connexion->prepare("
                UPDATE utilisateurs 
                SET role = :role, adresse = :adresse
                WHERE id = :id
            ");
            $requete->execute([
                ':role' => $nouveau_role,
                ':adresse' => $nouvelle_adresse,
                ':id' => $utilisateur_id
            ]);
            $message = "Utilisateur mis à jour avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs (rôle et adresse).";
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

// Récupération de la liste des utilisateurs (avec adresse)
try {
    $req = $connexion->query("SELECT id, nom, email, role, adresse, date_inscription FROM utilisateurs ORDER BY date_inscription DESC");
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
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f8f9fa;
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        .message {
            margin: 15px auto;
            max-width: 800px;
            padding: 12px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            text-align: center;
        }
        table {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {  
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
        }
        form.inline {
            display: inline;
        }
        form.inline select, form.inline input {
            margin-right: 5px;
            padding: 5px;
        }
        form.inline button {
            padding: 5px 10px;
            border: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        form.inline button:hover {
            background-color: #0056b3;
        }
        a.action-link {
            color: #dc3545;
            text-decoration: none;
            margin-left: 10px;
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
                    <th>Adresse</th>
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
                        <td><?php echo htmlspecialchars($utilisateur['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['date_inscription']); ?></td>
                        <td>
                            <form method="POST" action="admin_utilisateurs.php" class="inline">
                                <input type="hidden" name="utilisateur_id" value="<?php echo $utilisateur['id']; ?>">
                                <select name="role" required>
                                    <option value="utilisateur" <?php echo $utilisateur['role'] === 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                                    <option value="admin" <?php echo $utilisateur['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                </select>
                                <input type="text" name="adresse" value="<?php echo htmlspecialchars($utilisateur['adresse']); ?>" placeholder="Adresse" required>
                                <button type="submit" name="update_utilisateur">Mettre à jour</button>
                            </form>
                            <a class="action-link" href="admin_utilisateurs.php?delete_id=<?php echo $utilisateur['id']; ?>" onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">Supprimer</a>
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