<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}
require_once 'config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

$newsletters = $db->query("
    SELECT n.*, COUNT(na.article_id) as nb_articles
    FROM newsletters n
    LEFT JOIN newsletter_articles na ON n.id = na.newsletter_id
    WHERE n.statut = 'envoyee'
    GROUP BY n.id
    ORDER BY n.date_envoi DESC
")->fetchAll();

$retour = $user['role'] === 'etudiant'
    ? 'etudiant_dashboard'
    : ($user['role'] === 'enseignant' ? 'enseignant_dashboard' : 'dashboard');
?>
<?php
$titre_page = 'Archives';
$page_active = 'archives';
require 'app/views/layouts/header.php';
?>
    <style>
        .nl-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .nl-card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s; }
        .nl-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .nl-icon { font-size: 36px; margin-bottom: 14px; }
        .nl-title { font-size: 15px; font-weight: 700; color: #0d1b6e; margin-bottom: 8px; line-height: 1.4; }
        .nl-meta { font-size: 12px; color: #6c757d; margin-bottom: 16px; }
        .btn-lire { display: block; text-align: center; background: linear-gradient(135deg, #0d1b6e, #1a2fa0); color: white; border-radius: 8px; padding: 10px; font-size: 13px; font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
        .btn-lire:hover { opacity: 0.9; color: white; }
        .empty-state { text-align: center; padding: 60px; color: #adb5bd; }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
        @media (max-width: 900px) { .nl-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1> Archives des newsletters</h1>
            <p>Consultez toutes les éditions passées</p>
        </div>

        <?php if (empty($newsletters)): ?>
            <div class="empty-state">
                <div class="icon"></div>
                <p>Aucune newsletter envoyée pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="nl-grid">
                <?php foreach ($newsletters as $nl): ?>
                <div class="nl-card">
                    <div class="nl-icon"></div>
                    <div class="nl-title"><?= htmlspecialchars($nl['titre']) ?></div>
                    <div class="nl-meta">
                        📅 <?= date('d/m/Y', strtotime($nl['date_envoi'])) ?><br>
                        📄 <?= $nl['nb_articles'] ?> article(s)
                    </div>
                    <a href="index.php?page=apercu_newsletter&id=<?= $nl['id'] ?>" class="btn-lire">
                        Lire cette édition
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php require 'app/views/layouts/footer.php'; ?>