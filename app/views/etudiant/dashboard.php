<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'etudiant') {
    header('Location: index.php?page=login');
    exit;
}
$user = $_SESSION['user'];
require_once ROOT_PATH . '/config/database.php';
$db = getDB();

// Articles soumis par cet étudiant
$stmt = $db->prepare("SELECT * FROM articles WHERE auteur_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$mes_articles = $stmt->fetchAll();
?>
<?php
$titre_page = 'Espace Étudiant';
$page_active = 'etudiant_dashboard';
require VIEWS_PATH . '/layouts/header.php';
?>
    <style>
        .btn-soumettre {
            background: linear-gradient(135deg, #0d1b6e, #1a2fa0);
            color: white; border: none; border-radius: 10px;
            padding: 12px 24px; font-size: 14px; font-weight: 600;
            text-decoration: none; display: inline-block;
            margin-bottom: 28px; transition: opacity 0.2s;
        }
        .btn-soumettre:hover { opacity: 0.9; color: white; }
        .table-card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .table-card h2 { font-size: 16px; font-weight: 700; color: #0d1b6e; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; padding: 10px 12px; border-bottom: 2px solid #f0f4ff; }
        td { font-size: 14px; padding: 12px; border-bottom: 1px solid #f0f4ff; color: #333; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-brouillon  { background: #e9ecef; color: #495057; }
        .badge-en_attente { background: #fff3cd; color: #856404; }
        .badge-valide     { background: #d1e7dd; color: #0f5132; }
        .badge-publie     { background: #cfe2ff; color: #084298; }
        .badge-archive    { background: #f8d7da; color: #842029; }
        .empty-state { text-align: center; padding: 40px; color: #adb5bd; }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
    </style>
</head>
<body>
<?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Bonjour, <?= htmlspecialchars($user['prenom']) ?> </h1>
            <p>Suivez vos soumissions d'articles</p>
        </div>

        <a href="index.php?page=soumettre_article" class="btn-soumettre">
             Soumettre un nouvel article
        </a>

        <div class="table-card">
            <h2> Mes articles soumis</h2>
            <?php if (empty($mes_articles)): ?>
                <div class="empty-state">
                    <div class="icon"></div>
                    <p>Vous n'avez pas encore soumis d'article.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mes_articles as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article['titre']) ?></td>
                            <td><?= ucfirst($article['type']) ?></td>
                            <td>
                                <span class="badge badge-<?= $article['statut'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $article['statut'])) ?>
                                </span>
                            <td><?= date('d/m/Y', strtotime($article['date_soumission'])) ?></td>
                            <td>
                                 <?php if ($article['statut'] !== 'publie'): ?>
                                <form action="index.php" method="POST"
                                   onsubmit="return confirm('Supprimer cet article ?')">
                                  <input type="hidden" name="page" value="traitement_article">
                                  <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                                  <input type="hidden" name="action" value="supprimer">
                                  <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                 <button type="submit" style="background:#f8d7da; color:#842029; border:none; border-radius:6px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer;">
                                   Supprimer
                                 </button>
                                </form>
                             <?php else: ?>
                                      <span style="font-size:11px; color:#adb5bd;">Publié</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                                <a href="index.php?page=modifier_article&id=<?= $article['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                                <a href="index.php?page=supprimer_article&id=<?= $article['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php require VIEWS_PATH . '/layouts/footer.php'; ?>