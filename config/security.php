<?php
// ══ PROTECTION CONTRE LES ATTAQUES ══

// Définir APP_DEBUG à partir de la variable d'environnement si non défini.
if (!defined('APP_DEBUG')) {
    $envDebug = getenv('APP_DEBUG');
    if ($envDebug !== false) {
        $envDebug = strtolower($envDebug);
        define('APP_DEBUG', in_array($envDebug, ['1', 'true', 'yes'], true));
    } else {
        define('APP_DEBUG', false);
    }
}

// 1. En-têtes de sécurité
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// 2. Fonction nettoyage des entrées
function nettoyer($data) {
    if (is_array($data)) {
        return array_map('nettoyer', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// 3. Vérification du token CSRF
function genererToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifierToken($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die(' Token CSRF invalide. Accès refusé.');
    }
}

// 4. Vérifier si l'utilisateur est connecté
function verifierConnexion() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?page=login');
        exit;
    }
}

// 5. Vérifier le rôle
function verifierRole($role_requis) {
    verifierConnexion();
    if ($_SESSION['user']['role'] !== $role_requis) {
        header('Location: index.php?page=login&erreur=acces_refuse');
        exit;
    }
}

// 6. Limiter les tentatives de connexion
function verifierTentatives($email) {
    $fichier = sys_get_temp_dir() . '/tentatives_' . md5($email) . '.json';
    $max     = 5;
    $delai   = 15 * 60; // 15 minutes

    $data = ['tentatives' => 0, 'derniere' => time()];

    if (file_exists($fichier)) {
        $data = json_decode(file_get_contents($fichier), true);
        // Réinitialiser après 15 minutes
        if (time() - $data['derniere'] > $delai) {
            $data = ['tentatives' => 0, 'derniere' => time()];
        }
    }

    if ($data['tentatives'] >= $max) {
        $restant = ceil(($data['derniere'] + $delai - time()) / 60);
        die(" Trop de tentatives. Réessayez dans $restant minute(s).");
    }

    return $data;
}

function enregistrerTentative($email, $succes = false) {
    $fichier = sys_get_temp_dir() . '/tentatives_' . md5($email) . '.json';

    if ($succes) {
        // Supprimer le fichier en cas de succès
        if (file_exists($fichier)) unlink($fichier);
        return;
    }

    $data = ['tentatives' => 0, 'derniere' => time()];
    if (file_exists($fichier)) {
        $data = json_decode(file_get_contents($fichier), true);
    }

    $data['tentatives']++;
    $data['derniere'] = time();
    file_put_contents($fichier, json_encode($data));
}
?>