<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        login();
        break;
    case 'register':
        register();
        break;
    case 'logout':
        logout();
        break;
}

function login() {
    $email        = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($mot_de_passe)) {
        header('Location: /newsletter_automatique/index.php?page=login&erreur=champs_vides');
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
        header('Location: /newsletter_automatique/index.php?page=login&erreur=identifiants_incorrects');
        exit;
    }

    // Vérifier si le compte est actif
    if (!$user['actif']) {
        // Vérifier le statut de validation
        $statut = $user['validation_statut'] ?? 'accepte';

        if ($statut === 'en_attente') {
            header('Location: /newsletter_automatique/index.php?page=login&erreur=compte_en_attente');
            exit;
        }

        if ($statut === 'refuse') {
            header('Location: /newsletter_automatique/index.php?page=login&erreur=compte_refuse');
            exit;
        }

        // Compte désactivé par l'admin
        header('Location: /newsletter_automatique/index.php?page=login&erreur=compte_desactive');
        exit;
    }

    // Connexion réussie
    $_SESSION['user'] = [
        'id'          => $user['id'],
        'nom'         => $user['nom'],
        'prenom'      => $user['prenom'],
        'email'       => $user['email'],
        'role'        => $user['role'],
        'departement' => $user['departement'],
    ];

    // Redirection selon le rôle
    if ($user['role'] === 'etudiant') {
        header('Location: /newsletter_automatique/index.php?page=etudiant_dashboard');
    } elseif ($user['role'] === 'enseignant') {
        header('Location: /newsletter_automatique/index.php?page=enseignant_dashboard');
    } else {
        header('Location: /newsletter_automatique/index.php?page=dashboard');
    }
    exit;
}

function register() {
    $nom          = trim($_POST['nom'] ?? '');
    $prenom       = trim($_POST['prenom'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role         = $_POST['role'] ?? '';
    $departement  = trim($_POST['departement'] ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($role)) {
        header('Location: /newsletter_automatique/index.php?page=register&erreur=champs_vides');
        exit;
    }

    $roles_valides = ['etudiant', 'enseignant', 'administratif'];
    if (!in_array($role, $roles_valides)) {
        header('Location: /newsletter_automatique/index.php?page=register&erreur=role_invalide');
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: /newsletter_automatique/index.php?page=register&erreur=email_existe');
        exit;
    }

    $hash = password_hash($mot_de_passe, PASSWORD_BCRYPT, ['cost' => 12]);

    // Étudiant → accepté directement et actif
    // Enseignant/Administratif → en attente et inactif
    if ($role === 'etudiant') {
        $validation_statut = 'accepte';
        $actif = 1;
    } else {
        $validation_statut = 'en_attente';
        $actif = 0;
    }

    $stmt = $db->prepare("
        INSERT INTO utilisateurs
        (nom, prenom, email, mot_de_passe, role, departement, actif, abonne_newsletter, validation_statut)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)
    ");
    $stmt->execute([$nom, $prenom, $email, $hash, $role, $departement, $actif, $validation_statut]);

    if ($role === 'etudiant') {
        header('Location: /newsletter_automatique/index.php?page=login&succes=compte_cree');
    } else {
        header('Location: /newsletter_automatique/index.php?page=login&succes=compte_en_attente');
    }
    exit;
}

function logout() {
    session_destroy();
    header('Location: /newsletter_automatique/index.php?page=login');
    exit;
}
?>