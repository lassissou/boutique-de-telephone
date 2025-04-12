<?php
// filepath: c:\wamp64\www\vente téléphone\faq.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Questions Fréquentes</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }
        .faq-item {
            margin-bottom: 20px;
        }
        .faq-item h2 {
            font-size: 18px;
            color: #007BFF;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .faq-item p {
            display: none;
            line-height: 1.6;
            margin: 0;
        }
        .faq-item.active p {
            display: block;
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
    <div class="container">
        <h1>Foire Aux Questions (FAQ)</h1>
        <div class="faq-item">
            <h2>1. Comment passer une commande ?</h2>
            <p>Pour passer une commande, ajoutez les produits souhaités à votre panier, puis cliquez sur "Passer à la caisse". Suivez les instructions pour finaliser votre commande.</p>
        </div>
        <div class="faq-item">
            <h2>2. Quels sont les modes de paiement acceptés ?</h2>
            <p>Nous acceptons les paiements par carte bancaire, PayPal et virement bancaire.</p>
        </div>
        <div class="faq-item">
            <h2>3. Quels sont les délais de livraison ?</h2>
            <p>Les délais de livraison varient en fonction de votre emplacement. En général, les commandes sont livrées sous 3 à 7 jours ouvrables.</p>
        </div>
        <div class="faq-item">
            <h2>4. Puis-je retourner un produit ?</h2>
            <p>Oui, vous disposez d'un délai de 14 jours pour retourner un produit, à condition qu'il soit dans son état d'origine.</p>
        </div>
        <div class="faq-item">
            <h2>5. Comment contacter le service client ?</h2>
            <p>Vous pouvez nous contacter par email à contact@votresite.com ou par téléphone au 01 23 45 67 89.</p>
        </div>
    </div>
    <script>
        // Script pour afficher/masquer les réponses des FAQ
        document.querySelectorAll('.faq-item h2').forEach(item => {
            item.addEventListener('click', () => {
                const parent = item.parentElement;
                parent.classList.toggle('active');
            });
        });
    </script>
</body>
</html>