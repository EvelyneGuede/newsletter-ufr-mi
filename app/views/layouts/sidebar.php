<?php
require_once ROOT_PATH . '/config/database.php';
$retour = '';
if (isset($user)) {
    $retour = $user['role'] === 'etudiant'
        ? 'etudiant_dashboard'
        : ($user['role'] === 'enseignant' ? 'enseignant_dashboard' : 'dashboard');
}
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="mi">MI</div>
        <div class="subtitle">Newsletter UFR-MI</div>
    </div>

    <div class="sidebar-menu">
        <?php if ($user['role'] === 'administratif'): ?>
            <div class="menu-label">Navigation</div>
            <a class="menu-item <?= ($page_active === 'dashboard') ? 'active' : '' ?>"
               href="index.php?page=dashboard">
                <span class="icon"></span> Tableau de bord
            </a>
            <a class="menu-item <?= ($page_active === 'articles') ? 'active' : '' ?>"
               href="index.php?page=articles">
                <span class="icon"></span> Articles
            </a>
            <a class="menu-item <?= ($page_active === 'newsletters') ? 'active' : '' ?>"
               href="index.php?page=newsletters">
                <span class="icon"></span> Newsletters
            </a>
            <a class="menu-item <?= ($page_active === 'utilisateurs') ? 'active' : '' ?>"
               href="index.php?page=utilisateurs">
                <span class="icon"></span> Utilisateurs
            </a>
            <?php
            $nb_dem = 0;
            try {
                $db_nav = getDB();
                $nb_dem = (int) $db_nav->query("SELECT COUNT(*) FROM utilisateurs WHERE validation_statut = 'en_attente' AND role != 'etudiant' AND actif = 0")->fetchColumn();
            } catch (PDOException $e) {
            }
            ?>
            <a class="menu-item <?= ($page_active === 'demandes') ? 'active' : '' ?>"
               href="index.php?page=demandes">
                <span class="icon"></span> Demandes
                <?php if ($nb_dem > 0): ?>
                    <span style="background:#f39c12; color:#fff; font-size:10px; font-weight:700;
                                 padding:2px 7px; border-radius:20px; margin-left:auto;">
                        <?= $nb_dem ?>
                    </span>
                <?php endif; ?>
            </a>
            <a class="menu-item <?= ($page_active === 'statistiques') ? 'active' : '' ?>"
               href="index.php?page=statistiques">
                <span class="icon"></span> Statistiques
            </a>
            <a class="menu-item <?= ($page_active === 'soumettre_article') ? 'active' : '' ?>"
               href="index.php?page=soumettre_article">
                <span class="icon"></span> Soumettre un article
            </a>

        <?php else: ?>
            <div class="menu-label">Navigation</div>
            <a class="menu-item <?= ($page_active === $retour) ? 'active' : '' ?>"
               href="index.php?page=<?= $retour ?>">
                <span class="icon"></span> Mon espace
            </a>
            <a class="menu-item <?= ($page_active === 'soumettre_article') ? 'active' : '' ?>"
               href="index.php?page=soumettre_article">
                <span class="icon"></span> Soumettre un article
            </a>
            <a class="menu-item <?= ($page_active === 'archives') ? 'active' : '' ?>"
               href="index.php?page=archives">
                <span class="icon"></span> Archives
            </a>
            <a class="menu-item <?= ($page_active === 'profil') ? 'active' : '' ?>"
               href="index.php?page=profil">
                <span class="icon"></span> Mon profil
            </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
            <div class="user-avatar">
                <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
            </div>
            <div>
                <div class="user-name">
                    <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                </div>
                <div class="user-role"><?= $user['role'] ?></div>
            </div>
        </div>
        <a href="index.php?page=logout" class="btn-logout">Se déconnecter</a>
    </div>
</div>