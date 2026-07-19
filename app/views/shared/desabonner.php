<?php
require_once 'config/database.php';
$db    = getDB();
$email = $_GET['email'] ?? '';

if (!empty($email)) {
    $db->prepare("UPDATE utilisateurs SET abonne_newsletter = 0 WHERE email = ?")
       ->execute([$email]);
    $message = " Vous avez été désabonné avec succès.";
} else {
    $message = "❌ Email invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Désabonnement — UFR-MI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 420px;
        }
        .mi { font-size: 56px; font-weight: 900; color: #4a90d9; margin-bottom: 8px; }
        .subtitle { font-size: 12px; color: #7ab3e0; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 32px; }
        .message { font-size: 18px; font-weight: 600; color: #0d1b6e; margin-bottom: 16px; }
        .info { font-size: 14px; color: #6c757d; margin-bottom: 32px; }
        .btn { background: linear-gradient(135deg, #0d1b6e, #1a2fa0); color: white; border: none; border-radius: 8px; padding: 12px 28px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn:hover { opacity: 0.9; color: white; }
    </style>
</head>
<body>
    <div class="card">
        <div class="mi">MI</div>
        <div class="subtitle">UFR Mathématique-Informatique</div>
        <div class="message"><?= $message ?></div>
        <div class="info">
            Vous ne recevrez plus les newsletters de l'UFR-MI.<br>
            Pour vous réabonner, contactez l'administration.
        </div>
        <a href="index.php?page=login" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>