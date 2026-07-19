<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once 'config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

// ── Statistiques générales ──
$nb_utilisateurs  = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nb_etudiants     = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='etudiant'")->fetchColumn();
$nb_enseignants   = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='enseignant'")->fetchColumn();
$nb_administratifs= $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='administratif'")->fetchColumn();

$nb_articles      = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$nb_valides       = $db->query("SELECT COUNT(*) FROM articles WHERE statut='valide' OR statut='publie'")->fetchColumn();
$nb_en_attente    = $db->query("SELECT COUNT(*) FROM articles WHERE statut='en_attente'")->fetchColumn();
$nb_rejetes       = $db->query("SELECT COUNT(*) FROM articles WHERE statut='archive'")->fetchColumn();

$nb_newsletters   = $db->query("SELECT COUNT(*) FROM newsletters")->fetchColumn();
$nb_envoyees      = $db->query("SELECT COUNT(*) FROM newsletters WHERE statut='envoyee'")->fetchColumn();
$total_envoyes    = $db->query("SELECT SUM(nb_destinataires) FROM newsletters WHERE statut='envoyee'")->fetchColumn() ?? 0;

// ── Articles par type ──
$articles_par_type = $db->query("
    SELECT type, COUNT(*) as nb
    FROM articles
    GROUP BY type
    ORDER BY nb DESC
")->fetchAll();

// ── Newsletters par mois ──
$nl_par_mois = $db->query("
    SELECT DATE_FORMAT(created_at, '%M %Y') as mois, COUNT(*) as nb
    FROM newsletters
    GROUP BY mois
    ORDER BY created_at DESC
    LIMIT 6
")->fetchAll();

// ── Top auteurs ──
$top_auteurs = $db->query("
    SELECT u.prenom, u.nom, u.role, COUNT(a.id) as nb_articles
    FROM utilisateurs u
    LEFT JOIN articles a ON a.auteur_id = u.id
    GROUP BY u.id
    HAVING nb_articles > 0
    ORDER BY nb_articles DESC
    LIMIT 5
")->fetchAll();

// ── Dernières newsletters envoyées ──
$dernieres_nl = $db->query("
    SELECT titre, nb_destinataires, date_envoi
    FROM newsletters
    WHERE statut = 'envoyee'
    ORDER BY date_envoi DESC
    LIMIT 5
")->fetchAll();
?>
<?php
$titre_page = 'Statistiques';
$page_active = 'statistiques';
require 'app/views/layouts/header.php';
?>
    <style>
        /* Cartes stats */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px; }
        .stat-card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-top: 4px solid; }
        .stat-card.blue   { border-color: #0d1b6e; }
        .stat-card.sky    { border-color: #4a90d9; }
        .stat-card.orange { border-color: #ff9800; }
        .stat-card.purple { border-color: #9c27b0; }
        .stat-label { font-size: 11px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; }
        .stat-value { font-size: 36px; font-weight: 700; color: #0d1b6e; margin: 8px 0 4px; line-height: 1; }
        .stat-desc  { font-size: 12px; color: #adb5bd; }

        /* Grille graphiques */
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }
        .chart-card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .chart-title { font-size: 15px; font-weight: 700; color: #0d1b6e; margin-bottom: 20px; }

        /* Tableaux */
        .tables-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .table-card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .table-card h2 { font-size: 15px; font-weight: 700; color: #0d1b6e; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 11px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; padding: 8px 12px; border-bottom: 2px solid #f0f4ff; text-align: left; }
        td { font-size: 13px; padding: 10px 12px; border-bottom: 1px solid #f0f4ff; color: #333; }
        .badge { padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-etudiant      { background: #f8bbd0; color: #880e4f; }
        .badge-enseignant    { background: #bbdefb; color: #1565c0; }
        .badge-administratif { background: #c8e6c9; color: #1b5e20; }
        .progress-bar-wrap { background: #f0f4ff; border-radius: 10px; height: 8px; margin-top: 4px; }
        .progress-bar-fill { height: 8px; border-radius: 10px; background: linear-gradient(90deg, #0d1b6e, #4a90d9); }
        .empty-state { text-align: center; padding: 30px; color: #adb5bd; font-size: 13px; }
    </style>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <!-- CONTENU -->
    <div class="main-content">
        <div class="page-header">
            <h1> Statistiques</h1>
            <p>Vue d'ensemble de l'activité de la plateforme</p>
        </div>

        <!-- Cartes chiffres clés -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Utilisateurs</div>
                <div class="stat-value"><?= $nb_utilisateurs ?></div>
                <div class="stat-desc"><?= $nb_etudiants ?> étudiants · <?= $nb_enseignants ?> enseignants</div>
            </div>
            <div class="stat-card sky">
                <div class="stat-label">Articles soumis</div>
                <div class="stat-value"><?= $nb_articles ?></div>
                <div class="stat-desc"><?= $nb_valides ?> validés · <?= $nb_en_attente ?> en attente</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Newsletters</div>
                <div class="stat-value"><?= $nb_newsletters ?></div>
                <div class="stat-desc"><?= $nb_envoyees ?> envoyée(s)</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-label">Emails envoyés</div>
                <div class="stat-value"><?= $total_envoyes ?></div>
                <div class="stat-desc">Total destinataires atteints</div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="charts-grid">

            <!-- Graphique utilisateurs par rôle -->
            <div class="chart-card">
                <div class="chart-title"> Utilisateurs par rôle</div>
                <canvas id="chartRoles" height="200"></canvas>
            </div>

            <!-- Graphique articles par type -->
            <div class="chart-card">
                <div class="chart-title"> Articles par type</div>
                <canvas id="chartArticles" height="200"></canvas>
            </div>

        </div>

        <!-- Tableaux -->
        <div class="tables-grid">

            <!-- Top auteurs -->
            <div class="table-card">
                <h2>🏆 Top 5 auteurs</h2>
                <?php if (empty($top_auteurs)): ?>
                    <div class="empty-state">Aucun article soumis pour le moment.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Auteur</th>
                                <th>Rôle</th>
                                <th>Articles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $max = $top_auteurs[0]['nb_articles'] ?? 1;
                            foreach ($top_auteurs as $auteur): ?>
                            <tr>
                                <td><?= htmlspecialchars($auteur['prenom'] . ' ' . $auteur['nom']) ?></td>
                                <td><span class="badge badge-<?= $auteur['role'] ?>"><?= ucfirst($auteur['role']) ?></span></td>
                                <td>
                                    <strong><?= $auteur['nb_articles'] ?></strong>
                                    <div class="progress-bar-wrap">
                                        <div class="progress-bar-fill"
                                             style="width:<?= ($auteur['nb_articles'] / $max * 100) ?>%">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Dernières newsletters -->
            <div class="table-card">
                <h2> Dernières newsletters envoyées</h2>
                <?php if (empty($dernieres_nl)): ?>
                    <div class="empty-state">Aucune newsletter envoyée pour le moment.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Destinataires</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dernieres_nl as $nl): ?>
                            <tr>
                                <td style="font-size:12px;"><?= htmlspecialchars(substr($nl['titre'], 0, 30)) ?>...</td>
                                <td style="text-align:center; font-weight:700; color:#0d1b6e;">
                                    <?= $nl['nb_destinataires'] ?>
                                </td>
                                <td style="font-size:12px; color:#6c757d;">
                                    <?= date('d/m/Y', strtotime($nl['date_envoi'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Graphiques Chart.js -->
    <script>
    // Données PHP → JavaScript
    const rolesData = {
        labels: ['Étudiants', 'Enseignants', 'Administratifs'],
        datasets: [{
            data: [<?= $nb_etudiants ?>, <?= $nb_enseignants ?>, <?= $nb_administratifs ?>],
            backgroundColor: ['#4a90d9', '#0d1b6e', '#7ab3e0'],
            borderWidth: 0,
            hoverOffset: 8
        }]
    };

    const typesLabels = <?= json_encode(array_column($articles_par_type, 'type')) ?>;
    const typesData   = <?= json_encode(array_column($articles_par_type, 'nb')) ?>;
    const typesColors = ['#2196f3', '#e74c3c', '#f39c12', '#27ae60', '#9b59b6'];

    // Graphique rôles (donut)
    new Chart(document.getElementById('chartRoles'), {
        type: 'doughnut',
        data: rolesData,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            },
            cutout: '65%'
        }
    });

    // Graphique articles (barres)
    new Chart(document.getElementById('chartArticles'), {
        type: 'bar',
        data: {
            labels: typesLabels.map(t => t.charAt(0).toUpperCase() + t.slice(1)),
            datasets: [{
                label: 'Nombre d\'articles',
                data: typesData,
                backgroundColor: typesColors.slice(0, typesLabels.length),
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: '#f0f4ff' }
                },
                x: { grid: { display: false } }
            }
        }
    });
    </script>

<?php require 'app/views/layouts/footer.php'; ?>