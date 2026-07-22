<?php
if (!isset($_GET['id'])) {
    header('Location: index.php?page=archives');
    exit;
}

require_once ROOT_PATH . '/config/database.php';
$db = getDB();

$id = $_GET['id'];
$stmt = $db->prepare("SELECT * FROM newsletters WHERE id = ? AND statut = 'envoyee'");
$stmt->execute([$id]);
$newsletter = $stmt->fetch();

if (!$newsletter) {
    header('Location: index.php?page=archives');
    exit;
}

$titre_page  = $newsletter['titre'];
$page_active = 'archives';
require VIEWS_PATH . '/layouts/header.php';
?>
    <style>
        .main-content { margin-left: 0 !important; padding: 32px !important; }
        body { min-height: auto; }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="top-bar" style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
            <div>
                <h1>📰 <?= htmlspecialchars($newsletter['titre']) ?></h1>
                <div style="margin-top:6px; color:#6c757d; font-size:14px;">
                    Envoyée le <?= date('d/m/Y', strtotime($newsletter['date_envoi'])) ?>
                </div>
            </div>
            <img src="app/views/admin/logomi.jpeg" alt="Logo du département"
                 style="width:72px; height:auto; max-height:60px; object-fit:contain; border-radius:10px; background:#fff; padding:6px; border:1px solid #e0e0ff; box-shadow:0 4px 12px rgba(21,21,181,0.12);">
        </div>

        <a href="index.php?page=archives" class="btn-retour">← Retour aux archives</a>

        <iframe class="preview-frame"
                srcdoc="<?= htmlspecialchars($newsletter['template_html']) ?>"
                style="width:100%; height:700px; border:none; margin-top:20px; display:block;">
        </iframe>
    </div>
<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
