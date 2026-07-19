<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}
require_once 'config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

// Récupérer les infos à jour
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user['id']]);
$profil = $stmt->fetch();

$retour = $user['role'] === 'etudiant'
    ? 'etudiant_dashboard'
    : ($user['role'] === 'enseignant' ? 'enseignant_dashboard' : 'dashboard');
?>
<?php
$titre_page = 'Mon profil';
$page_active = 'profil';
require 'app/views/layouts/header.php';
?>
    <style>
        .user-avatar-lg { width: 80px; height: 80px; border-radius: 50%; background: #4a90d9; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #0d1b6e; font-size: 32px; margin: 0 auto 16px; }
        .profil-card { background: #fff; border-radius: 14px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); max-width: 600px; }
        .profil-header { text-align: center; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid #f0f4ff; }
        .profil-nom { font-size: 22px; font-weight: 700; color: #0d1b6e; margin-bottom: 4px; }
        .profil-role { font-size: 13px; color: #6c757d; }
        .form-label { font-size: 13px; font-weight: 600; color: #1a2fa0; margin-bottom: 6px; display: block; }
        .form-control { border: 1.5px solid #d0e4f7; border-radius: 8px; padding: 11px 14px; font-size: 14px; width: 100%; margin-bottom: 16px; }
        .form-control:focus { border-color: #1a2fa0; box-shadow: 0 0 0 3px rgba(45,106,79,0.12); outline: none; }
        .section-title { font-size: 15px; font-weight: 700; color: #0d1b6e; margin: 24px 0 16px; padding-top: 24px; border-top: 1px solid #f0f4ff; }
        .btn-save { background: linear-gradient(135deg, #0d1b6e, #1a2fa0); color: white; border: none; border-radius: 8px; padding: 12px 28px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-save:hover { opacity: 0.9; }
        .alert-success { background: #f0fff4; border: 1px solid #d0e4f7; color: #0d1b6e; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="profil-card">

            <!-- En-tête profil -->
            <div class="profil-header">
                <div class="user-avatar-lg">
                    <?= strtoupper(substr($profil['prenom'], 0, 1)) ?>
                </div>
                <div class="profil-nom"><?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?></div>
                <div class="profil-role"><?= ucfirst($profil['role']) ?> — <?= htmlspecialchars($profil['departement'] ?? 'UFR-MI') ?></div>
            </div>

            <?php if (isset($_GET['succes'])): ?>
                <div class="alert-success"> Profil mis à jour avec succès !</div>
            <?php endif; ?>

            <!-- Formulaire modification -->
            <form action="index.php" method="POST">
                <input type="hidden" name="page" value="traitement_profil">
                <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                <input type="hidden" name="action" value="modifier">

                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control"
                       value="<?= htmlspecialchars($profil['prenom']) ?>" required>

                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control"
                       value="<?= htmlspecialchars($profil['nom']) ?>" required>

                <label class="form-label">Département</label>
                <input type="text" name="departement" class="form-control"
                       value="<?= htmlspecialchars($profil['departement'] ?? '') ?>"
                       placeholder="Ex: Informatique">

                <div class="section-title">🔐 Changer le mot de passe</div>

                <label class="form-label">Nouveau mot de passe <small style="color:#adb5bd">(laisser vide pour ne pas changer)</small></label>
                <input type="password" name="nouveau_mdp" class="form-control" placeholder="••••••••">

                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="confirmer_mdp" class="form-control" placeholder="••••••••">

                <button type="submit" class="btn-save">💾 Enregistrer les modifications</button>
            </form>
        </div>
    </div>
<?php require 'app/views/layouts/footer.php'; ?>