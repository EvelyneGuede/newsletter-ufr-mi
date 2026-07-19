<?php
session_start();
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierToken($_POST['csrf_token'] ?? '');
}

if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

$db     = getDB();
$user   = $_SESSION['user'];
$action = $_POST['action'] ?? '';

if ($action === 'modifier') {
    $prenom      = trim($_POST['prenom'] ?? '');
    $nom         = trim($_POST['nom'] ?? '');
    $departement = trim($_POST['departement'] ?? '');
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirmer   = $_POST['confirmer_mdp'] ?? '';

    if (!empty($nouveau_mdp)) {
        if ($nouveau_mdp !== $confirmer) {
            header('Location: index.php?page=profil&erreur=mdp_different');
            exit;
        }
        $hash = password_hash($nouveau_mdp, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE utilisateurs SET prenom=?, nom=?, departement=?, mot_de_passe=? WHERE id=?")
           ->execute([$prenom, $nom, $departement, $hash, $user['id']]);
    } else {
        $db->prepare("UPDATE utilisateurs SET prenom=?, nom=?, departement=? WHERE id=?")
           ->execute([$prenom, $nom, $departement, $user['id']]);
    }

    // Mettre à jour la session
    $_SESSION['user']['prenom']      = $prenom;
    $_SESSION['user']['nom']         = $nom;
    $_SESSION['user']['departement'] = $departement;

    header('Location: index.php?page=profil&succes=1');
    exit;
}
?>
