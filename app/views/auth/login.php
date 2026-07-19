<?php
if (isset($_SESSION['user'])) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Newsletter UFR-MI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background-color: #f0f2f0;
        }
        .left-panel {
            width: 45%;
            background: linear-gradient(160deg, #0d1b6e, #1a2fa0);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -100px;
            left: -100px;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            bottom: -80px;
            right: -80px;
        }
        .big-mi {
            font-family: 'Playfair Display', serif;
            font-size: 180px;
            font-weight: 700;
            color: #4a90d9;
            line-height: 1;
            letter-spacing: -4px;
            text-shadow: 0 4px 30px rgba(74,144,217,0.4);
            z-index: 1;
        }
        .university-name {
            color: #d0e4f7;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 16px;
            z-index: 1;
            line-height: 1.8;
        }
        .divider-left {
            width: 50px;
            height: 2px;
            background: #4a90d9;
            margin: 20px auto;
            z-index: 1;
        }
        .tagline {
            color: #a8c4e8;
            font-size: 12px;
            text-align: center;
            letter-spacing: 1px;
            z-index: 1;
            font-style: italic;
        }
        .right-panel {
            width: 55%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #f5f8ff;
        }
        .form-box { width: 100%; max-width: 420px; }
        .form-title {
            font-size: 26px;
            font-weight: 700;
            color: #0d1b6e;
            margin-bottom: 6px;
        }
        .form-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 36px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #1a2fa0;
            margin-bottom: 6px;
        }
        .form-control {
            border: 1.5px solid #d0e4f7;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
            width: 100%;
        }
        .form-control:focus {
            border-color: #1a2fa0;
            box-shadow: 0 0 0 3px rgba(26,47,160,0.12);
            outline: none;
        }
        .btn-login {
            background: linear-gradient(135deg, #0d1b6e, #1a2fa0);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            letter-spacing: 0.5px;
        }
        .btn-login:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: white;
            border: 1.5px solid #dde6ef;
            color: #222;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(15,23,42,0.04);
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .btn-google:hover {
            box-shadow: 0 4px 16px rgba(15,23,42,0.1);
            border-color: #c0d0e0;
            color: #222;
        }
        .btn-google img { width: 20px; height: 20px; }
        .separator {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: #adb5bd;
            font-size: 13px;
        }
        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }
        .alert-danger {
            background: #fff5f5;
            border: 1px solid #f5c2c7;
            color: #842029;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-success-login {
            background: #e8f0fe;
            border: 1px solid #4a90d9;
            color: #0d1b6e;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- PANNEAU GAUCHE -->
    <div class="left-panel">
        <div class="big-mi">MI</div>
        <div class="divider-left"></div>
        <div class="university-name">
            UFR Mathématique-Informatique<br>
            Université Félix Houphouët-Boigny
        </div>
        <div class="divider-left"></div>
        <div class="tagline">Journal d'activités hebdomadaire</div>
    </div>

    <!-- PANNEAU DROIT -->
    <div class="right-panel">
        <div class="form-box">

            <div class="form-title">Connectez-vous à votre espace Newsletter</div>
            <div class="form-subtitle">Bienvenue sur la plateforme de l'UFR-MI</div>

            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert-danger">
                    <?php
                   $erreurs = [
                     
                     'identifiants_incorrects' => '❌ Email ou mot de passe incorrect.',
                     'champs_vides'            => '❌ Veuillez remplir tous les champs.',
                     'oauth_echec'             => '❌ Échec de l\'authentification Google.',
                     'compte_en_attente'       => '⏳ Votre compte est en attente de validation par l\'administrateur. Vous recevrez un email dès que votre demande sera traitée.',
                     'compte_refuse'           => '❌ Votre demande a été refusée par l\'administration. Contactez l\'UFR-MI pour plus d\'informations.',
                     'compte_desactive'        => '⚠️ Votre compte a été désactivé. Contactez l\'administration.',
                     ];

                    echo $erreurs[$_GET['erreur']] ?? '❌ Une erreur est survenue.';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['succes']) && $_GET['succes'] === 'compte_en_attente'): ?>
                <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:8px;
                            padding:14px 16px; font-size:13px; color:#856404; margin-bottom:20px;">
                    <strong>Demande envoyée !</strong><br>
                    Votre compte est en attente de validation par l'administrateur.
                    Vous recevrez un email dès que votre demande sera traitée.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['succes']) && $_GET['succes'] === 'compte_cree'): ?>
                <div class="alert-success-login">
                    Compte créé avec succès ! Connectez-vous maintenant.
                </div>
            <?php endif; ?>

            <form action="index.php?page=auth_action" method="POST">
                <input type="hidden" name="action" value="login">

                <div class="mb-3">
                    <label class="form-label">Adresse email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           placeholder="Votre adresse email"
                           autocomplete="off"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password"
                           name="mot_de_passe"
                           class="form-control"
                           placeholder="••••••••"
                           autocomplete="new-password"
                           required>
                </div>

                <button type="submit" class="btn-login">Se connecter</button>
            </form>

            <div class="separator">ou</div>

            <a href="app/controllers/GoogleAuthController.php" class="btn-google">
                <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 533.5 544.3'><path fill='%23ea4335' d='M533.5 278.4c0-17.6-1.4-34.6-4-51.1H272v96.7h146.9c-6.4 34.6-25.9 63.9-55.4 83.6v69.5h89.4c52.5-48.3 82.6-119.7 82.6-199.4z'/><path fill='%234285f4' d='M272 544.3c73.7 0 135.6-24.4 180.8-66.2l-89.4-69.5c-25 16.9-56.6 27.1-91.4 27.1-70.3 0-129.9-47.5-151.3-111.4H28.6v69.9C73.7 486.6 167.6 544.3 272 544.3z'/><path fill='%2396be3b' d='M120.7 328.3c-10.8-32.5-10.8-67.4 0-99.9V158.5H28.6c-36.6 72.9-36.6 159.8 0 232.7l92.1-63z'/><path fill='%23fbbc04' d='M272 107.7c39.9 0 75.9 13.7 104.3 40.7l78.2-78.2C403.8 24.4 341.8 0 272 0 167.6 0 73.7 57.7 28.6 158.5l92.1 69.9C142.1 155.2 201.7 107.7 272 107.7z'/></svg>"
                     alt="Google logo">
                Se connecter avec Google
            </a> 
            <div style="text-align:center; margin-top:20px; font-size:14px; color:#6c757d;">
            Pas encore de compte ?
            <a href="index.php?page=register"
             style="color:#1a2fa0; font-weight:600; text-decoration:none;">
             Créer un compte
    </a>
</div>

        </div>
    </div>

</body>
</html>