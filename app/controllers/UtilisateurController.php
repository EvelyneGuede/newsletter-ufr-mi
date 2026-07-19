<?php
session_start();
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierToken($_POST['csrf_token'] ?? '');
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}

$db     = getDB();
$action  = $_POST['action'] ?? '';
$user_id = $_POST['user_id'] ?? 0;

switch ($action) {
    case 'activer':
        $db->prepare("UPDATE utilisateurs SET actif = 1 WHERE id = ?")->execute([$user_id]);
        break;
    case 'desactiver':
        $db->prepare("UPDATE utilisateurs SET actif = 0 WHERE id = ?")->execute([$user_id]);
        break;
    case 'toggle_abonnement':
        $db->prepare("UPDATE utilisateurs SET abonne_newsletter = NOT abonne_newsletter WHERE id = ?")->execute([$user_id]);
        break;
    case 'supprimer_user':
        supprimerUser();
        break;
}

header('Location: index.php?page=utilisateurs&succes=1');
exit;

function supprimerUser() {
    $db      = getDB();
    $user_id = $_POST['user_id'] ?? 0;

    // Ne pas supprimer son propre compte
    if ($user_id == $_SESSION['user']['id']) {
        header('Location: index.php?page=utilisateurs&erreur=impossible');
        exit;
    }

    // Supprimer les articles de l'utilisateur
    $db->prepare("DELETE FROM articles WHERE auteur_id = ?")
       ->execute([$user_id]);

    // Supprimer le compte
    $db->prepare("DELETE FROM utilisateurs WHERE id = ?")
       ->execute([$user_id]);

    header('Location: index.php?page=utilisateurs&succes=supprime');
    exit;
}
?>
