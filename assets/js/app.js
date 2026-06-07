/**
 * Library Management System — App JS
 */

// ── Theme Toggle ──────────────────────────────────────────────
const themeBtn = document.getElementById('themeToggle');
const root = document.documentElement;

function setTheme(theme) {
  root.dataset.theme = theme;
  localStorage.setItem('lms-theme', theme);
  if (themeBtn) {
    themeBtn.querySelector('i').className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
  }
}
// Load saved theme
setTheme(localStorage.getItem('lms-theme') || 'light');
if (themeBtn) themeBtn.addEventListener('click', () =>
  setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark')
);

// ── Sidebar Toggle ────────────────────────────────────────────
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');

function openSidebar() {
  sidebar?.classList.add('open');
  let overlay = document.querySelector('.sidebar-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', closeSidebar);
    document.body.appendChild(overlay);
  }
}
function closeSidebar() {
  sidebar?.classList.remove('open');
  document.querySelector('.sidebar-overlay')?.remove();
}
sidebarToggle?.addEventListener('click', () =>
  sidebar?.classList.contains('open') ? closeSidebar() : openSidebar()
);

// ── Dropdown Menus ────────────────────────────────────────────
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-dropdown]');
  if (trigger) {
    e.stopPropagation();
    const target = document.querySelector(trigger.dataset.dropdown);
    if (!target) return;
    // close others
    document.querySelectorAll('.dropdown-menu.show').forEach(m => {
      if (m !== target) m.classList.remove('show');
    });
    target.classList.toggle('show');
    return;
  }
  document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
});

// ── Notifications Badge ───────────────────────────────────────
function loadNotifications() {
  const badge = document.getElementById('notifBadge');
  const list  = document.getElementById('notifList');
  if (!badge && !list) return;
  fetch(BASE_URL + '/api/notifications.php?action=unread_count')
    .then(r => r.json())
    .then(data => {
      if (data.count > 0) {
        badge.textContent = data.count;
        badge.style.display = 'inline';
      } else {
        badge.style.display = 'none';
      }
    }).catch(() => {});
}
loadNotifications();

// ── Auto-dismiss Flash Alerts ─────────────────────────────────
document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
  setTimeout(() => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  }, 4000);
});

// ── Delete Confirmation ───────────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', e => {
    if (!confirm(btn.dataset.confirm || 'Are you sure?')) {
      e.preventDefault();
    }
  });
});

// ── DataTable-style Search (client-side fallback) ─────────────
const tableSearch = document.getElementById('tableSearch');
if (tableSearch) {
  tableSearch.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

// ── AJAX Member Search for Issue Form ─────────────────────────
const memberSearchInput = document.getElementById('memberSearch');
const memberResults     = document.getElementById('memberResults');
const memberIdField     = document.getElementById('member_id');

if (memberSearchInput) {
  let timer;
  memberSearchInput.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { memberResults.innerHTML = ''; return; }
    timer = setTimeout(() => {
      fetch(BASE_URL + '/api/search.php?type=member&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          memberResults.innerHTML = data.map(m =>
            `<div class="search-result-item" data-id="${m.id}" data-name="${m.full_name}">
               <strong>${m.full_name}</strong> <small>${m.member_id}</small>
             </div>`
          ).join('') || '<div class="search-result-item text-muted">No results</div>';
        });
    }, 300);
  });

  memberResults?.addEventListener('click', e => {
    const item = e.target.closest('.search-result-item');
    if (item && item.dataset.id) {
      memberIdField.value   = item.dataset.id;
      memberSearchInput.value = item.dataset.name;
      memberResults.innerHTML = '';
    }
  });
}

// ── AJAX Book Search for Issue Form ──────────────────────────
const bookSearchInput = document.getElementById('bookSearch');
const bookResults     = document.getElementById('bookResults');
const bookIdField     = document.getElementById('book_id');

if (bookSearchInput) {
  let timer2;
  bookSearchInput.addEventListener('input', function () {
    clearTimeout(timer2);
    const q = this.value.trim();
    if (q.length < 2) { bookResults.innerHTML = ''; return; }
    timer2 = setTimeout(() => {
      fetch(BASE_URL + '/api/search.php?type=book&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          bookResults.innerHTML = data.map(b =>
            `<div class="search-result-item" data-id="${b.id}" data-name="${b.title}">
               <strong>${b.title}</strong>
               <small>${b.author_name || ''} &bull; Available: ${b.available_quantity}</small>
             </div>`
          ).join('') || '<div class="search-result-item text-muted">No results</div>';
        });
    }, 300);
  });

  bookResults?.addEventListener('click', e => {
    const item = e.target.closest('.search-result-item');
    if (item && item.dataset.id) {
      bookIdField.value    = item.dataset.id;
      bookSearchInput.value = item.dataset.name;
      bookResults.innerHTML = '';
    }
  });
}

// ── Image Preview on Upload ───────────────────────────────────
document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
  input.addEventListener('change', function () {
    const preview = document.getElementById(this.dataset.preview);
    if (!preview || !this.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; };
    reader.readAsDataURL(this.files[0]);
  });
});

// ── Sidebar active link ───────────────────────────────────────
const currentPath = window.location.pathname;
document.querySelectorAll('.nav-link').forEach(link => {
  if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop().replace('.php', ''))) {
    link.classList.add('active');
  }
});
