<?php
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../api/sheets.php';

$internships = csvToAssoc(fetchSheetCsv('Internships'));
$volunteers  = csvToAssoc(fetchSheetCsv('Volunteers'));

$tab = ($_GET['tab'] ?? 'internships') === 'volunteers' ? 'volunteers' : 'internships';

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Umeed Admin — Dashboard</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, sans-serif;
      background: #F5EFE6;
      color: #1A1410;
      min-height: 100vh;
    }

    /* ── Header ── */
    header {
      background: #fff;
      border-bottom: 1px solid #D9CFB8;
      padding: 0 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 60px;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    .header-logo { font-size: 1.3rem; font-weight: 700; color: #C8541F; }
    .header-right { display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; }
    .btn-sheets {
      padding: 0.4rem 1rem;
      background: #1a73e8;
      color: #fff;
      border: none;
      border-radius: 50px;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      transition: background 0.2s;
    }
    .btn-sheets:hover { background: #1558b0; }
    .btn-logout {
      padding: 0.4rem 1rem;
      background: #EBE2D2;
      color: #1A1410;
      border: 1px solid #D9CFB8;
      border-radius: 50px;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 500;
      transition: background 0.2s;
    }
    .btn-logout:hover { background: #D9CFB8; }

    /* ── Layout ── */
    main { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; }

    /* ── Stats ── */
    .stats { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .stat-card {
      background: #fff;
      border: 1px solid #D9CFB8;
      border-radius: 12px;
      padding: 1.1rem 1.5rem;
      min-width: 160px;
      flex: 1;
    }
    .stat-label { font-size: 0.8rem; color: #4A3F35; text-transform: uppercase; letter-spacing: 0.05em; }
    .stat-num { font-size: 2rem; font-weight: 700; color: #C8541F; line-height: 1.2; }

    /* ── Tabs ── */
    .tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
    .tab {
      padding: 0.55rem 1.4rem;
      border-radius: 50px;
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid #D9CFB8;
      color: #4A3F35;
      background: #fff;
      transition: all 0.2s;
    }
    .tab.active { background: #C8541F; color: #fff; border-color: #C8541F; }
    .tab:not(.active):hover { background: #EBE2D2; }

    /* ── Table ── */
    .table-wrap {
      background: #fff;
      border: 1px solid #D9CFB8;
      border-radius: 14px;
      overflow: hidden;
    }
    .table-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #D9CFB8;
      font-weight: 600;
      color: #4A3F35;
      font-size: 0.9rem;
    }
    .scroll-x { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
    th {
      text-align: left;
      padding: 0.75rem 1rem;
      background: #F5EFE6;
      color: #4A3F35;
      font-weight: 600;
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      white-space: nowrap;
      border-bottom: 1px solid #D9CFB8;
    }
    td {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #EBE2D2;
      color: #1A1410;
      vertical-align: top;
      max-width: 220px;
      overflow-wrap: break-word;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #faf7f2; }
    .badge {
      display: inline-block;
      padding: 0.2rem 0.65rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      background: #EBE2D2;
      color: #4A3F35;
      white-space: nowrap;
    }
    .date { color: #4A3F35; white-space: nowrap; font-size: 0.8rem; }
    .empty {
      text-align: center;
      padding: 3rem 1rem;
      color: #4A3F35;
      font-size: 0.95rem;
    }
    .msg-cell { max-width: 260px; }
    @media (max-width: 600px) {
      main { padding: 1rem; }
      header { padding: 0 1rem; }
    }
  </style>
</head>
<body>

<header>
  <span class="header-logo">Umeed — Admin</span>
  <div class="header-right">
    <a href="https://docs.google.com/spreadsheets/d/<?= urlencode(SPREADSHEET_ID) ?>"
       target="_blank" rel="noopener" class="btn-sheets">Open in Google Sheets</a>
    <a href="logout.php" class="btn-logout">Sign Out</a>
  </div>
</header>

<main>

  <!-- Stats -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-label">Internship Applications</div>
      <div class="stat-num"><?= count($internships) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Volunteer Applications</div>
      <div class="stat-num"><?= count($volunteers) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Submissions</div>
      <div class="stat-num"><?= count($internships) + count($volunteers) ?></div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <a href="?tab=internships" class="tab <?= $tab === 'internships' ? 'active' : '' ?>">
      Internship (<?= count($internships) ?>)
    </a>
    <a href="?tab=volunteers" class="tab <?= $tab === 'volunteers' ? 'active' : '' ?>">
      Volunteer (<?= count($volunteers) ?>)
    </a>
  </div>

  <!-- Internships Table -->
  <?php if ($tab === 'internships'): ?>
  <div class="table-wrap">
    <div class="table-header">Internship Applications</div>
    <div class="scroll-x">
      <?php if (empty($internships)): ?>
        <div class="empty">No internship applications yet.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>College / University</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($internships as $i => $row): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= e($row['Name']) ?></strong></td>
            <td><a href="mailto:<?= e($row['Email']) ?>" style="color:#C8541F"><?= e($row['Email']) ?></a></td>
            <td><?= e($row['Phone']) ?: '<span style="color:#aaa">—</span>' ?></td>
            <td><?= e($row['College / University']) ?: '<span style="color:#aaa">—</span>' ?></td>
            <td class="date"><?= e($row['Submitted At']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Volunteers Table -->
  <?php if ($tab === 'volunteers'): ?>
  <div class="table-wrap">
    <div class="table-header">Volunteer Applications</div>
    <div class="scroll-x">
      <?php if (empty($volunteers)): ?>
        <div class="empty">No volunteer applications yet.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>City</th>
            <th>Availability</th>
            <th>Skills</th>
            <th>Message</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($volunteers as $i => $row): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= e($row['Name']) ?></strong></td>
            <td><a href="mailto:<?= e($row['Email']) ?>" style="color:#C8541F"><?= e($row['Email']) ?></a></td>
            <td><?= e($row['Phone'])  ?: '<span style="color:#aaa">—</span>' ?></td>
            <td><?= e($row['City'])   ?: '<span style="color:#aaa">—</span>' ?></td>
            <td><?php if (!empty($row['Availability'])): ?><span class="badge"><?= e($row['Availability']) ?></span><?php else: ?><span style="color:#aaa">—</span><?php endif; ?></td>
            <td><?= e($row['Skills'])  ?: '<span style="color:#aaa">—</span>' ?></td>
            <td class="msg-cell"><?= e($row['Message']) ?: '<span style="color:#aaa">—</span>' ?></td>
            <td class="date"><?= e($row['Submitted At']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</main>
</body>
</html>
