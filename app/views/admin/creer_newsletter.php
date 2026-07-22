<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once ROOT_PATH . '/config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

// Articles validés ou publiés disponibles
$articles = $db->query("
    SELECT a.*, u.nom, u.prenom
    FROM articles a
    LEFT JOIN utilisateurs u ON a.auteur_id = u.id
    WHERE a.statut IN ('valide', 'publie', 'validé', 'publié')
    ORDER BY COALESCE(a.date_validation, a.created_at) DESC
")->fetchAll();
?>
<?php
$titre_page = 'Créer une newsletter';
$page_active = 'newsletters';
require VIEWS_PATH . '/layouts/header.php';
?>
</head>
<body>
<?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="form-card">
            <h1> Créer une newsletter</h1>
            <p>Sélectionnez les articles validés à inclure dans cette édition.</p>

            <form action="index.php" method="POST">
                <input type="hidden" name="page" value="traitement_newsletter">
                <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                <input type="hidden" name="action" value="creer">

                <div class="mb-4">
                    <label class="form-label">Titre de l'édition *</label>
                    <input type="text" name="titre" class="form-control"
                           placeholder="Ex: Newsletter UFR-MI — Semaine du 12 mai 2026" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Date d'envoi planifiée</label>
                    <input type="datetime-local" name="date_planifiee" class="form-control">
                </div>

                <div class="mb-4">
                    <label class="form-label">Articles à inclure *</label>
                    <?php if (empty($articles)): ?>
                        <div class="empty-articles">
                            ⚠️ Aucun article validé ou publié disponible pour le moment.<br>
                            <a href="index.php?page=articles" style="color:#856404; font-weight:600;">
                                Valider des articles d'abord →
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                        <label class="article-checkbox" id="cb-<?= $article['id'] ?>">
                            <div class="article-checkbox-row">
                                <input type="checkbox" name="articles[]"
                                       value="<?= $article['id'] ?>"
                                       onchange="toggleSelected(<?= $article['id'] ?>)">
                                <div>
                                    <div class="article-titre-cb"><?= htmlspecialchars($article['titre']) ?></div>
                                    <div class="article-meta-cb">
                                        👤 <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?>
                                        &nbsp;•&nbsp; 🏷️ <?= ucfirst($article['type']) ?>
                                        &nbsp;•&nbsp; 📅 <?= date('d/m/Y', strtotime($article['date_validation'] ?: $article['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a href="index.php?page=newsletters" class="btn-retour">← Retour</a>
                <?php if (!empty($articles)): ?>
                    <button type="submit" class="btn-submit">💾 Créer la newsletter</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
    function toggleSelected(id) {
        const label = document.getElementById('cb-' + id);
        const cb    = label.querySelector('input');
        label.classList.toggle('selected', cb.checked);
    }
    </script>
<?php require VIEWS_PATH . '/layouts/footer.php'; ?>

