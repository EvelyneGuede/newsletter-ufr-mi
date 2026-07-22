<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifierToken($_POST['csrf_token'] ?? '');
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;

$user   = $_SESSION['user'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'creer':
        creer();
        break;
    case 'envoyer':
        envoyer();
        break;
    case 'supprimer_newsletter':
        supprimerNewsletter();
        break;
}

function creer() {
    global $user;
    $db           = getDB();
    $titre        = trim($_POST['titre'] ?? '');
    $date_planif  = $_POST['date_planifiee'] ?: null;
    $articles_ids = $_POST['articles'] ?? [];

    if (empty($titre) || empty($articles_ids)) {
        header('Location: index.php?page=creer_newsletter&erreur=champs_vides');
        exit;
    }

    $template      = genererTemplate($db, $titre, $articles_ids);

    $stmt = $db->prepare("
        INSERT INTO newsletters (titre, statut, template_html, date_planifiee, created_by)
        VALUES (?, 'brouillon', ?, ?, ?)
    ");
    $stmt->execute([$titre, $template, $date_planif, $user['id']]);
    $newsletter_id = $db->lastInsertId();

    $stmt = $db->prepare("INSERT INTO newsletter_articles (newsletter_id, article_id, ordre) VALUES (?, ?, ?)");
    foreach ($articles_ids as $ordre => $article_id) {
        $stmt->execute([$newsletter_id, $article_id, $ordre]);
        $db->prepare("UPDATE articles SET statut = 'publie' WHERE id = ?")->execute([$article_id]);
    }

    header("Location: index.php?page=apercu_newsletter&id=$newsletter_id&succes=cree");
    exit;
}

function genererTemplate($db, $titre, $articles_ids) {
    $articles = [];
    foreach ($articles_ids as $id) {
        $stmt = $db->prepare("
            SELECT a.*, u.nom, u.prenom
            FROM articles a
            LEFT JOIN utilisateurs u ON a.auteur_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $articles[] = $stmt->fetch();
    }

    $articles_html = '';
    foreach ($articles as $article) {
        $type_colors = [
            'cours'     => '#2196f3',
            'evenement' => '#e74c3c',
            'annonce'   => '#f39c12',
            'resultat'  => '#27ae60',
            'club'      => '#9b59b6',
        ];
        $color = $type_colors[$article['type']] ?? '#2929cc';

        // Image si présente
        $image_html = '';
        if (!empty($article['piece_jointe'])) {
            $image_url = APP_URL . '/public/uploads/' . htmlspecialchars($article['piece_jointe']);
            $image_html = "
            <div style='margin-top:16px;'>
                 <img src='$image_url'
                     style='max-width:100%; border-radius:10px;'
                     alt='Image de l article'>
            </div>";
        }

        $articles_html .= "
        <div style='background:#fff; border-radius:12px; padding:24px; margin-bottom:20px; border-left:4px solid $color;'>
            <div style='display:inline-block; background:$color; color:#fff; font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; margin-bottom:10px; text-transform:uppercase;'>
                " . htmlspecialchars($article['type']) . "
            </div>
            <h2 style='font-size:18px; font-weight:700; color:#1515b5; margin:0 0 8px;'>
                " . htmlspecialchars($article['titre']) . "
            </h2>
            <p style='font-size:13px; color:#6c757d; margin:0 0 12px;'>
                 " . htmlspecialchars($article['prenom'] . ' ' . $article['nom']) . "
            </p>
            <p style='font-size:14px; color:#444; line-height:1.7; margin:0;'>
                " . nl2br(htmlspecialchars($article['contenu'])) . "
            </p>
            $image_html
        </div>";
    }

    $date  = date('d/m/Y');
    $annee = date('Y');
    $logo_url = APP_URL . '/app/views/admin/logomi.jpeg';

    return "<!DOCTYPE html>
<html lang='fr'>
<head><meta charset='UTF-8'><title>$titre</title></head>
<body style='margin:0; padding:0; background:#f0f0ff; font-family:Arial,sans-serif;'>
    <div style='max-width:680px; margin:30px auto;'>

        <!-- En-tête -->
        <div style='background:linear-gradient(135deg,#1515b5,#2929cc); border-radius:16px 16px 0 0; padding:36px 32px; text-align:center;'>
            <div style='display:flex; justify-content:center; align-items:center; gap:14px; flex-wrap:wrap; margin-bottom:10px;'>
                <div style='font-size:64px; font-weight:900; color:#5555ff; letter-spacing:-2px; line-height:1;'>MI</div>
                <img src='$logo_url' alt='Logo du département' style='width:78px; height:auto; max-height:70px; object-fit:contain; border-radius:10px; background:#fff; padding:6px; border:1px solid rgba(255,255,255,0.3);'>
            </div>
            <div style='color:#e0e0ff; font-size:12px; letter-spacing:2px; text-transform:uppercase; margin-top:8px;'>
                UFR Mathématique-Informatique
            </div>
            <h1 style='color:#fff; font-size:22px; font-weight:700; margin:16px 0 0;'>$titre</h1>
            <div style='color:#b0b0ff; font-size:13px; margin-top:8px;'>Édition du $date</div>
        </div>

        <!-- Corps -->
        <div style='background:#f5f5ff; padding:28px 32px;'>
            $articles_html
        </div>

        <!-- Pied de page -->
        <div style='background:#1515b5; border-radius:0 0 16px 16px; padding:24px 32px; text-align:center;'>
            <p style='color:#b0b0ff; font-size:12px; margin:0;'>
                © $annee UFR-MI — Université Félix Houphouët-Boigny, Abidjan
            </p>
            <p style='margin:16px 0 0;'>
                <a href='" . APP_URL . "/index.php?page=desabonner&email={EMAIL}'
                   style='background:#4a90d9; color:#fff; padding:8px 24px; border-radius:20px;
                          font-size:12px; font-weight:600; text-decoration:none;
                          display:inline-block;'>
                    Se désabonner
                </a>
            </p>
            <p style='color:#8888ff; font-size:11px; margin:12px 0 0;'>
                Vous recevez cet email car vous êtes membre de l'UFR-MI.
            </p>
        </div>

    </div>
</body>
</html>";
}

function envoyer() {
    $newsletter_id = $_POST['newsletter_id'] ?? 0;
    $db = getDB();

    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (!file_exists($autoload)) {
        header('Location: index.php?page=apercu_newsletter&id=' . $newsletter_id . '&erreur=composer_manquant');
        exit;
    }

    require $autoload;

    $stmt = $db->prepare("SELECT * FROM newsletters WHERE id = ?");
    $stmt->execute([$newsletter_id]);
    $newsletter = $stmt->fetch();

    $destinataires = $db->query("
        SELECT * FROM utilisateurs
        WHERE actif = 1
        ORDER BY id
    ")->fetchAll();

    $nb_envoyes = 0;
    $nb_echecs = 0;

    foreach ($destinataires as $dest) {
        $mail = new PHPMailer(true);
        try {
            if (empty(MAIL_USERNAME) || empty(MAIL_PASSWORD)) {
                throw new Exception('Identifiants SMTP manquants.');
            }

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_USERNAME, 'Newsletter UFR-MI');
            $mail->addAddress($dest['email'], $dest['prenom'] . ' ' . $dest['nom']);
            $mail->isHTML(true);
            $mail->Subject = $newsletter['titre'];

            // Personnaliser le lien désabonnement avec l'email du destinataire
            $body = str_replace(
                '{EMAIL}',
                urlencode($dest['email']),
                $newsletter['template_html']
            );
            $mail->Body = $body;

            $mail->send();
            $nb_envoyes++;

            // Traçabilité - utiliser prepared statement
            $db->prepare("INSERT IGNORE INTO destinataires (email, nom, role) VALUES (?, ?, ?)")
               ->execute([$dest['email'], $dest['nom'], $dest['role']]);
            
            // ✅ FIX: Utiliser un prepared statement pour récupérer l'ID
            $stmt_dest = $db->prepare("SELECT id FROM destinataires WHERE email = ?");
            $stmt_dest->execute([$dest['email']]);
            $dest_id = $stmt_dest->fetchColumn();
            
            $db->prepare("INSERT INTO envois (newsletter_id, destinataire_id) VALUES (?, ?)")
               ->execute([$newsletter_id, $dest_id]);

        } catch (Exception $e) {
            $nb_echecs++;
            error_log("Erreur envoi email: " . $e->getMessage());
        }
    }

    $db->prepare("
        UPDATE newsletters
        SET statut = 'envoyee', date_envoi = NOW(), nb_destinataires = ?
        WHERE id = ?
    ")->execute([$nb_envoyes, $newsletter_id]);

    header("Location: index.php?page=apercu_newsletter&id=$newsletter_id&succes=envoye&nb=$nb_envoyes&echecs=$nb_echecs");
    exit;
}

function supprimerNewsletter() {
     $db            = getDB();
     $newsletter_id = $_POST['newsletter_id'] ?? 0;

     // Supprimer les liaisons
     $db->prepare("DELETE FROM newsletter_articles WHERE newsletter_id = ?")
         ->execute([$newsletter_id]);

     // Supprimer les envois
     $db->prepare("DELETE FROM envois WHERE newsletter_id = ?")
         ->execute([$newsletter_id]);

     // Supprimer la newsletter
     $db->prepare("DELETE FROM newsletters WHERE id = ?")
         ->execute([$newsletter_id]);

     header('Location: index.php?page=newsletters&succes=supprime');
     exit;
}
?>
