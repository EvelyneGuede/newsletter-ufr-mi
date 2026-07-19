// ══════════════════════════════════════
//   JS GLOBAL — Newsletter UFR-MI
// ══════════════════════════════════════

// 1. Confirmation avant suppression
function confirmerSuppression(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir supprimer ?');
}

// 2. Afficher/Masquer le formulaire de rejet
function toggleRejet(id) {
    const form = document.getElementById('rejet-' + id);
    if (form) {
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }
}

// 3. Filtrer le tableau des utilisateurs
function filtrer() {
    const recherche = document.getElementById('recherche')?.value.toLowerCase() || '';
    const role      = document.getElementById('filtreRole')?.value || '';
    document.querySelectorAll('.user-row').forEach(row => {
        const matchNom  = row.dataset.nom?.includes(recherche);
        const matchRole = role === '' || row.dataset.role === role;
        row.style.display = (matchNom && matchRole) ? '' : 'none';
    });
}

// 4. Sélectionner/désélectionner articles newsletter
function toggleSelected(id) {
    const label = document.getElementById('cb-' + id);
    if (label) {
        const cb = label.querySelector('input');
        label.classList.toggle('selected', cb.checked);
    }
}

// 5. Auto-masquer les alertes après 5 secondes
document.addEventListener('DOMContentLoaded', function () {
    const alertes = document.querySelectorAll('.alert-success, .alert-danger, .alert-warning');
    alertes.forEach(alerte => {
        setTimeout(() => {
            alerte.style.transition = 'opacity 0.5s';
            alerte.style.opacity = '0';
            setTimeout(() => alerte.remove(), 500);
        }, 5000);
    });
});