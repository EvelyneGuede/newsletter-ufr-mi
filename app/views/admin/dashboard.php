<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

$user = $_SESSION['user'];

// Rediriger selon le rôle
if ($user['role'] === 'etudiant') {
    header('Location: index.php?page=etudiant_dashboard');
    exit;
} elseif ($user['role'] === 'enseignant') {
    header('Location: index.php?page=enseignant_dashboard');
    exit;
}

// Récupérer les statistiques
require_once ROOT_PATH . '/config/database.php';
$db = getDB();

$nb_utilisateurs = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nb_articles     = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$nb_en_attente   = $db->query("SELECT COUNT(*) FROM articles WHERE statut = 'en_attente'")->fetchColumn();
$nb_newsletters  = $db->query("SELECT COUNT(*) FROM newsletters")->fetchColumn();
?>
<?php
$titre_page = 'Tableau de bord';
$page_active = 'dashboard';
require VIEWS_PATH . '/layouts/header.php';
?>
    <style>
        /* ── Cartes statistiques ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card.green  { border-color: #2929cc; }
        .stat-card.blue   { border-color: #2196f3; }
        .stat-card.orange { border-color: #ff9800; }
        .stat-card.purple { border-color: #9c27b0; }
        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #1515b5;
            margin: 8px 0 4px;
            line-height: 1;
        }
        .stat-desc { font-size: 12px; color: #adb5bd; }

        /* ── Section actions rapides ── */
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1515b5;
            margin-bottom: 16px;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }
        .action-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .action-icon { font-size: 36px; margin-bottom: 12px; }
        .action-title { font-size: 14px; font-weight: 600; color: #1515b5; }
        .action-desc  { font-size: 12px; color: #6c757d; margin-top: 4px; }

        /* Badge en attente */
        .badge-attente {
            display: inline-block;
            background: #ff9800;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 6px;
        }
    </style>
</head>
<body>
<?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <!-- ══ CONTENU PRINCIPAL ══ -->
    <div class="main-content">

        <div class="page-header">
            <h1>Bonjour, <?= htmlspecialchars($user['prenom']) ?> </h1>
            <p>Voici un aperçu de l'activité de la plateforme</p>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card green">
                <div class="stat-label">Utilisateurs</div>
                <div class="stat-value"><?= $nb_utilisateurs ?></div>
                <div class="stat-desc">Comptes actifs</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Articles</div>
                <div class="stat-value"><?= $nb_articles ?></div>
                <div class="stat-desc">Total soumis</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">En attente</div>
                <div class="stat-value"><?= $nb_en_attente ?></div>
                <div class="stat-desc">À valider</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-label">Newsletters</div>
                <div class="stat-value"><?= $nb_newsletters ?></div>
                <div class="stat-desc">Éditées</div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="section-title">Actions rapides</div>
        <div class="actions-grid">
            <a class="action-card" href="index.php?page=articles">
                <div class="action-icon"></div>
                <div class="action-title">Valider les articles</div>
                <div class="action-desc"><?= $nb_en_attente ?> article(s) en attente</div>
            </a>
            <a class="action-card" href="index.php?page=newsletters">
                <div class="action-icon"></div>
                <div class="action-title">Créer une newsletter</div>
                <div class="action-desc">Composer et planifier l'envoi</div>
            </a>
            <a class="action-card" href="index.php?page=utilisateurs">
                <div class="action-icon"></div>
                <div class="action-title">Gérer les utilisateurs</div>
                <div class="action-desc"><?= $nb_utilisateurs ?> utilisateur(s) enregistré(s)</div>
            </a>
        </div>

    </div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>

