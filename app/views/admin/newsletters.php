<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once 'config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

$newsletters = $db->query("
    SELECT n.*, u.nom, u.prenom,
           COUNT(na.article_id) as nb_articles
    FROM newsletters n
    LEFT JOIN utilisateurs u ON n.created_by = u.id
    LEFT JOIN newsletter_articles na ON n.id = na.newsletter_id
    GROUP BY n.id
    ORDER BY n.created_at DESC
")->fetchAll();
?>
<?php
$titre_page = 'Newsletters';
$page_active = 'newsletters';
require 'app/views/layouts/header.php';
?>
    <style>
        .btn-creer { background: linear-gradient(135deg, #1515b5, #2929cc); color: white; border: none; border-radius: 10px; padding: 12px 24px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; transition: opacity 0.2s; }
        .btn-creer:hover { opacity: 0.9; color: white; }
        .nl-card { background: #fff; border-radius: 14px; padding: 22px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .nl-title-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap; }
        .nl-title { font-size: 16px; font-weight: 700; color: #1515b5; }
        .nl-title-logo { width: 34px; height: 34px; object-fit: contain; border-radius: 8px; background: #fff; padding: 4px; border: 1px solid #e0e0ff; box-shadow: 0 2px 8px rgba(21,21,181,0.10); }
        .nl-meta { font-size: 12px; color: #6c757d; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-brouillon  { background: #e9ecef; color: #495057; }
        .badge-planifiee  { background: #fff3cd; color: #856404; }
        .badge-envoyee    { background: #d1e7dd; color: #0f5132; }
        .badge-archivee   { background: #f8d7da; color: #842029; }
        .nl-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-apercu { background: #e8e8ff; color: #2929cc; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none; }
        .btn-apercu:hover { background: #c8e6c9; color: #1515b5; }
        .empty-state { text-align: center; padding: 60px; color: #adb5bd; }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
    </style>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1> Newsletters</h1>
                <p>Créez et envoyez les éditions hebdomadaires</p>
            </div>
            <a href="index.php?page=creer_newsletter" class="btn-creer">+ Nouvelle newsletter</a>
        </div>

        <?php if (empty($newsletters)): ?>
            <div class="empty-state">
                <div class="icon"></div>
                <p>Aucune newsletter créée pour le moment.</p>
                <a href="index.php?page=creer_newsletter" class="btn-creer" style="margin-top:16px; display:inline-block;">
                    + Créer la première newsletter
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($newsletters as $nl): ?>
            <div class="nl-card">
                <div>
                    <div class="nl-title-row">
                        <div class="nl-title"><?= htmlspecialchars($nl['titre']) ?></div>
                        <img src="app/views/admin/logomi.jpeg" alt="Logo du département" class="nl-title-logo">
                    </div>
                    <div class="nl-meta">
                         <?= date('d/m/Y', strtotime($nl['created_at'])) ?> &nbsp;•&nbsp;
                         <?= $nl['nb_articles'] ?> article(s) &nbsp;•&nbsp;
                         <?= htmlspecialchars($nl['prenom'] . ' ' . $nl['nom']) ?>
                    </div>
                </div>
                <div class="nl-actions">
                    <span class="badge badge-<?= $nl['statut'] ?>">
                        <?= ucfirst($nl['statut']) ?>
                    </span>
                    <a href="index.php?page=apercu_newsletter&id=<?= $nl['id'] ?>" class="btn-apercu">
                         Aperçu
                    </a>
                    <form action="index.php" method="POST" onsubmit="return confirm('Supprimer cette newsletter ?');" style="display:inline-block;">
                        <input type="hidden" name="page" value="traitement_newsletter">
                        <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                        <input type="hidden" name="action" value="supprimer_newsletter">
                        <input type="hidden" name="newsletter_id" value="<?= $nl['id'] ?>">
                        <button type="submit" title="Supprimer" style="background:transparent;border:none;font-size:18px;cursor:pointer;margin-left:6px;">🗑️</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php require 'app/views/layouts/footer.php'; ?>

