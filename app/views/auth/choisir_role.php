<?php
if (!isset($_SESSION['google_user'])) {
    header('Location: index.php?page=login');
    exit;
}
$google_user = $_SESSION['google_user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir votre rôle — UFR-MI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f0ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 500px;
            width: 100%;
        }
        .mi {
            font-size: 48px;
            font-weight: 900;
            color: #1515b5;
            text-align: center;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 12px;
            color: #7ab3e0;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 28px;
        }
        .welcome {
            text-align: center;
            margin-bottom: 28px;
        }
        .welcome h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1515b5;
            margin-bottom: 6px;
        }
        .welcome p {
            font-size: 14px;
            color: #6c757d;
        }
        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        .role-card {
            border: 2px solid #e0e0ff;
            border-radius: 12px;
            padding: 20px 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .role-card:hover {
            border-color: #1515b5;
            background: #f0f0ff;
            transform: translateY(-2px);
        }
        .role-card.selected {
            border-color: #1515b5;
            background: #e8e8ff;
        }
        .role-card input[type="radio"] {
            display: none;
        }
        .role-icon { font-size: 36px; margin-bottom: 10px; }
        .role-name {
            font-size: 13px;
            font-weight: 600;
            color: #1515b5;
        }
        .role-desc {
            font-size: 11px;
            color: #6c757d;
            margin-top: 4px;
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #2929cc;
            margin-bottom: 6px;
            display: block;
        }
        .form-control {
            border: 1.5px solid #e0e0ff;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
            width: 100%;
            margin-bottom: 16px;
        }
        .form-control:focus {
            border-color: #2929cc;
            outline: none;
            box-shadow: 0 0 0 3px rgba(41,41,204,0.12);
        }
        .btn-submit {
            background: linear-gradient(135deg, #1515b5, #2929cc);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 13px;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-submit:hover { opacity: 0.9; }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="mi">MI</div>
        <div class="subtitle">UFR Mathématique-Informatique</div>

        <div class="welcome">
            <h2>Bienvenue, <?= htmlspecialchars($google_user['prenom']) ?> !</h2>
            <p>Vous êtes connecté avec <strong><?= htmlspecialchars($google_user['email']) ?></strong></p>
            <p style="margin-top:6px; color:#1515b5; font-size:13px;">
                Choisissez votre rôle pour continuer
            </p>
        </div>

        <div id="alerteRole" class="alert-warning">
             Veuillez sélectionner un rôle avant de continuer.
        </div>

        <form action="index.php?page=enregistrer_role" method="POST" onsubmit="return validerFormulaire()">

            <!-- Choix du rôle -->
            <div class="role-grid">
                <label class="role-card" id="card-etudiant">
                    <input type="radio" name="role" value="etudiant"
                           onchange="selectionner('etudiant')">
                    <div class="role-icon"></div>
                    <div class="role-name">Étudiant</div>
                    <div class="role-desc">Je suis étudiant à l'UFR-MI</div>
                </label>

                <label class="role-card" id="card-enseignant">
                    <input type="radio" name="role" value="enseignant"
                           onchange="selectionner('enseignant')">
                    <div class="role-icon"></div>
                    <div class="role-name">Enseignant</div>
                    <div class="role-desc">Je suis enseignant à l'UFR-MI</div>
                </label>

                <label class="role-card" id="card-administratif">
                    <input type="radio" name="role" value="administratif"
                           onchange="selectionner('administratif')">
                    <div class="role-icon"></div>
                    <div class="role-name">Administratif</div>
                    <div class="role-desc">Personnel administratif</div>
                </label>
            </div>

            <!-- Département -->
            <label class="form-label">Département (optionnel)</label>
            <input type="text" name="departement" class="form-control"
                   placeholder="Ex: Informatique, Mathématiques...">

            <button type="submit" class="btn-submit">
                Confirmer et accéder à la plateforme
            </button>
        </form>
    </div>

    <script>
    function selectionner(role) {
        // Retirer la sélection de toutes les cartes
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        // Sélectionner la carte cliquée
        document.getElementById('card-' + role).classList.add('selected');
        // Masquer l'alerte
        document.getElementById('alerteRole').style.display = 'none';
    }

    function validerFormulaire() {
        const roleSelectionne = document.querySelector('input[name="role"]:checked');
        if (!roleSelectionne) {
            document.getElementById('alerteRole').style.display = 'block';
            return false;
        }
        return true;
    }
    </script>
</body>
</html>