<?php
session_start();

define('ROOT_PATH', __DIR__);
define('VIEWS_PATH', ROOT_PATH . '/app/views');

require_once ROOT_PATH . '/config/database.php';

$page = $_GET['page'] ?? $_POST['page'] ?? 'login';

switch ($page) {
    case 'login':
        require VIEWS_PATH . '/auth/login.php';
        break;
    case 'register':
        require VIEWS_PATH . '/auth/register.php';
        break;
    case 'choisir_role':
        require VIEWS_PATH . '/auth/choisir_role.php';
        break;
    case 'dashboard':
        require VIEWS_PATH . '/admin/dashboard.php';
        break;
    case 'articles':
        require VIEWS_PATH . '/admin/articles.php';
        break;
    case 'newsletters':
        require VIEWS_PATH . '/admin/newsletters.php';
        break;
    case 'creer_newsletter':
        require VIEWS_PATH . '/admin/creer_newsletter.php';
        break;
    case 'apercu_newsletter':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'administratif') {
            require VIEWS_PATH . '/admin/apercu_newsletter.php';
        } else {
            require VIEWS_PATH . '/shared/voir_newsletter.php';
        }
        break;
    case 'utilisateurs':
        require VIEWS_PATH . '/admin/utilisateurs.php';
        break;
    case 'statistiques':
        require VIEWS_PATH . '/admin/statistiques.php';
        break;
    case 'demandes':
        require VIEWS_PATH . '/admin/demandes.php';
        break;
    case 'soumettre_article':
        require VIEWS_PATH . '/articles/soumettre.php';
        break;
    case 'etudiant_dashboard':
        require VIEWS_PATH . '/etudiant/dashboard.php';
        break;
    case 'enseignant_dashboard':
        require VIEWS_PATH . '/enseignant/dashboard.php';
        break;
    case 'archives':
        require VIEWS_PATH . '/shared/archives.php';
        break;
    case 'profil':
        require VIEWS_PATH . '/shared/profil.php';
        break;
    case 'voir_newsletter':
        require VIEWS_PATH . '/shared/voir_newsletter.php';
        break;
    case 'desabonner':
        require VIEWS_PATH . '/shared/desabonner.php';
        break;
    case 'google_auth':
        require ROOT_PATH . '/app/controllers/GoogleAuthController.php';
        break;
    case 'enregistrer_role':
        require ROOT_PATH . '/app/controllers/GoogleRoleController.php';
        break;
    case 'auth_action':
    case 'traitement_login':
    case 'traitement_register':
        require ROOT_PATH . '/app/controllers/AuthController.php';
        break;
    case 'traitement_article':
        require ROOT_PATH . '/app/controllers/ArticleController.php';
        break;
    case 'traitement_newsletter':
        require ROOT_PATH . '/app/controllers/NewsletterController.php';
        break;
    case 'traitement_utilisateur':
        require ROOT_PATH . '/app/controllers/UtilisateurController.php';
        break;
    case 'traitement_profil':
        require ROOT_PATH . '/app/controllers/ProfilController.php';
        break;
    case 'traitement_demande':
        require ROOT_PATH . '/app/controllers/DemandeController.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    default:
        require VIEWS_PATH . '/auth/login.php';
        break;
}