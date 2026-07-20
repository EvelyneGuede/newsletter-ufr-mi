<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: /newsletter_automatique/index.php?page=login');
    exit;
}

$db      = getDB();
$action  = $_POST['action'] ?? '';
$user_id = $_POST['user_id'] ?? 0;

// Récupérer les infos de l'utilisateur concerné
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$destinataire = $stmt->fetch();

if (!$destinataire) {
    header('Location: /newsletter_automatique/index.php?page=demandes&erreur=utilisateur_introuvable');
    exit;
}

switch ($action) {

    case 'accepter':
        // Mettre à jour en base
        $db->prepare(" 
            UPDATE utilisateurs
            SET validation_statut = 'accepte',
                actif = 1,
                date_validation_compte = NOW()
            WHERE id = ?
        ")->execute([$user_id]);

        // Envoyer email d'acceptation
        envoyerEmail(
            $destinataire['email'],
            $destinataire['prenom'] . ' ' . $destinataire['nom'],
            'acceptation',
            $destinataire['role'],
            ''
        );

        header('Location: /newsletter_automatique/index.php?page=demandes&succes=accepte');
        break;

    case 'refuser':
        $motif = trim($_POST['motif_refus'] ?? '');
        if (empty($motif)) {
            header('Location: /newsletter_automatique/index.php?page=demandes&erreur=motif_requis');
            exit;
        }

        // Mettre à jour en base
        $db->prepare(" 
            UPDATE utilisateurs
            SET validation_statut = 'refuse',
                actif = 0,
                motif_refus = ?,
                date_validation_compte = NOW()
            WHERE id = ?
        ")->execute([$motif, $user_id]);

        // Envoyer email de refus
        envoyerEmail(
            $destinataire['email'],
            $destinataire['prenom'] . ' ' . $destinataire['nom'],
            'refus',
            $destinataire['role'],
            $motif
        );

        header('Location: /newsletter_automatique/index.php?page=demandes&succes=refuse');
        break;
}
exit;

// ══ FONCTION D'ENVOI EMAIL ══
function envoyerEmail($email, $nom, $type, $role, $motif = '') {

    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP - ✅ FIX: Utiliser les credentials depuis .env
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, 'Administration UFR-MI');
        $mail->addAddress($email, $nom);
        $mail->isHTML(true);

        if ($type === 'acceptation') {
            $mail->Subject = 'Votre compte UFR-MI a été accepté';
            $mail->Body    = templateAcceptation($nom, $role);
        } else {
            $mail->Subject = 'Votre demande de compte UFR-MI a été refusée';
            $mail->Body    = templateRefus($nom, $role, $motif);
        }

        $mail->send();

    } catch (Exception $e) {
        // Email non envoyé mais on continue
        error_log("Email non envoyé : " . $e->getMessage());
    }
}

// ══ TEMPLATE EMAIL ACCEPTATION ══
function templateAcceptation($nom, $role) {
    $role_label = ucfirst($role);
    $url = APP_URL;
    $annee = date('Y');

    return "<!DOCTYPE html>
<html lang='fr'>
<head><meta charset='UTF-8'><title>Compte accepté</title></head>
<body style='margin:0; padding:0; background:#f0f0ff; font-family:Arial,sans-serif;'>
<div style='max-width:600px; margin:30px auto;'>

    <!-- En-tête -->
    <div style='background:linear-gradient(135deg,#1515b5,#2929cc); border-radius:16px 16px 0 0; padding:32px; text-align:center;'>
        <div style='font-size:56px; font-weight:900; color:#5555ff; line-height:1;'>MI</div>
        <div style='color:#e0e0ff; font-size:11px; letter-spacing:2px; text-transform:uppercase; margin-top:6px;'>
            UFR Mathématique-Informatique
        </div>
        <div style='color:#b0b0ff; font-size:11px; margin-top:4px;'>
            Université Félix Houphouët-Boigny
        </div>
    </div>

    <!-- Corps -->
    <div style='background:#fff; padding:32px;'>

        <!-- Icône succès -->
        <div style='text-align:center; margin-bottom:24px;'>
            <div style='display:inline-block; background:#d1e7dd; border-radius:50%; width:70px; height:70px; line-height:70px; font-size:36px;'>
                ✅
            </div>
        </div>

        <h2 style='color:#1515b5; font-size:22px; font-weight:700; margin:0 0 16px; text-align:center;'>
            Félicitations, $nom !
        </h2>

        <p style='color:#444; font-size:14px; line-height:1.7; margin:0 0 16px;'>
            Nous avons le plaisir de vous informer que votre demande de compte
            <strong>$role_label</strong> sur la plateforme Newsletter UFR-MI
            a été <strong style='color:#0f5132;'>acceptée</strong> par l'administration.
        </p>

        <p style='color:#444; font-size:14px; line-height:1.7; margin:0 0 24px;'>
            Vous pouvez désormais vous connecter et accéder à toutes les fonctionnalités
            de votre espace.
        </p>

        <!-- Bouton connexion -->
        <div style='text-align:center; margin:28px 0;'>
            <a href='$url'
               style='background:linear-gradient(135deg,#1515b5,#2929cc); color:#fff;
                      text-decoration:none; padding:14px 36px; border-radius:8px;
                      font-size:15px; font-weight:700; display:inline-block;'>
                Se connecter maintenant →
            </a>
        </div>

        <!-- Infos compte -->
        <div style='background:#f0f0ff; border-radius:10px; padding:18px; margin-top:24px;'>
            <div style='font-size:13px; font-weight:700; color:#1515b5; margin-bottom:10px;'>
                Votre espace $role_label vous permet de :
            </div>
            " . droitsRole($role) . "
        </div>

        <p style='color:#6c757d; font-size:12px; margin-top:24px; line-height:1.6;'>
            Si vous n'êtes pas à l'origine de cette demande ou si vous avez des questions,
            veuillez contacter l'administration de l'UFR-MI.
        </p>
    </div>

    <!-- Pied de page -->
    <div style='background:#1515b5; border-radius:0 0 16px 16px; padding:20px; text-align:center;'>
        <p style='color:#b0b0ff; font-size:11px; margin:0;'>
            © $annee UFR-MI — Université Félix Houphouët-Boigny, Abidjan
        </p>
        <p style='color:#8888ff; font-size:10px; margin:6px 0 0;'>
            Cet email a été envoyé automatiquement par la plateforme Newsletter UFR-MI.
        </p>
    </div>

</div>
</body>
</html>";
}

