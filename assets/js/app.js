/**
 * Library Management System — app.js
 * All handlers run after DOM is ready (script is deferred).
 */

/* ── Page-load progress bar ─────────────────────────────── */
(function () {
  const bar = document.getElementById('page-loader');
  if (!bar) return;

  // Start at 20% immediately (visible before any slow network call)
  bar.style.width = '20%';
  // Jump to 85% quickly — gives the "almost done" feel
  setTimeout(() => { bar.style.width = '85%'; }, 80);

  window.addEventListener('load', () => {
    bar.style.width = '100%';
    setTimeout(() => { bar.style.opacity = '0'; bar.style.transition = 'opacity .3s'; }, 300);
  });

  // Show bar again on any link click (navigation start)
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href');
    // Only internal same-origin links, not anchors or JS links
    if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto')) return;
    if (a.target === '_blank') return;
    bar.style.opacity = '1';
    bar.style.transition = 'width .4s ease';
    bar.style.width = '40%';
    setTimeout(() => { bar.style.width = '75%'; }, 150);
  });
})();

/* ── Theme ───────────────────────────────────────────────── */
const themeBtn = document.getElementById('themeToggle');

function setTheme(theme) {
  document.documentElement.dataset.theme = theme;
  localStorage.setItem('lms-theme', theme);
  const icon = themeBtn?.querySelector('i');
  if (icon) icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Init from saved preference
setTheme(localStorage.getItem('lms-theme') || 'light');
themeBtn?.addEventListener('click', () =>
  setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark')
);

/* ── Sidebar toggle (mobile) ─────────────────────────────── */
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

function openSidebar() {
  sidebar?.classList.add('open');
  if (!document.querySelector('.sidebar-overlay')) {
    const ov = document.createElement('div');
    ov.className = 'sidebar-overlay';
    ov.addEventListener('click', closeSidebar);
    document.body.appendChild(ov);
  }
}

function closeSidebar() {
  sidebar?.classList.remove('open');
  document.querySelector('.sidebar-overlay')?.remove();
}

sidebarToggle?.addEventListener('click', () =>
  sidebar?.classList.contains('open') ? closeSidebar() : openSidebar()
);

/* ── Active nav link ─────────────────────────────────────── */
(function () {
  const path = window.location.pathname.replace(/\\/g, '/');
  document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (!href || href === '#') return;
    // Extract the PHP filename from the href
    const linkFile = href.split('/').pop().split('?')[0];
    const currFile = path.split('/').pop().split('?')[0];
    if (linkFile && currFile && linkFile === currFile) {
      link.classList.add('active');
    }
  });
})();

/* ── Dropdown menus ─────────────────────────────────────── */
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-dropdown]');
  if (trigger) {
    e.stopPropagation();
    const menu = document.querySelector(trigger.dataset.dropdown);
    if (!menu) return;
    // Close all others first
    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
      if (m !== menu) m.classList.remove('show');
    });
    menu.classList.toggle('show');
    return;
  }
  // Click outside — close all
  document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
});

/* ── Auto-dismiss flash alerts ───────────────────────────── */
document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .5s, max-height .5s, margin .5s, padding .5s';
    el.style.opacity = '0';
    el.style.maxHeight = '0';
    el.style.padding = '0';
    el.style.margin = '0';
    setTimeout(() => el.remove(), 550);
  }, 4000);
});

/* ── Confirm dialogs ─────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', e => {
    if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });
});

/* ── Client-side table quick-filter ─────────────────────── */
const tableSearch = document.getElementById('tableSearch');
if (tableSearch) {
  tableSearch.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

/* ── Image preview on file input ─────────────────────────── */
document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
  input.addEventListener('change', function () {
    const preview = document.getElementById(this.dataset.preview);
    if (!preview || !this.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; };
    reader.readAsDataURL(this.files[0]);
  });
});

/* ── AJAX member search (Issue form) ────────────────────── */
(function () {
  const input   = document.getElementById('memberSearch');
  const results = document.getElementById('memberResults');
  const hiddenId = document.getElementById('member_id');
  if (!input || !results) return;

  let timer;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { results.innerHTML = ''; return; }
    timer = setTimeout(() => {
      fetch(`${BASE_URL}/api/search.php?type=member&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          if (!data.length) {
            results.innerHTML = '<div class="search-result-item text-muted">No members found</div>';
            return;
          }
          results.innerHTML = data.map(m =>
            `<div class="search-result-item" data-id="${m.id}" data-name="${m.full_name}">
               <strong>${m.full_name}</strong>
               <small style="color:var(--text-muted);margin-left:8px;">${m.member_id}</small>
             </div>`
          ).join('');
        })
        .catch(() => { results.innerHTML = ''; });
    }, 280);
  });

  results.addEventListener('click', e => {
    const item = e.target.closest('.search-result-item[data-id]');
    if (!item) return;
    hiddenId.value   = item.dataset.id;
    input.value      = item.dataset.name;
    results.innerHTML = '';
    input.style.borderColor = 'var(--success)';
  });

  // Close on outside click
  document.addEventListener('click', e => {
    if (!input.contains(e.target) && !results.contains(e.target)) results.innerHTML = '';
  });
})();

/* ── AJAX book search (Issue form) ──────────────────────── */
(function () {
  const input    = document.getElementById('bookSearch');
  const results  = document.getElementById('bookResults');
  const hiddenId = document.getElementById('book_id');
  if (!input || !results) return;

  let timer;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { results.innerHTML = ''; return; }
    timer = setTimeout(() => {
      fetch(`${BASE_URL}/api/search.php?type=book&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          if (!data.length) {
            results.innerHTML = '<div class="search-result-item text-muted">No books found</div>';
            return;
          }
          results.innerHTML = data.map(b =>
            `<div class="search-result-item" data-id="${b.id}" data-name="${b.title}">
               <strong>${b.title}</strong>
               <small style="color:var(--text-muted);margin-left:8px;">${b.author_name || ''}</small>
               <span style="float:right;font-size:.75rem;color:var(--success);">
                 ${b.available_quantity} available
               </span>
             </div>`
          ).join('');
        })
        .catch(() => { results.innerHTML = ''; });
    }, 280);
  });

  results.addEventListener('click', e => {
    const item = e.target.closest('.search-result-item[data-id]');
    if (!item) return;
    hiddenId.value    = item.dataset.id;
    input.value       = item.dataset.name;
    results.innerHTML = '';
    input.style.borderColor = 'var(--success)';
  });

  document.addEventListener('click', e => {
    if (!input.contains(e.target) && !results.contains(e.target)) results.innerHTML = '';
  });
})();
