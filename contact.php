<?php
// filepath: c:\wamp64\www\vente téléphone\contact.php

$message = "";
$error = "";

// Gestion de l'envoi du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $sujet = trim($_POST['sujet']);
    $message_contact = trim($_POST['message']);

    // Validation des champs
    if (!empty($nom) && !empty($email) && !empty($sujet) && !empty($message_contact)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Envoi de l'email
            $to = "admin@ventetelephone.com"; // Remplacez par l'adresse email de l'administrateur
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            $body = "Nom : $nom\n";
            $body .= "Email : $email\n";
            $body .= "Sujet : $sujet\n\n";
            $body .= "Message :\n$message_contact\n";

            if (mail($to, $sujet, $body, $headers)) {
                $message = "Votre message a été envoyé avec succès.";
            } else {
                $error = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer.";
            }
        } else {
            $error = "Veuillez entrer une adresse email valide.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 150px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .contact-info {
            margin-top: 30px;
        }
        .contact-info h3 {
            margin-bottom: 10px;
        }
        .contact-info p {
            margin: 5px 0;
        }
        .map {
            margin-top: 30px;
            text-align: center;
        }
        .map iframe {
            width: 100%;
            height: 300px;
            border: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contactez-nous</h1>

        <?php if (!empty($message)): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="sujet">Sujet :</label>
                <input type="text" id="sujet" name="sujet" required>
            </div>
            <div class="form-group">
                <label for="message">Message :</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" name="submit_contact">Envoyer</button>
        </form>

        <div class="contact-info">
            <h3>Nos coordonnées</h3>
            <p><strong>Adresse :</strong> 123 Rue des Téléphones, Paris, France</p>
            <p><strong>Téléphone :</strong> +33 1 23 45 67 89</p>
            <p><strong>Email :</strong> contact@ventetelephone.com</p>
            <p><strong>Horaires :</strong> Lundi - Vendredi : 9h - 18h</p>
        </div>

        <div class="map">
            <h3>Notre emplacement</h3>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9999999999995!2d2.2944813156746826!3d48.85884497928744!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66f0000000000%3A0x0000000000000000!2sTour%20Eiffel!5e0!3m2!1sfr!2sfr!4v1610000000000!5m2!1sfr!2sfr" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</body>
</html>