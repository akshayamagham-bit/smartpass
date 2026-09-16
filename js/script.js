/* =====================================================================
   SMARTPASS - Client-side JavaScript
   Handles: nav toggle, password toggle, category pills, tabs,
   toasts, form validation, confirm dialogs, scroll header.
   ===================================================================== */
(function () {
  'use strict';

  /* ---------- mobile nav toggle ---------- */
  var navToggle = document.getElementById('navToggle');
  var siteNav = document.getElementById('siteNav');
  if (navToggle && siteNav) {
    navToggle.addEventListener('click', function () {
      siteNav.classList.toggle('open');
    });
    // close on link click (mobile)
    siteNav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { siteNav.classList.remove('open'); });
    });
  }

  /* ---------- dashboard sidebar toggle (mobile) ---------- */
  var sidebarToggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
    // close sidebar when clicking a link on mobile
    sidebar.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        if (window.innerWidth <= 768) sidebar.classList.remove('open');
      });
    });
  }

  /* ---------- sticky header shadow on scroll ---------- */
  var header = document.getElementById('siteHeader');
  if (header) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 10) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    });
  }

  /* ---------- password visibility toggle ---------- */
  document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.getElementById(btn.getAttribute('data-target'));
      if (!target) return;
      if (target.type === 'password') {
        target.type = 'text';
        btn.textContent = '🙈';
      } else {
        target.type = 'password';
        btn.textContent = '👁';
      }
    });
  });

  /* ---------- login role toggle ---------- */
  var roleToggle = document.getElementById('roleToggle');
  var roleHidden = document.getElementById('roleHidden');
  if (roleToggle && roleHidden) {
    roleToggle.querySelectorAll('.role-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        roleToggle.querySelectorAll('.role-btn').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        roleHidden.value = btn.getAttribute('data-role');
      });
    });
  }

  /* ---------- category pills (events filter) ---------- */
  var categoryPills = document.getElementById('categoryPills');
  var catHidden = document.getElementById('catHidden');
  if (categoryPills && catHidden) {
    categoryPills.querySelectorAll('.pill').forEach(function (pill) {
      pill.addEventListener('click', function () {
        categoryPills.querySelectorAll('.pill').forEach(function (p) { p.classList.remove('active'); });
        pill.classList.add('active');
        catHidden.value = pill.getAttribute('data-cat');
      });
    });
  }

  /* ---------- leaderboard filter pills ---------- */
  var lbFilter = document.getElementById('leaderboardFilter');
  var filterHidden = document.getElementById('filterHidden');
  if (lbFilter && filterHidden) {
    lbFilter.querySelectorAll('.pill').forEach(function (pill) {
      pill.addEventListener('click', function () {
        lbFilter.querySelectorAll('.pill').forEach(function (p) { p.classList.remove('active'); });
        pill.classList.add('active');
        filterHidden.value = pill.getAttribute('data-filter');
      });
    });
  }

  /* ---------- tabs (my events) ---------- */
  var myEventsTabs = document.getElementById('myEventsTabs');
  if (myEventsTabs) {
    myEventsTabs.querySelectorAll('.tab-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        myEventsTabs.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var target = btn.getAttribute('data-tab');
        document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
        var panel = document.getElementById('tab-' + target);
        if (panel) panel.classList.add('active');
      });
    });
  }

  /* ---------- register form client-side validation ---------- */
  var registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      var pw1 = registerForm.querySelector('#pw1');
      var pw2 = registerForm.querySelector('#pw2');
      if (pw1 && pw2 && pw1.value !== pw2.value) {
        e.preventDefault();
        pw2.setCustomValidity('Passwords do not match');
        pw2.reportValidity();
        showToast('Passwords do not match', 'error');
        return;
      }
      if (pw2) pw2.setCustomValidity('');
      if (pw1 && pw1.value.length < 6) {
        e.preventDefault();
        showToast('Password must be at least 6 characters', 'error');
        return;
      }
      showToast('Creating your account...', 'success');
    });
  }

  /* ---------- password change form validation ---------- */
  var pwForm = document.getElementById('pwForm');
  if (pwForm) {
    pwForm.addEventListener('submit', function (e) {
      var npw = pwForm.querySelector('#npw');
      var cnpw = pwForm.querySelector('#cnpw');
      if (npw && cnpw && npw.value !== cnpw.value) {
        e.preventDefault();
        showToast('New passwords do not match', 'error');
        return;
      }
    });
  }

  /* ---------- toast helper ---------- */
  function showToast(message, type) {
    var wrap = document.getElementById('toastWrap');
    if (!wrap) return;
    var toast = document.createElement('div');
    toast.className = 'toast' + (type ? ' ' + type : '');
    toast.textContent = message;
    wrap.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(20px)';
      toast.style.transition = 'all .3s ease';
      setTimeout(function () { toast.remove(); }, 300);
    }, 3000);
  }

  /* ---------- show toasts from query params ---------- */
  var params = new URLSearchParams(window.location.search);
  if (params.get('created')) showToast('Event created successfully', 'success');
  if (params.get('deleted')) showToast('Event deleted', 'success');
  if (params.get('welcome')) showToast('Welcome to SmartPass!', 'success');

  /* ---------- expose for inline use ---------- */
  window.showToast = showToast;
})();
