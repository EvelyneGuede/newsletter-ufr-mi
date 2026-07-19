<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once 'config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

$utilisateurs = $db->query("
    SELECT *, (SELECT COUNT(*) FROM articles WHERE auteur_id = utilisateurs.id) as nb_articles
    FROM utilisateurs
    ORDER BY created_at DESC
")->fetchAll();
?>
<?php
$titre_page = 'Utilisateurs';
$page_active = 'utilisateurs';
require 'app/views/layouts/header.php';
?>
    <style>
        .search-bar { background: #fff; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); display: flex; gap: 12px; }
        .search-bar input { flex: 1; border: 1.5px solid #e0e0ff; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; }
        .search-bar input:focus { border-color: #2929cc; }
        .search-bar select { border: 1.5px solid #e0e0ff; border-radius: 8px; padding: 10px 14px; font-size: 14px; outline: none; color: #444; }
        .table-card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; padding: 10px 14px; border-bottom: 2px solid #f0f0ff; text-align: left; }
        td { font-size: 14px; padding: 14px; border-bottom: 1px solid #f0f0ff; vertical-align: middle; }
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: #5555ff; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1515b5; font-size: 14px; flex-shrink: 0; }
        .avatar.enseignant { background: #bbdefb; color: #1565c0; }
        .avatar.etudiant   { background: #f8bbd0; color: #880e4f; }
        .avatar.administratif { background: #c8e6c9; color: #1b5e20; }
        .user-fullname { font-weight: 600; color: #1515b5; font-size: 14px; }
        .user-email { font-size: 12px; color: #6c757d; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-enseignant    { background: #bbdefb; color: #1565c0; }
        .badge-etudiant      { background: #f8bbd0; color: #880e4f; }
        .badge-administratif { background: #c8e6c9; color: #1b5e20; }
        .badge-actif   { background: #d1e7dd; color: #0f5132; }
        .badge-inactif { background: #f8d7da; color: #842029; }
        .btn-action { border: none; border-radius: 6px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn-activer   { background: #d1e7dd; color: #0f5132; }
        .btn-desactiver { background: #f8d7da; color: #842029; }
        .btn-action:hover { opacity: 0.8; }
    </style>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1> Gestion des utilisateurs</h1>
            <p><?= count($utilisateurs) ?> utilisateur(s) enregistré(s)</p>
        </div>

        <!-- Barre de recherche -->
        <div class="search-bar">
            <input type="text" id="recherche" placeholder="🔍 Rechercher par nom, prénom ou email..." oninput="filtrer()">
            <select id="filtreRole" onchange="filtrer()">
                <option value="">Tous les rôles</option>
                <option value="etudiant">Étudiant</option>
                <option value="enseignant">Enseignant</option>
                <option value="administratif">Administratif</option>
            </select>
        </div>

        <div class="table-card">
            <table id="tableUsers">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Département</th>
                        <th>Articles</th>
                        <th>Newsletter</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr class="user-row"
                        data-nom="<?= strtolower($u['nom'] . ' ' . $u['prenom'] . ' ' . $u['email']) ?>"
                        data-role="<?= $u['role'] ?>">
                        <td>
                            <div class="user-cell">
                                <div class="avatar <?= $u['role'] ?>">
                                    <?= strtoupper(substr($u['prenom'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="user-fullname"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></div>
                                    <div class="user-email"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= htmlspecialchars($u['departement'] ?? '—') ?></td>
                        <td style="text-align:center; font-weight:600; color:#1515b5;"><?= $u['nb_articles'] ?></td>
                        <td style="text-align:center;">
                            <?= $u['abonne_newsletter'] ? '' : '❌' ?>
                        </td>
                        <td>
                            <span class="badge <?= $u['actif'] ? 'badge-actif' : 'badge-inactif' ?>">
                                <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <form action="index.php" method="POST">
                                    <input type="hidden" name="page" value="traitement_utilisateur">
                                    <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <?php if ($u['actif']): ?>
                                        <input type="hidden" name="action" value="desactiver">
                                        <button type="submit" class="btn-action btn-desactiver"
                                                onclick="return confirm('Désactiver cet utilisateur ?')">
                                            Désactiver
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="activer">
                                        <button type="submit" class="btn-action btn-activer">
                                            Activer
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <form action="index.php" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="page" value="traitement_utilisateur">
                                    <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                                    <input type="hidden" name="action" value="supprimer_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn-action" style="background:#fff;border:1px solid #f5c6cb;border-radius:6px;padding:6px 10px;">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function filtrer() {
        const recherche = document.getElementById('recherche').value.toLowerCase();
        const role      = document.getElementById('filtreRole').value;
        document.querySelectorAll('.user-row').forEach(row => {
            const matchNom  = row.dataset.nom.includes(recherche);
            const matchRole = role === '' || row.dataset.role === role;
            row.style.display = (matchNom && matchRole) ? '' : 'none';
        });
    }
    </script>
<?php require 'app/views/layouts/footer.php'; ?>