// ══ TEMPLATE EMAIL REFUS ══
function templateRefus($nom, $role, $motif) {
    $role_label = ucfirst($role);
    $annee = date('Y');

    return "<!DOCTYPE html>
<html lang='fr'>
<head><meta charset='UTF-8'><title>Demande refusée</title></head>
<body style='margin:0; padding:0; background:#f0f0ff; font-family:Arial,sans-serif;'>
<div style='max-width:600px; margin:30px auto;'>

    <!-- En-tête -->
    <div style='background:linear-gradient(135deg,#1515b5,#2929cc); border-radius:16px 16px 0 0; padding:32px; text-align:center;'>
        <div style='font-size:56px; font-weight:900; color:#5555ff; line-height:1;'>MI</div>
        <div style='color:#e0e0ff; font-size:11px; letter-spacing:2px; text-transform:uppercase; margin-top:6px;'>
            UFR Mathématique-Informatique
        </div>
        <div style='color:#b0b0ff; font-size:11px; margin-top:4px;'>
            Université Félix Houphouët-Boigny
        </div>
    </div>

    <!-- Corps -->
    <div style='background:#fff; padding:32px;'>

        <!-- Icône refus -->
        <div style='text-align:center; margin-bottom:24px;'>
            <div style='display:inline-block; background:#f8d7da; border-radius:50%; width:70px; height:70px; line-height:70px; font-size:36px;'>
                ❌
            </div>
        </div>

        <h2 style='color:#842029; font-size:20px; font-weight:700; margin:0 0 16px; text-align:center;'>
            Demande non approuvée
        </h2>

        <p style='color:#444; font-size:14px; line-height:1.7; margin:0 0 16px;'>
            Bonjour <strong>$nom</strong>,
        </p>

        <p style='color:#444; font-size:14px; line-height:1.7; margin:0 0 16px;'>
            Après examen de votre demande de compte <strong>$role_label</strong>
            sur la plateforme Newsletter UFR-MI, l'administration n'a pas pu
            approuver votre demande.
        </p>

        <!-- Motif du refus -->
        <div style='background:#fff5f5; border:1px solid #f5c2c7; border-radius:10px;
                    padding:18px; margin:20px 0; border-left:4px solid #842029;'>
            <div style='font-size:13px; font-weight:700; color:#842029; margin-bottom:8px;'>
                Motif du refus :
            </div>
            <p style='color:#444; font-size:13px; margin:0; line-height:1.6;'>
                " . htmlspecialchars($motif) . "
            </p>
        </div>

        <p style='color:#444; font-size:14px; line-height:1.7; margin:16px 0;'>
            Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez fournir
            des informations supplémentaires, veuillez contacter directement
            l'administration de l'UFR-MI.
        </p>

        <!-- Contact -->
        <div style='background:#f0f0ff; border-radius:10px; padding:18px; margin-top:20px;'>
            <div style='font-size:13px; font-weight:700; color:#1515b5; margin-bottom:8px;'>
                Contactez l'administration :
            </div>
            <p style='color:#444; font-size:13px; margin:0;'>
                📍 UFR Mathématique-Informatique<br>
                🏛️ Université Félix Houphouët-Boigny, Abidjan<br>
                📧 admin@ufr-mi.ci
            </p>
        </div>
    </div>

    <!-- Pied de page -->
    <div style='background:#1515b5; border-radius:0 0 16px 16px; padding:20px; text-align:center;'>
        <p style='color:#b0b0ff; font-size:11px; margin:0;'>
            © $annee UFR-MI — Université Félix Houphouët-Boigny, Abidjan
        </p>
        <p style='color:#8888ff; font-size:10px; margin:6px 0 0;'>
            Cet email a été envoyé automatiquement par la plateforme Newsletter UFR-MI.
        </p>
    </div>

</div>
</body>
</html>";
}

// ══ DROITS SELON LE RÔLE ══
function droitsRole($role) {
    if ($role === 'enseignant') {
        return "
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Soumettre des articles de tous types</p>
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Publication directe sans validation</p>
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Consulter les archives des newsletters</p>
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Modifier votre profil</p>
        ";
    } else {
        return "
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Valider ou rejeter les articles</p>
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Créer et envoyer les newsletters</p>
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Gérer tous les utilisateurs</p>
            <p style='color:#444; font-size:12px; margin:4px 0;'>✅ Consulter les statistiques</p>
        ";
    }
}
?>
