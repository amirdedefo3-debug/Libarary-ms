<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Library Management System') ?></title>

  <!-- Font Awesome (local CDN with swap) -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

  <!-- Chart.js (defer so it never blocks first paint) -->
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <script>
    // Expose base URL for JS modules
    const BASE_URL = "<?= BASE_URL ?>";
    // Apply saved theme before paint — prevents flash
    (function(){
      const t = localStorage.getItem('lms-theme');
      if (t) document.documentElement.dataset.theme = t;
    })();
  </script>
</head>
<body>
