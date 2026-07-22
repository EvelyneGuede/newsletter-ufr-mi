<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once ROOT_PATH . '/config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

$articles = $db->query("
    SELECT a.*, u.nom, u.prenom, u.role as role_auteur, c.libelle as categorie
    FROM articles a
    LEFT JOIN utilisateurs u ON a.auteur_id = u.id
    LEFT JOIN categories c ON a.categorie_id = c.id
    ORDER BY
        CASE a.statut WHEN 'en_attente' THEN 0 ELSE 1 END,
        a.created_at DESC
")->fetchAll();
?>
<?php
$titre_page = 'Articles';
$page_active = 'articles';
require VIEWS_PATH . '/layouts/header.php';
?>
    <style>
        .article-card { background: #fff; border-radius: 14px; padding: 22px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid #dee2e6; }
        .article-card.en_attente { border-left-color: #ff9800; }
        .article-card.valide     { border-left-color: #2929cc; }
        .article-card.archive    { border-left-color: #dc3545; }
        .article-titre { font-size: 16px; font-weight: 700; color: #1515b5; margin-bottom: 6px; }
        .article-meta  { font-size: 12px; color: #6c757d; margin-bottom: 12px; }
        .article-contenu { font-size: 14px; color: #444; line-height: 1.6; margin-bottom: 16px; background: #f5f5ff; padding: 12px; border-radius: 8px; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-en_attente { background: #fff3cd; color: #856404; }
        .badge-valide     { background: #d1e7dd; color: #0f5132; }
        .badge-archive    { background: #f8d7da; color: #842029; }
        .badge-brouillon  { background: #e9ecef; color: #495057; }
        .actions { display: flex; gap: 10px; align-items: flex-start; flex-wrap: wrap; }
        .btn-valider { background: #2929cc; color: white; border: none; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-rejeter { background: #dc3545; color: white; border: none; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .reject-form { display: none; margin-top: 12px; width: 100%; }
        .reject-form textarea { width: 100%; border: 1.5px solid #e0e0ff; border-radius: 8px; padding: 10px; font-size: 13px; margin-bottom: 8px; min-height: 80px; }
        .alert-success { background: #f0fff4; border: 1px solid #e0e0ff; color: #1515b5; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1> Gestion des articles</h1>
            <p>Validez ou rejetez les articles soumis par les utilisateurs</p>
        </div>

        <?php if (isset($_GET['succes'])): ?>
            <div class="alert-success">
                <?= $_GET['succes'] === 'valide' ? ' Article validé avec succès !' : '🗑️ Article rejeté.' ?>
            </div>
        <?php endif; ?>

        <?php if (empty($articles)): ?>
            <div style="text-align:center; padding:60px; color:#adb5bd;">
                <div style="font-size:48px; margin-bottom:12px;"></div>
                <p>Aucun article pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
            <div class="article-card <?= $article['statut'] ?>">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div class="article-titre"><?= htmlspecialchars($article['titre']) ?></div>
                    <span class="badge badge-<?= $article['statut'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $article['statut'])) ?>
                    </span>
                </div>
                <div class="article-meta">
                    👤 <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?>
                    (<?= $article['role_auteur'] ?>) &nbsp;•&nbsp;
                    📂 <?= $article['categorie'] ?? 'Non classé' ?> &nbsp;•&nbsp;
                    🏷️ <?= ucfirst($article['type']) ?> &nbsp;•&nbsp;
                    📅 <?= date('d/m/Y à H:i', strtotime($article['date_soumission'])) ?>
                </div>
                <div class="article-contenu">
                    <?= nl2br(htmlspecialchars(substr($article['contenu'], 0, 300))) ?>
                    <?= strlen($article['contenu']) > 300 ? '...' : '' ?>
                </div>

                <?php if ($article['statut'] === 'en_attente'): ?>
                <div class="actions">
                    <!-- Valider -->
                    <form action="index.php" method="POST">
                        <input type="hidden" name="page" value="traitement_article">
                        <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                        <input type="hidden" name="action" value="valider">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <button type="submit" class="btn-valider"> Valider</button>
                    </form>

                    <!-- Rejeter -->
                    <button class="btn-rejeter" onclick="toggleRejet(<?= $article['id'] ?>)">
                        ❌ Rejeter
                    </button>

                    <form action="index.php" method="POST"
                          id="rejet-<?= $article['id'] ?>" class="reject-form">
                        <input type="hidden" name="page" value="traitement_article">
                        <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                        <input type="hidden" name="action" value="rejeter">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <textarea name="commentaire" placeholder="Motif du rejet (obligatoire)..." required></textarea>
                        <button type="submit" class="btn-rejeter">Confirmer le rejet</button>
                    </form>
                </div>
                <?php elseif ($article['commentaire_rejet']): ?>
                    <div style="font-size:13px; color:#dc3545; margin-top:8px;">
                        💬 Motif du rejet : <?= htmlspecialchars($article['commentaire_rejet']) ?>
                    </div>
                <?php endif; ?>

                <div style="margin-top:8px;">
                    <form action="index.php" method="POST" onsubmit="return confirm('Supprimer cet article ?');">
                        <input type="hidden" name="page" value="traitement_article">
                        <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <button type="submit" class="btn-supprimer" style="background:#fff;border:1px solid #f5c6cb;border-radius:8px;padding:8px 12px;cursor:pointer;">🗑️ Supprimer</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function toggleRejet(id) {
        const form = document.getElementById('rejet-' + id);
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }
    </script>
<?php require VIEWS_PATH . '/layouts/footer.php'; ?>

