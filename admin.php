<?php
session_start();
require_once 'config.php';

$admin_user = 'admin';
$admin_pass = 'fornace2025';

if (isset($_POST['login'])) {
  if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
    $_SESSION['admin'] = true;
  } else { $login_error = 'Credenziali errate'; }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
if (isset($_POST['update_stato']) && isset($_SESSION['admin'])) {
  $pdo->prepare("UPDATE ordini SET stato=? WHERE id_ordine=?")->execute([$_POST['stato'],$_POST['id_ordine']]);
}

if (!isset($_SESSION['admin'])): ?>
<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8"><title>Admin Login</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{background:#1a1a1a;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif}.box{background:white;padding:40px;border-radius:12px;width:320px;text-align:center}h2{margin-bottom:24px;color:#C0392B}input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;margin-bottom:12px;font-size:.95rem}button{width:100%;background:#C0392B;color:white;border:none;padding:12px;border-radius:6px;cursor:pointer;font-size:1rem}.error{color:red;font-size:.85rem;margin-bottom:12px}</style>
</head><body><div class="box"><h2>🍕 Admin Panel</h2>
<?php if(isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
<form method="POST"><input type="text" name="username" placeholder="Username" required><input type="password" name="password" placeholder="Password" required><button name="login">Accedi</button></form></div></body></html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — La Fornace di Napoli</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Segoe UI',sans-serif;background:#f5f5f5;color:#222}
    header{background:#1a1a1a;color:white;padding:16px 32px;display:flex;justify-content:space-between;align-items:center}
    header h1{font-size:1.3rem}
    header a{color:#D4AC0D;text-decoration:none;font-size:.85rem}
    .container{max-width:1300px;margin:28px auto;padding:0 20px}
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}
    .stat-card{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.06);text-align:center}
    .stat-card strong{display:block;font-size:1.8rem;color:#C0392B;margin-bottom:4px}
    .stat-card span{font-size:.78rem;color:#999}
    h2{margin-bottom:16px;font-size:1.1rem}
    table{width:100%;background:white;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow:hidden;border-collapse:collapse;margin-bottom:28px}
    th{background:#1a1a1a;color:white;padding:11px 14px;text-align:left;font-size:.78rem;letter-spacing:1px;text-transform:uppercase}
    td{padding:11px 14px;border-bottom:1px solid #f0f0f0;font-size:.88rem;vertical-align:top}
    tr:hover td{background:#fafafa}
    .badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:700}
    .badge-ricevuto{background:#FEF3C7;color:#92400E}
    .badge-preparazione{background:#DBEAFE;color:#1E40AF}
    .badge-pronto{background:#D1FAE5;color:#065F46}
    .badge-consegnato{background:#F3F4F6;color:#374151}
    .badge-annullato{background:#FEE2E2;color:#991B1B}
    select.stato-sel{border:1px solid #ddd;border-radius:4px;padding:4px 8px;font-size:.8rem}
    .btn-upd{background:#C0392B;color:white;border:none;padding:4px 12px;border-radius:4px;cursor:pointer;font-size:.8rem}
    .impasto-row{display:flex;flex-wrap:wrap;gap:4px;margin-top:4px}
    .impasto-chip{background:#FDF6E3;border:1px solid #e0d0c0;color:#5D4037;font-size:.72rem;padding:2px 8px;border-radius:10px;white-space:nowrap}
    .toggle-detail{background:none;border:1px solid #ddd;border-radius:4px;padding:2px 8px;font-size:.75rem;cursor:pointer;color:#666;margin-top:4px}
    .detail-panel{display:none;margin-top:8px;background:#fafafa;border-radius:6px;padding:10px;font-size:.82rem}
    .detail-panel table{box-shadow:none;border-radius:4px;margin:0}
    .detail-panel th{font-size:.75rem;padding:7px 10px}
    .detail-panel td{padding:7px 10px;font-size:.82rem}
  </style>
</head>
<body>
<header>
  <h1>🍕 La Fornace — Pannello Admin</h1>
  <a href="?logout=1">Esci</a>
</header>

<?php
$ordini = $pdo ? $pdo->query("
  SELECT o.*, c.nome AS cli_nome, c.cognome AS cli_cognome, c.telefono,
         COUNT(d.id_dettaglio) AS num_pizze
  FROM ordini o
  JOIN clienti c ON o.id_cliente = c.id_cliente
  LEFT JOIN dettagli_ordine d ON o.id_ordine = d.id_ordine
  GROUP BY o.id_ordine
  ORDER BY o.created_at DESC LIMIT 50
")->fetchAll() : [];

$stats = $pdo ? $pdo->query("
  SELECT COUNT(*) AS totale,
         SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) AS oggi,
         IFNULL(SUM(totale),0) AS fatturato,
         SUM(CASE WHEN stato='ricevuto' THEN 1 ELSE 0 END) AS in_attesa
  FROM ordini
")->fetch() : ['totale'=>0,'oggi'=>0,'fatturato'=>0,'in_attesa'=>0];
?>

<div class="container">
  <div class="stats">
    <div class="stat-card"><strong><?= $stats['totale'] ?></strong><span>Ordini Totali</span></div>
    <div class="stat-card"><strong><?= $stats['oggi'] ?></strong><span>Ordini Oggi</span></div>
    <div class="stat-card"><strong>€<?= number_format($stats['fatturato'],2,',','.') ?></strong><span>Fatturato</span></div>
    <div class="stat-card"><strong><?= $stats['in_attesa'] ?></strong><span>In Attesa</span></div>
  </div>

  <h2>📋 Ultimi Ordini</h2>
  <table>
    <thead>
      <tr><th>#</th><th>Cliente</th><th>Tel</th><th>Tipo</th><th>Pizze e Impasti</th><th>Totale</th><th>Ora</th><th>Stato</th><th>Azione</th></tr>
    </thead>
    <tbody>
    <?php foreach ($ordini as $o):
      // Carica dettagli ordine
      $dettagli = $pdo ? $pdo->prepare("SELECT nome_pizza, impasto, quantita, prezzo_unitario, subtotale FROM dettagli_ordine WHERE id_ordine = ?") : null;
      if ($dettagli) { $dettagli->execute([$o['id_ordine']]); $items = $dettagli->fetchAll(); }
      else { $items = []; }
    ?>
      <tr>
        <td><strong>#<?= $o['id_ordine'] ?></strong></td>
        <td><?= htmlspecialchars($o['cli_nome'].' '.$o['cli_cognome']) ?></td>
        <td><?= htmlspecialchars($o['telefono']) ?></td>
        <td><?= $o['tipo_ordine']==='consegna' ? '🛵 Consegna' : '🏃 Asporto' ?></td>
        <td>
          <!-- Impasti per pizza -->
          <div class="impasto-row">
            <?php foreach($items as $it): ?>
              <span class="impasto-chip" title="<?= htmlspecialchars($it['impasto']) ?>">
                🍕 <?= htmlspecialchars($it['nome_pizza']) ?> x<?= $it['quantita'] ?>
                · 🌾 <?= htmlspecialchars($it['impasto']) ?>
              </span>
            <?php endforeach; ?>
          </div>
          <button class="toggle-detail" onclick="toggleDetail(this, <?= $o['id_ordine'] ?>)">▼ Dettaglio</button>
          <div class="detail-panel" id="detail-<?= $o['id_ordine'] ?>">
            <table>
              <thead><tr><th>Pizza</th><th>Impasto</th><th>Q.tà</th><th>Prezzo</th><th>Subtotale</th></tr></thead>
              <tbody>
                <?php foreach($items as $it): ?>
                <tr>
                  <td><?= htmlspecialchars($it['nome_pizza']) ?></td>
                  <td><?= htmlspecialchars($it['impasto']) ?></td>
                  <td><?= $it['quantita'] ?></td>
                  <td>€<?= number_format($it['prezzo_unitario'],2,',','') ?></td>
                  <td>€<?= number_format($it['subtotale'],2,',','') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </td>
        <td><strong>€<?= number_format($o['totale'],2,',','') ?></strong></td>
        <td><?= date('H:i d/m', strtotime($o['created_at'])) ?></td>
        <td><span class="badge badge-<?= $o['stato'] ?>"><?= ucfirst($o['stato']) ?></span></td>
        <td>
          <form method="POST" style="display:flex;gap:5px;align-items:center;">
            <input type="hidden" name="id_ordine" value="<?= $o['id_ordine'] ?>">
            <select name="stato" class="stato-sel">
              <?php foreach(['ricevuto','preparazione','pronto','consegnato','annullato'] as $s): ?>
                <option value="<?=$s?>" <?=$o['stato']===$s?'selected':''?>><?=ucfirst($s)?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" name="update_stato" class="btn-upd">✓</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(empty($ordini)): ?>
      <tr><td colspan="9" style="text-align:center;color:#999;padding:32px;">Nessun ordine ancora</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
function toggleDetail(btn, id) {
  const panel = document.getElementById('detail-' + id);
  const open  = panel.style.display === 'block';
  panel.style.display = open ? 'none' : 'block';
  btn.textContent = open ? '▼ Dettaglio' : '▲ Chiudi';
}
</script>
</body>
</html>
