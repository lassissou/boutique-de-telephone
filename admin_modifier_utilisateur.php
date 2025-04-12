<?php
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();

$message = "";
 
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_utilisateurs.php");
    exit;
}
$utilisateur_id = intval($_GET['id']);
 
// Récupération des données de l'utilisateur
try {
    $stmt = $connexion->prepare("SELECT id, nom, email, role, adresse FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $utilisateur_id]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$utilisateur) {
        header("Location: admin_utilisateurs.php");
        exit;
    }
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération de l'utilisateur : " . $e->getMessage();
}
 
// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_utilisateur'])) {
    $nouveau_role = trim($_POST['role']);
    $nouvelle_adresse = trim($_POST['adresse']);
 
    if (!empty($nouveau_role) && !empty($nouvelle_adresse)) {
        try {
            $stmtUpdate = $connexion->prepare("
                UPDATE utilisateurs 
                SET role = :role, adresse = :adresse
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':role' => $nouveau_role,
                ':adresse' => $nouvelle_adresse,
                ':id' => $utilisateur_id
            ]);
            $message = "Utilisateur mis à jour avec succès.";
            // Récupérer les données mises à jour
            $stmt = $connexion->prepare("SELECT id, nom, email, role, adresse FROM utilisateurs WHERE id = :id");
            $stmt->execute([':id' => $utilisateur_id]);
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir les champs du rôle et de l'adresse.";
    }
}
?>

<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-retour {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-retour:hover {
            background-color: #0056b3;
        }

        h2 {
            color: #37474f;
            margin-bottom: 25px;
            border-bottom: 2px solid #eceff1;
            padding-bottom: 10px;
            text-align: center;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
        }

        .message.success {
            background-color: #e6ffe6;
            color: #38761d;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #ffe6e6;
            color: #cc0000;
            border: 1px solid #f5c6cb;
        }

        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #546e7a;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #dce7ec;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:disabled {
            background-color: #f9f9f9;
            color: #aaa;
            cursor: not-allowed;
        }

        .form-group input[type="text"]:focus,
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-group select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg fill="%2364b5f6" viewBox="0 0 4 5"><path d="M2 0L0 2h4L2 0z"/></svg>');
            background-repeat: no-repeat;
            background-position-x: 98%;
            background-position-y: 50%;
            background-size: 6px auto;
            padding-right: 25px;
        }

        button[type="submit"] {
            background-color: #2e7d32;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #1b5e20;
        }
    </style>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier l'utilisateur</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f8f9fa; }
        h2 { text-align: center; color: #333; }
        .message { margin: 15px auto; max-width: 800px; padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; text-align: center; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px; }
        .btn-retour { display: inline-block; margin: 20px; padding: 10px 20px; background-color: #007BFF; color: #fff; text-decoration: none; border-radius: 4px; transition: background-color 0.3s ease; }
        .btn-retour:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <a href="admin_utilisateurs.php" class="btn-retour">Retour</a>
    <h2>Modifier l'utilisateur</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST" action="admin_modifier_utilisateur.php?id=<?php echo $utilisateur['id']; ?>">
        <div class="form-group">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="role">Rôle :</label>
            <select id="role" name="role" required>
                <option value="utilisateur" <?php echo ($utilisateur['role'] === 'utilisateur') ? 'selected' : ''; ?>>Utilisateur</option>
                <option value="admin" <?php echo ($utilisateur['role'] === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
            </select>
        </div>
        <div class="form-group">
            <label for="adresse">Adresse :</label>
            <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($utilisateur['adresse']); ?>" required>
        </div>
        <button type="submit" name="update_utilisateur">Mettre à jour</button>
    </form>
</body>
</html>