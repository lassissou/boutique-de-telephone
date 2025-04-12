<?php
session_start();

// Inclure le fichier gestionnaire.php pour la connexion à la base de données
require_once 'gestionnaire.php';
$gestionnaire = new Gestionnaire();
$connexion = $gestionnaire->getConnexion();


// Variables pour les messages d'erreur ou de succès
$message = "";

// Gestion de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        try {
            // Vérifier dans la table utilisateurs
            $requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE email = :email");
            $requete->execute([':email' => $email]);
            $utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
                // Connexion réussie
                if (trim($utilisateur['role']) === 'admin') {
                    $_SESSION['admin'] = $utilisateur; // Stocker les informations de l'admin dans la session
                    var_dump($_SESSION['admin']); // Débogage
                    header('Location: administration.php'); // Rediriger vers la page d'administration
                } else {
                    $_SESSION['utilisateur'] = $utilisateur; // Stocker les informations de l'utilisateur dans la session
                    header('Location: index.php'); // Rediriger vers la page d'accueil
                }
                exit;
            } else {
                $message = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $message = "Erreur lors de la connexion : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// Dans la partie gestion de l'inscription, après avoir récupéré $confirmPassword :
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscription'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']); // Nouveau champ adresse
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (!empty($nom) && !empty($email) && !empty($adresse) && !empty($password) && !empty($confirmPassword)) {
        if ($password === $confirmPassword) {
            try {
                // Vérifier si l'email existe déjà
                $requete = $connexion->prepare("SELECT * FROM utilisateurs WHERE email = :email");
                $requete->execute([':email' => $email]);
                if ($requete->rowCount() > 0) {
                    $message = "Cet email est déjà utilisé.";
                } else {
                    // Insérer le nouvel utilisateur avec adresse
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $requete = $connexion->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, adresse, role) VALUES (:nom, :email, :mot_de_passe, :adresse, 'utilisateur')");
                    $requete->execute([
                        ':nom' => $nom,
                        ':email' => $email,
                        ':mot_de_passe' => $hashedPassword,
                        ':adresse' => $adresse
                    ]);
                    $message = "Inscription réussie. Vous pouvez maintenant vous connecter.";
                }
            } catch (PDOException $e) {
                $message = "Erreur lors de l'inscription : " . $e->getMessage();
            }
        } else {
            $message = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Connexion / Inscription</title>
    <link rel="stylesheet" href="connexion.css"> <!-- Ajoutez un fichier CSS pour le style -->
</head>
<body>
    <!-- Insérez ce code dans votre page à l'endroit désiré, par exemple juste avant le footer -->
<a href="javascript:history.back()" class="btn-retour">Retour</a>

<div class="container">
    <h1>Connexion / Inscription</h1>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Formulaire de connexion -->
    <div class="formulaire">
        <h2>Connexion</h2>
        <form method="POST" action="connexion.php">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="connexion">Se connecter</button>
        </form>
    </div>

    <!-- Formulaire d'inscription -->
    <div class="formulaire">
    <h2>Inscription</h2>
    <form method="POST" action="connexion.php">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required>
        
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>

        <label for="adresse">Adresse :</label>
        <input type="text" id="adresse" name="adresse" required>
        
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        
        <label for="confirm_password">Confirmer le mot de passe :</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <button type="submit" name="inscription">S'inscrire</button>
    </form>
</div>
</div>
</body>
</html>