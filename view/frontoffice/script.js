document.addEventListener('DOMContentLoaded', function () {
  
    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));

    
    function toast(message, type = 'success') {
        const toast = Object.assign(document.createElement('div'), {
            className: `toast toast-${type}`,
            textContent: message,
            style: `
                position:fixed;top:1rem;right:1rem;
                background:${type === 'error' ? '#dc2626' : '#10b981'};
                color:#fff;padding:.75rem 1.5rem;
                border-radius:.5rem;z-index:9999;
                animation:slideIn .3s ease, fadeOut 3s 2s forwards;
                box-shadow:0 4px 12px rgba(0,0,0,0.15);
            `
        });
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    
    if (document.body.classList.contains('admin-layout') && $('.admin-main')) {
        const table = $('table');
        const searchInput = $('input[type="search"]');
        const catSelect = $('select:nth-of-type(1)');
        const statusSelect = $('select:nth-of-type(2)');

        function filterTable() {
            const query = searchInput.value.trim().toLowerCase();
            const cat = catSelect?.value;
            const status = statusSelect?.value;

            $$('tbody tr').forEach(row => {
                const title = row.cells[2].textContent.toLowerCase();
                const category = row.cells[3].textContent;
                const priceCell = row.cells[4].textContent;
                const id = row.cells[0].textContent;

                const matchesSearch = title.includes(query);
                const matchesCat = !cat || category === cat;
                const matchesStatus = !status || row.querySelector('.status')?.textContent === status;

                row.style.display = matchesSearch && matchesCat && matchesStatus ? '' : 'none';
            });
        }

        [searchInput, catSelect, statusSelect].forEach(el => {
            if (el) el.addEventListener('input', filterTable);
            if (el) el.addEventListener('change', filterTable);
        });

        
        $$('thead th').forEach((th, idx) => {
            if (idx === 1 || idx === 5) return;
            th.style.cursor = 'pointer';
            th.title = 'Cliquer pour trier';
            th.addEventListener('click', () => sortTable(idx));
        });

        function sortTable(colIdx) {
            const rows = Array.from($$('tbody tr')).filter(r => r.style.display !== 'none');
            const isAsc = table.dataset.sortCol == colIdx && table.dataset.sortDir === 'asc';
            table.dataset.sortCol = colIdx;
            table.dataset.sortDir = isAsc ? 'desc' : 'asc';

            rows.sort((a, b) => {
                let A = a.cells[colIdx].textContent.trim();
                let B = b.cells[colIdx].textContent.trim();

                if (colIdx === 4) { 
                    A = parseFloat(A.replace(/[^0-9.]/g, '')) || 0;
                    B = parseFloat(B.replace(/[^0-9.]/g, '')) || 0;
                } else if (colIdx === 0) {
                    A = parseInt(A, 10);
                    B = parseInt(B, 10);
                }

                if (A < B) return isAsc ? 1 : -1;
                if (A > B) return isAsc ? -1 : 1;
                return 0;
            });

            const tbody = $('tbody');
            rows.forEach(row => tbody.appendChild(row));
        }

      
        $$('.btn-delete').forEach(btn => {
            btn.addEventListener('click', e => {
                if (!confirm(`Supprimer "${btn.closest('tr').cells[2].textContent}" ?`)) {
                    e.preventDefault();
                }
            });
        });

       
        const urlParams = new URLSearchParams(location.search);
        if (urlParams.get('success') === 'add') toast('Jeu ajouté avec succès !');
        if (urlParams.get('success') === 'edit') toast('Jeu modifié avec succès !');
        if (urlParams.get('success') === 'delete') toast('Jeu supprimé.');
        if (urlParams.get('error')) toast('Une erreur est survenue.', 'error');
    }

   
    if (document.querySelector('.catalogue')) {
        const searchInput = $('input[type="search"]');
        const sortSelect = $('select');

        function filterCatalogue() {
            const query = searchInput.value.trim().toLowerCase();
            const sort = sortSelect.value;

            const cards = $$('.game-card');
            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = title.includes(query) ? '' : 'none';
            });

            const container = $('.games-grid');
            const visible = cards.filter(c => c.style.display !== 'none');
            visible.sort((a, b) => {
                const A = a.querySelector('h3').textContent;
                const B = b.querySelector('h3').textContent;
                return sort === 'az' ? A.localeCompare(B) : B.localeCompare(A);
            });
            visible.forEach(c => container.appendChild(c));
        }

        searchInput?.addEventListener('input', filterCatalogue);
        sortSelect?.addEventListener('change', filterCatalogue);
    }

    
    const form = document.querySelector('form');
    if (form && form.enctype === 'multipart/form-data') {
        const titreInput = $('#titre');
        const prixInput = $('#prix');
        const fileInput = $('#image');
        const catSelect = $('#id_categorie');

        
        const previewContainer = document.createElement('div');
        previewContainer.style.cssText = 'margin-top:.5rem;max-height:150px;overflow:hidden;border-radius:.375rem;box-shadow:0 2px 6px rgba(0,0,0,.1);background:#f9fafb;padding:0.5rem;text-align:center;';
        fileInput?.parentNode.appendChild(previewContainer);

        fileInput?.addEventListener('change', () => {
            const file = fileInput.files[0];
            previewContainer.innerHTML = '';

            if (file) {
                if (!['image/jpeg','image/jpg','image/png','image/webp'].includes(file.type)) {
                    toast('Format non supporté (JPG, PNG, WebP uniquement)', 'error');
                    fileInput.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    toast('Image trop lourde (max 5 Mo)', 'error');
                    fileInput.value = '';
                    return;
                }

                const img = new Image();
                img.style.cssText = 'max-width:100%;max-height:150px;object-fit:contain;display:block;margin:0 auto;border-radius:.375rem;';
                img.src = URL.createObjectURL(file);
                previewContainer.appendChild(img);
            }
        });

       
        form.addEventListener('submit', e => {
            const errors = [];

            if (!titreInput?.value.trim()) errors.push('Titre requis');
            if (!prixInput?.value || parseFloat(prixInput.value) < 0) errors.push('Prix invalide');
            if (!catSelect?.value) errors.push('Catégorie requise');

            
            const isAdding = location.pathname.includes('ajouter.php');
            if (isAdding && !fileInput?.files[0]) {
                errors.push('Image requise');
            }

            if (errors.length > 0) {
                e.preventDefault();
                toast(errors.join(' • '), 'error');
            }
        });
    }

    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn { from { transform:translateX(100%); } to { transform:translateX(0); } }
        @keyframes fadeOut { to { opacity:0; } }
        .toast { animation: slideIn .3s ease forwards; }
    `;
    document.head.appendChild(style);
});