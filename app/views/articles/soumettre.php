<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}
$user = $_SESSION['user'];
require_once 'config/database.php';
$db = getDB();

$categories = $db->query("SELECT * FROM categories")->fetchAll();

$retour = $user['role'] === 'etudiant'
    ? 'etudiant_dashboard'
    : ($user['role'] === 'enseignant' ? 'enseignant_dashboard' : 'dashboard');
?>
<?php
$titre_page = 'Soumettre un article';
$page_active = 'soumettre_article';
require 'app/views/layouts/header.php';
?>
</head>
<body>
<?php require 'app/views/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="form-card">
            <h1> Soumettre un article</h1>

            <?php if ($user['role'] === 'etudiant'): ?>
                <p>Votre article sera examiné par l'équipe administrative avant publication.</p>
            <?php else: ?>
                <p>Votre article sera publié directement et disponible pour la prochaine newsletter.</p>
            <?php endif; ?>

            <?php if (isset($_GET['succes'])): ?>
                <div class="alert-success">
                    <?php if ($user['role'] === 'etudiant'): ?>
                         Article soumis avec succès ! Il est en attente de validation.
                    <?php else: ?>
                         Article publié avec succès ! Il est disponible pour la prochaine newsletter.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($user['role'] === 'etudiant'): ?>
                <div class="note-etudiant">
                    ⚠️ En tant qu'étudiant, vos articles sont limités aux <strong>Clubs & Associations</strong> et nécessitent une validation obligatoire.
                </div>
            <?php elseif ($user['role'] === 'enseignant'): ?>
                <div class="note-direct">
                     En tant qu'enseignant, vos articles sont <strong>publiés directement</strong> sans validation.
                </div>
            <?php elseif ($user['role'] === 'administratif'): ?>
                <div class="note-direct">
                     En tant qu'administrateur, vos articles sont <strong>publiés directement</strong> et disponibles immédiatement.
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="page" value="traitement_article">
                <input type="hidden" name="csrf_token" value="<?= genererToken() ?>">
                <input type="hidden" name="action" value="soumettre">

                <div class="mb-3">
                    <label class="form-label">Titre de l'article *</label>
                    <input type="text" name="titre" class="form-control"
                           placeholder="Ex: Résultats du concours de programmation" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Type d'article *</label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php if ($user['role'] === 'etudiant'): ?>
                                <option value="club">Club / Association</option>
                            <?php else: ?>
                                <option value="cours">Cours & Pédagogie</option>
                                <option value="evenement">Événement</option>
                                <option value="annonce">Annonce</option>
                                <option value="resultat">Résultat</option>
                                <option value="club">Club / Association</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Catégorie</label>
                        <select name="categorie_id" class="form-select">
                            <option value="">-- Choisir --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu de l'article *</label>
                    <textarea name="contenu" class="form-control"
                              placeholder="Rédigez votre article ici..." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Pièce jointe <small style="color:#adb5bd">(image ou PDF, max 5Mo)</small></label>
                    <input type="file" name="piece_jointe" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <a href="index.php?page=<?= $retour ?>" class="btn-retour">← Retour</a>
                <button type="submit" class="btn-submit"> Soumettre l'article</button>
            </form>
        </div>
    </div>
<?php require 'app/views/layouts/footer.php'; ?>