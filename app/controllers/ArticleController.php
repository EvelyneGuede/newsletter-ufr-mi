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

$user   = $_SESSION['user'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'soumettre':
        soumettre();
        break;
    case 'valider':
        valider();
        break;
    case 'rejeter':
        rejeter();
        break;
    case 'supprimer':
        supprimer();
        break;
}

function soumettre() {
    global $user;
    $db           = getDB();
    $titre        = trim($_POST['titre'] ?? '');
    $contenu      = trim($_POST['contenu'] ?? '');
    $type         = $_POST['type'] ?? '';
    $categorie    = $_POST['categorie_id'] ?: null;
    $piece_jointe = null;

    if (empty($titre) || empty($contenu) || empty($type)) {
        header('Location: index.php?page=soumettre_article&erreur=champs_vides');
        exit;
    }

    // Upload fichier
    if (!empty($_FILES['piece_jointe']['name'])) {
        $upload_dir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $ext      = pathinfo($_FILES['piece_jointe']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('article_') . '.' . $ext;
        $allowed  = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array(strtolower($ext), $allowed) && $_FILES['piece_jointe']['size'] <= 5 * 1024 * 1024) {
            move_uploaded_file($_FILES['piece_jointe']['tmp_name'], $upload_dir . $filename);
            $piece_jointe = $filename;
        }
    }

    // Admin et enseignant publient directement
    if ($user['role'] === 'administratif' || $user['role'] === 'enseignant') {
        $statut = 'valide';
    } else {
        $statut = 'en_attente'; // étudiant → validation obligatoire
    }

    $stmt = $db->prepare("
        INSERT INTO articles (titre, contenu, type, statut, auteur_id, categorie_id, piece_jointe)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$titre, $contenu, $type, $statut, $user['id'], $categorie, $piece_jointe]);

    header("Location: index.php?page=soumettre_article&succes=1");
    exit;
}

function valider() {
    $db         = getDB();
    $article_id = $_POST['article_id'] ?? 0;

    $stmt = $db->prepare("UPDATE articles SET statut = 'valide', date_validation = NOW() WHERE id = ?");
    $stmt->execute([$article_id]);

    header('Location: index.php?page=articles&succes=valide');
    exit;
}

function rejeter() {
    $db          = getDB();
    $article_id  = $_POST['article_id'] ?? 0;
    $commentaire = trim($_POST['commentaire'] ?? '');

    if (empty($commentaire)) {
        header('Location: index.php?page=articles&erreur=commentaire_requis');
        exit;
    }

    $stmt = $db->prepare("UPDATE articles SET statut = 'archive', commentaire_rejet = ? WHERE id = ?");
    $stmt->execute([$commentaire, $article_id]);

    header('Location: index.php?page=articles&succes=rejete');
    exit;
}

function supprimer() {
    global $user;
    $db         = getDB();
    $article_id = $_POST['article_id'] ?? 0;

    // Vérifier que l'article existe
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();

    if (!$article) {
        header('Location: index.php?page=dashboard&erreur=article_introuvable');
        exit;
    }

    // Seul l'auteur ou l'admin peut supprimer
    if ($article['auteur_id'] !== $user['id'] && $user['role'] !== 'administratif') {
        header('Location: index.php?page=dashboard&erreur=non_autorise');
        exit;
    }

    // Supprimer la pièce jointe si elle existe
    if (!empty($article['piece_jointe'])) {
        $fichier = __DIR__ . '/../../public/uploads/' . $article['piece_jointe'];
        if (file_exists($fichier)) {
            unlink($fichier);
        }
    }

    // Supprimer l'article
    $db->prepare("DELETE FROM articles WHERE id = ?")->execute([$article_id]);

    // Rediriger selon le rôle
    $retour = $user['role'] === 'etudiant'
        ? 'etudiant_dashboard'
        : ($user['role'] === 'enseignant' ? 'enseignant_dashboard' : 'articles');

    header("Location: index.php?page=$retour&succes=supprime");
    exit;
}
?>
