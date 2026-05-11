<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success'=>false,'message'=>'Metodo non consentito']); exit;
}

$nome      = trim($_POST['nome']      ?? '');
$cognome   = trim($_POST['cognome']   ?? '');
$telefono  = trim($_POST['telefono']  ?? '');
$email     = trim($_POST['email']     ?? '');
$tipo      = trim($_POST['tipo_ordine'] ?? 'asporto');
$indirizzo = trim($_POST['indirizzo'] ?? '');
$orario    = trim($_POST['orario']    ?? '');
$note      = trim($_POST['note']      ?? '');
$cartJson  = $_POST['cart']  ?? '[]';
$totale    = floatval($_POST['total'] ?? 0);

if (empty($nome) || empty($cognome) || empty($telefono)) {
  echo json_encode(['success'=>false,'message'=>'Compila tutti i campi obbligatori']); exit;
}
if ($tipo === 'consegna' && empty($indirizzo)) {
  echo json_encode(['success'=>false,'message'=>"Inserisci l'indirizzo di consegna"]); exit;
}

$cart = json_decode($cartJson, true);
if (empty($cart)) {
  echo json_encode(['success'=>false,'message'=>'Carrello vuoto']); exit;
}

// Modalità demo (DB non configurato)
if ($pdo === null) {
  echo json_encode(['success'=>true,'ordine_id'=>rand(1000,9999),'demo'=>true]); exit;
}

try {
  $pdo->beginTransaction();

  // 1. Inserisci o recupera cliente
  $stmt = $pdo->prepare("SELECT id_cliente FROM clienti WHERE telefono = ?");
  $stmt->execute([$telefono]);
  $cliente = $stmt->fetch();

  if ($cliente) {
    $id_cliente = $cliente['id_cliente'];
    if (!empty($email)) {
      $pdo->prepare("UPDATE clienti SET email=? WHERE id_cliente=?")->execute([$email,$id_cliente]);
    }
  } else {
    $pdo->prepare("INSERT INTO clienti (nome,cognome,telefono,email,indirizzo) VALUES (?,?,?,?,?)")
        ->execute([$nome,$cognome,$telefono,$email,$indirizzo]);
    $id_cliente = $pdo->lastInsertId();
  }

  // 2. Crea ordine (nessun campo impasto_scelto globale: ora è per riga)
  $pdo->prepare("
    INSERT INTO ordini (id_cliente, tipo_ordine, indirizzo_consegna, orario_richiesto, note, totale, stato)
    VALUES (?, ?, ?, ?, ?, ?, 'ricevuto')
  ")->execute([
    $id_cliente,
    $tipo,
    $indirizzo ?: null,
    $orario    ?: null,
    $note      ?: null,
    $totale
  ]);
  $id_ordine = $pdo->lastInsertId();

  // 3. Inserisci dettagli — ogni riga ha il proprio impasto
  $stmt3 = $pdo->prepare("
    INSERT INTO dettagli_ordine (id_ordine, nome_pizza, impasto, prezzo_unitario, quantita)
    VALUES (?, ?, ?, ?, ?)
  ");
  foreach ($cart as $item) {
    $stmt3->execute([
      $id_ordine,
      $item['name'],
      $item['impasto'],          // impasto specifico per questa pizza
      floatval($item['price']),
      intval($item['qty'])
    ]);
  }

  $pdo->commit();
  echo json_encode(['success'=>true,'ordine_id'=>$id_ordine]);

} catch (Exception $e) {
  $pdo->rollBack();
  error_log("Ordine error: ".$e->getMessage());
  echo json_encode(['success'=>false,'message'=>'Errore interno. Riprova o chiamaci.']);
}
