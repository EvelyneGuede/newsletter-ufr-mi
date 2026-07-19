<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once 'config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM newsletters WHERE id = ?");
$stmt->execute([$id]);
$newsletter = $stmt->fetch();

if (!$newsletter) {
    header('Location: index.php?page=newsletters');
    exit;
}
?>
<?php
$titre_page = 'Aperçu Newsletter';
$page_active = 'newsletters';
require 'app/views/layouts/header.php';
?>
    <style>
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
        .top-bar h1 { font-size: 22px; font-weight: 700; color: #1515b5; }
        .btn-envoyer { background: linear-gradient(135deg, #1515b5, #2929cc); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-envoyer:hover { opacity: 0.9; }
        .btn-retour { background: #f0f0ff; color: #2929cc; border: none; border-radius: 10px; padding: 12px 20px; font-size: 14px; font-weight: 600; text-decoration: none; margin-right: 10px; display: inline-block; }
        .alert-success { background: #f0fff4; border: 1px solid #e0e0ff; color: #1515b5; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; }
        .preview-frame { width: 100%; border: none; border-radius: 14px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); min-height: 600px; background: #fff; }
        .badge-envoyee  { background: #d1e7dd; color: #0f5132; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-brouillon { background: #e9ecef; color: #495057; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="top-bar">
            <div>
                <h1><?= htmlspecialchars($newsletter['titre']) ?></h1>
                <div style="margin-top:6px;">
                    <span class="badge-<?= $newsletter['statut'] ?>"><?= ucfirst($newsletter['statut']) ?></span>
                    <span style="font-size:13px; color:#6c757d; margin-left:10px;">
                        Créée le <?= date('d/m/Y', strtotime($newsletter['created_at'])) ?>
                    </span>
                </div>
            </div>
            <div class="top-actions">
                <img src="app/views/admin/logomi.jpeg" alt="Logo du département"
                     style="width:72px; height:auto; max-height:60px; object-fit:contain; border-radius:10px; background:#fff; padding:6px; border:1px solid #e0e0ff; box-shadow:0 4px 12px rgba(21,21,181,0.12);">
                <div>
                    <a href="index.php?page=newsletters" class="btn-retour">← Retour</a>
                    <?php if ($newsletter['statut'] === 'brouillon'): ?>
                    <form action="index.php" method="POST" style="display:inline;">
                        <input type="hidden" name="page" value="traitement_newsletter">
                        <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                        <input type="hidden" name="action" value="envoyer">
                        <input type="hidden" name="newsletter_id" value="<?= $newsletter['id'] ?>">
                        <button type="submit" class="btn-envoyer"
                                onclick="return confirm('Envoyer cette newsletter à tous les abonnés ?')">
                             Envoyer la newsletter
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['succes'])): ?>
            <div class="alert-success">
                <?php if ($_GET['succes'] === 'cree'): ?>
                     Newsletter créée avec succès ! Vérifiez l'aperçu avant d'envoyer.
                <?php elseif ($_GET['succes'] === 'envoye'): ?>
                     Newsletter envoyée à <?= $_GET['nb'] ?? 0 ?> destinataire(s) !
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erreur']) && $_GET['erreur'] === 'composer_manquant'): ?>
            <div class="alert-warning">
                PHPMailer non installé. Exécutez <code>composer require phpmailer/phpmailer</code> dans le terminal.
            </div>
        <?php endif; ?>

        <!-- Aperçu de la newsletter dans un iframe -->
        <iframe class="preview-frame"
                srcdoc="<?= htmlspecialchars($newsletter['template_html']) ?>"
                style="height:700px;">
        </iframe>
    </div>
<?php require 'app/views/layouts/footer.php'; ?>