<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administratif') {
    header('Location: index.php?page=login');
    exit;
}
require_once ROOT_PATH . '/config/database.php';
$db   = getDB();
$user = $_SESSION['user'];

$demandes = $db->query(" 
    SELECT * FROM utilisateurs
    WHERE validation_statut = 'en_attente'
    AND role != 'etudiant'
    ORDER BY created_at DESC
")->fetchAll();

$historique = $db->query(" 
    SELECT * FROM utilisateurs
    WHERE validation_statut IN ('accepte', 'refuse')
    AND role != 'etudiant'
    ORDER BY date_validation_compte DESC
    LIMIT 20
")->fetchAll();

$titre_page  = 'Demandes de compte';
$page_active = 'demandes';
require VIEWS_PATH . '/layouts/header.php';
?>

<?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

<div class="main-content">

    <div class="page-header">
        <h1> Demandes de compte</h1>
        <p>Validez ou refusez les demandes d'enseignants et d'administratifs</p>
    </div>

    <?php if (isset($_GET['succes'])): ?>
        <div class="alert-success">
            <?= $_GET['succes'] === 'accepte' ? 'Compte accepté ! L\'utilisateur peut maintenant se connecter.' : 'Demande refusée.' ?>
        </div>
    <?php endif; ?>

    <!-- Demandes en attente -->
    <div class="table-card" style="margin-bottom:28px;">
        <h2>En attente de validation (<?= count($demandes) ?>)</h2>

        <?php if (empty($demandes)): ?>
            <div class="empty-state">
                <div class="icon"></div>
                <p>Aucune demande en attente.</p>
            </div>
        <?php else: ?>
            <?php foreach ($demandes as $d): ?>
            <div style="background:#fff; border:1.5px solid #e0e0ff; border-radius:12px;
                        padding:20px; margin-bottom:16px; border-left:4px solid #f39c12;">

                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">

                    <!-- Infos utilisateur -->
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div style="width:50px; height:50px; border-radius:50%; background:#1515b5;
                                    display:flex; align-items:center; justify-content:center;
                                    font-weight:700; color:#fff; font-size:18px; flex-shrink:0;">
                            <?= strtoupper(substr($d['prenom'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-size:16px; font-weight:700; color:#1515b5;">
                                <?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?>
                            </div>
                            <div style="font-size:13px; color:#6c757d; margin-top:2px;">
                                 <?= htmlspecialchars($d['email']) ?>
                            </div>
                            <div style="font-size:13px; color:#6c757d; margin-top:2px;">
                                 <?= ucfirst($d['role']) ?>
                                <?php if ($d['departement']): ?>
                                    &nbsp;•&nbsp;  <?= htmlspecialchars($d['departement']) ?>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:12px; color:#adb5bd; margin-top:4px;">
                                 Demande du <?= date('d/m/Y à H:i', strtotime($d['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons action -->
                    <div style="display:flex; flex-direction:column; gap:8px; min-width:200px;">

                        <!-- Accepter -->
                        <form action="index.php" method="POST">
                            <input type="hidden" name="page" value="traitement_demande">
                            <input type="hidden" name="action" value="accepter">
                            <input type="hidden" name="user_id" value="<?= $d['id'] ?>">
                            <button type="submit"
                                style="background:#d1e7dd; color:#0f5132; border:none; border-radius:8px;
                                       padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer;
                                       width:100%; transition:background 0.2s;"
                                onmouseover="this.style.background='#b7e4c7'"
                                onmouseout="this.style.background='#d1e7dd'">
                                Accepter la demande
                            </button>
                        </form>

                        <!-- Refuser -->
                        <button onclick="toggleRefus(<?= $d['id'] ?>)"
                            style="background:#f8d7da; color:#842029; border:none; border-radius:8px;
                                   padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer;
                                   width:100%;">
                            Refuser la demande
                        </button>

                        <!-- Formulaire de refus -->
                        <form action="index.php" method="POST"
                              id="refus-<?= $d['id'] ?>"
                              style="display:none;">
                            <input type="hidden" name="page" value="traitement_demande">
                            <input type="hidden" name="action" value="refuser">
                            <input type="hidden" name="user_id" value="<?= $d['id'] ?>">
                            <textarea name="motif_refus"
                                placeholder="Motif du refus (obligatoire)..."
                                required
                                style="width:100%; border:1.5px solid #e0e0ff; border-radius:8px;
                                       padding:10px; font-size:13px; margin-bottom:8px;
                                       min-height:80px; font-family:'Inter',sans-serif;">
                            </textarea>
                            <button type="submit"
                                style="background:#842029; color:#fff; border:none; border-radius:8px;
                                       padding:8px 16px; font-size:13px; font-weight:600;
                                       cursor:pointer; width:100%;">
                                Confirmer le refus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Historique -->
    <div class="table-card">
        <h2> Historique des demandes traitées</h2>
        <?php if (empty($historique)): ?>
            <div class="empty-state">
                <div class="icon"></div>
                <p>Aucun historique.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Motif refus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $h): ?>
                    <tr>
                        <td style="font-weight:600;">
                            <?= htmlspecialchars($h['prenom'] . ' ' . $h['nom']) ?>
                        </td>
                        <td><?= ucfirst($h['role']) ?></td>
                        <td style="font-size:12px; color:#6c757d;">
                            <?= htmlspecialchars($h['email']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $h['validation_statut'] === 'accepte' ? 'badge-valide' : 'badge-archive' ?>">
                                <?= $h['validation_statut'] === 'accepte' ? 'Accepté' : 'Refusé' ?>
                            </span>
                        </td>
                        <td style="font-size:12px; color:#6c757d;">
                            <?= $h['date_validation_compte'] ? date('d/m/Y', strtotime($h['date_validation_compte'])) : '—' ?>
                        </td>
                        <td style="font-size:12px; color:#842029;">
                            <?= htmlspecialchars($h['motif_refus'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleRefus(id) {
    const form = document.getElementById('refus-' + id);
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
