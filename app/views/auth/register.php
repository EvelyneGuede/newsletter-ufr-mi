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
    <title>Inscription — Newsletter UFR-MI</title>
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
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -100px; left: -100px;
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
            width: 50px; height: 2px;
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
            overflow-y: auto;
        }
        .form-box { width: 100%; max-width: 420px; }
        .form-title {
            font-size: 22px;
            font-weight: 700;
            color: #0d1b6e;
            margin-bottom: 6px;
        }
        .form-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 28px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #1a2fa0;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            border: 1.5px solid #d0e4f7;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a2fa0;
            box-shadow: 0 0 0 3px rgba(26,47,160,0.12);
            outline: none;
        }
        .btn-register {
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
        }
        .btn-register:hover { opacity: 0.92; transform: translateY(-1px); }
        .login-link {
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            margin-top: 20px;
        }
        .login-link a {
            color: #1a2fa0;
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover { text-decoration: underline; }
        .alert-danger {
            background: #fff5f5;
            border: 1px solid #f5c2c7;
            color: #842029;
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

            <div class="form-title">Créer un compte</div>
            <div class="form-subtitle">Rejoignez la plateforme de l'UFR-MI</div>

            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert-danger">
                    <?php
                    $erreurs = [
                        'champs_vides'  => '❌ Veuillez remplir tous les champs.',
                        'email_existe'  => '❌ Cet email est déjà utilisé.',
                        'role_invalide' => '❌ Rôle invalide.',
                    ];
                    echo $erreurs[$_GET['erreur']] ?? '❌ Une erreur est survenue.';
                    ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=auth_action" method="POST">
                <input type="hidden" name="action" value="register">

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Votre nom"
                               autocomplete="off" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control"
                               placeholder="Votre prénom"
                               autocomplete="off" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="Votre adresse email"
                           autocomplete="off" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Choisir votre rôle --</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="administratif">Administratif</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Département</label>
                    <select name="departement" class="form-select" autocomplete="off">
                        <option value="">-- Choisir un département --</option>
                        <option value="INFORMATIQUE">INFORMATIQUE</option>
                        <option value="MATHEMATIQUE">MATHEMATIQUE</option>
                        <option value="MECANIQUE">MECANIQUE</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control"
                           placeholder="••••••••"
                           autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn-register">Créer mon compte</button>
            </form>

            <div class="login-link">
                Déjà un compte ?
                <a href="index.php?page=login">Se connecter</a>
            </div>

        </div>
    </div>

</body>
</html>
