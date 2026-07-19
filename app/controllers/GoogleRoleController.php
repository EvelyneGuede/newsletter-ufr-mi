<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['google_user'])) {
    header('Location: index.php?page=login');
    exit;
}

$google_user = $_SESSION['google_user'];
$role        = $_POST['role'] ?? '';
$departement = trim($_POST['departement'] ?? '');

$roles_valides = ['etudiant', 'enseignant', 'administratif'];

if (!in_array($role, $roles_valides)) {
    header('Location: index.php?page=choisir_role&erreur=role_invalide');
    exit;
}

$db   = getDB();
$hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

// Créer le compte
$stmt = $db->prepare(" 
    INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, departement, actif, abonne_newsletter)
    VALUES (?, ?, ?, ?, ?, ?, 1, 1)
");
$stmt->execute([
    $google_user['nom'],
    $google_user['prenom'],
    $google_user['email'],
    $hash,
    $role,
    $departement
]);
$new_id = $db->lastInsertId();

// Supprimer les données temporaires Google
unset($_SESSION['google_user']);

if ($role === 'etudiant') {
    $_SESSION['user'] = [
        'id'          => $new_id,
        'nom'         => $google_user['nom'],
        'prenom'      => $google_user['prenom'],
        'email'       => $google_user['email'],
        'role'        => $role,
        'departement' => $departement,
    ];

    header('Location: index.php?page=etudiant_dashboard');
    exit;
}

header('Location: index.php?page=login&succes=compte_en_attente');
exit;
?>
