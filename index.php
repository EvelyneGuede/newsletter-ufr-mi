<?php
session_start(); 

require_once 'config/security.php'; // ← ajoute cette ligne

$page = $_GET['page'] ?? $_POST['page'] ?? 'login';

switch ($page) {
    case 'login':
        require 'app/views/auth/login.php';
        break;
    case 'register':
        require 'app/views/auth/register.php';
        break;
    case 'choisir_role':
        require 'app/views/auth/choisir_role.php';
        break;
    case 'enregistrer_role':
        require 'app/controllers/GoogleRoleController.php';
        break;
    case 'auth_action':
        require 'app/controllers/AuthController.php';
        break;
    case 'dashboard':
        require 'app/views/admin/dashboard.php';
        break;
    case 'articles':
        require 'app/views/admin/articles.php';
        break;
    case 'soumettre_article':
        require 'app/views/articles/soumettre.php';
        break;
    case 'etudiant_dashboard':
        require 'app/views/etudiant/dashboard.php';
        break;
    case 'enseignant_dashboard':
        require 'app/views/enseignant/dashboard.php';
        break;
    case 'newsletters':
        require 'app/views/admin/newsletters.php';
        break;
    case 'creer_newsletter':
        require 'app/views/admin/creer_newsletter.php';
        break;
    case 'traitement_newsletter':
        require 'app/controllers/NewsletterController.php';
        break;
    case 'apercu_newsletter':
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'administratif') {
            require 'app/views/admin/apercu_newsletter.php';
        } else {
            require 'app/views/shared/voir_newsletter.php';
        }
        break;
    case 'traitement_article':
        require 'app/controllers/ArticleController.php';
        break;
    case 'utilisateurs':
        require 'app/views/admin/utilisateurs.php';
        break;
    case 'demandes':
        require 'app/views/admin/demandes.php';
        break;
    case 'traitement_demande':
        require 'app/controllers/DemandeController.php';
        break;
    case 'traitement_utilisateur':
        require 'app/controllers/UtilisateurController.php';
        break;
    case 'traitement_profil':
        require 'app/controllers/ProfilController.php';
        break;
    case 'archives':
        require 'app/views/shared/archives.php';
        break;
    case 'profil':
        require 'app/views/shared/profil.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
        break; 
    case 'desabonner':
       require 'app/views/shared/desabonner.php'; 
       break; 
    case 'statistiques':
       require 'app/views/admin/statistiques.php';
       break;
    case 'google_auth':
       require 'app/controllers/GoogleAuthController.php';
       break;
    default:
        require 'app/views/auth/login.php';
        break;
}
?>