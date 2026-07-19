<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$client_id     = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri  = APP_URL . '/index.php?page=google_auth';

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setScopes(['email', 'profile']);

if (isset($_GET['code'])) {

    try {
        $client->authenticate($_GET['code']);
        $token = $client->getAccessToken();

        if (!$token) {
            die('❌ Impossible d\'obtenir le token Google.');
        }

        $oauth2      = new Google_Service_Oauth2($client);
        $google_user = $oauth2->userinfo->get();

        $email  = $google_user->email;
        $prenom = $google_user->givenName  ?? 'Utilisateur';
        $nom    = $google_user->familyName ?? 'Google';

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = [
                'id'          => $user['id'],
                'nom'         => $user['nom'],
                'prenom'      => $user['prenom'],
                'email'       => $user['email'],
                'role'        => $user['role'],
                'departement' => $user['departement'],
            ];

            $page = $user['role'] === 'etudiant'
                ? 'etudiant_dashboard'
                : ($user['role'] === 'enseignant' ? 'enseignant_dashboard' : 'dashboard');

            header("Location: index.php?page=" . $page);
            exit;

        } else {
            $_SESSION['google_user'] = [
                'nom'    => $nom,
                'prenom' => $prenom,
                'email'  => $email,
            ];
            header("Location: index.php?page=choisir_role");
            exit;
        }

    } catch (Exception $e) {
        die('❌ Erreur Google : ' . $e->getMessage());
    }

} else {
    $auth_url = $client->createAuthUrl();
    header("Location: " . $auth_url);
    exit;
}
?>
