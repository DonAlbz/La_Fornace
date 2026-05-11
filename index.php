<?php
require_once 'php/config.php';


$pizze = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT p.*, c.nome AS categoria_nome FROM pizze p 
                         JOIN categorie c ON p.id_categoria = c.id_categoria 
                         WHERE p.disponibile = 1 ORDER BY c.id_categoria");
    $pizze = $stmt->fetchAll();
}

$impasti = [];
try {
  $stmt2   = $pdo->query("SELECT nome, gluten_free, costo_extra FROM impasti WHERE disponibile = 1 ORDER BY id_impasto");
  $impasti = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $impasti = [
    ['nome'=>'Tonda Tradizionale',  'gluten_free'=>0, 'costo_extra'=>0.00],
    ['nome'=>'Integrale',            'gluten_free'=>0, 'costo_extra'=>0.50],
    ['nome'=>'Lunga Lievitazione',   'gluten_free'=>0, 'costo_extra'=>1.00],
    ['nome'=>'Farro e Semola',       'gluten_free'=>0, 'costo_extra'=>0.80],
    ['nome'=>'Senza Glutine',        'gluten_free'=>1, 'costo_extra'=>1.50],
  ];
}

$emoji_map = [
  'Margherita'=>'🍕','Diavola'=>'🌶️','Quattro Stagioni'=>'🍄','Napoletana'=>'🧄',
  'Bufala e Datterini'=>'🫙','Nduja e Stracciatella'=>'🔥','Tartufo Nero'=>'⚫',
  'Marinara'=>'🌿','Ortolana'=>'🥗','Prosciutto e Rucola'=>'🥩',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>La Fiamma Fresca di Brescia — Pizzeria d'Asporto</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav>
  <a href="#hero" class="nav-logo">Fiamma <span>Fresca</span></a>
  <ul class="nav-links">
    <li><a href="#impasti">Impasti</a></li>
    <li><a href="#menu">Menu</a></li>
    <li><a href="#chi-siamo">Chi Siamo</a></li>
    <li><a href="#ordina" class="nav-cta">Ordina Ora</a></li>
    <li><a href="#ordina" id="cart-btn">🛒 Carrello <span id="cart-count">0</span></a></li>
  </ul>
</nav>

<!-- ===== HERO ===== -->
<section id="hero">
  <div class="flour-circle"></div>
  <div class="flour-circle"></div>
  <div class="flour-circle"></div>
  <div class="hero-bg-pattern"></div>
  <div class="hero-content">
    <div class="hero-badge">Fiamma · Fresca</div>
    <h1>La Pizza<br><em>Vera</em> Artigianale</h1>
    <p>Cinque impasti tradizionali, sceglibili pizza per pizza. Ingredienti selezionati, forno a legna. Pronto in 20 minuti.</p>
    <div class="hero-btns">
      <a href="#menu" class="btn-primary">Sfoglia il Menu</a>
      <a href="#impasti" class="btn-secondary">Scopri gli Impasti</a>
    </div>
  </div>
</section>

<!-- ===== IMPASTI VETRINA ===== -->
<section id="impasti">
  <div class="section-header">
    <span class="section-label">L'arte della farina</span>
    <h2>I Nostri <em>5 Impasti</em></h2>
    <div class="divider"></div>
    <p style="color:#777;margin-top:16px;max-width:560px;margin-left:auto;margin-right:auto;">
      Ogni pizza del tuo ordine può avere un impasto diverso. Scegli direttamente sulla card!
    </p>
  </div>

  <div class="impasti-grid">
    <div class="impasto-card ">
      <span class="impasto-icon">🌾</span>
      <h3>Tonda Tradizionale</h3>
      <p>Farina 00, 24h di lievitazione. Il cornicione alto e soffice della tradizione.</p>
      <span class="impasto-tag">Originale</span>
    </div>
    <div class="impasto-card">
      <span class="impasto-icon">🌿</span>
      <h3>Integrale</h3>
      <p>Farina integrale macinata a pietra, ricca di fibre. Sapore rustico e autentico.</p>
      <span class="impasto-tag">+€0.50</span>
    </div>
    <div class="impasto-card">
      <span class="impasto-icon">⏰</span>
      <h3>Lunga Lievitazione</h3>
      <p>72 ore di fermentazione lenta. Altamente digeribile, croccante fuori e morbido dentro.</p>
      <span class="impasto-tag">+€1.00</span>
    </div>
    <div class="impasto-card ">
      <span class="impasto-icon">🟤</span>
      <h3>Farro e Semola</h3>
      <p>Blend di farro antico e semola rimacinata. Sapore nocciolato e consistenza unica.</p>
      <span class="impasto-tag">+€0.80</span>
    </div>
    <div class="impasto-card">
      <span class="impasto-icon">🟡</span>
      <h3>Senza Glutine</h3>
      <p>Mix di riso e mais certificato. Friabile e gustoso, adatto a chi è celiaco.</p>
      <span class="impasto-tag">+€1.50 · GF</span>
    </div>
  </div>

</section>

<!-- ===== MENU ===== -->
<section id="menu">
  <div class="section-header">
    <span class="section-label">Le nostre specialità</span>
    <h2>Il Nostro <em>Menu</em></h2>
    <div class="divider"></div>
  </div>

  <div class="filter-bar">
    <button class="filter-btn active" data-filter="all">Tutte</button>
    <button class="filter-btn" data-filter="classica">Classiche</button>
    <button class="filter-btn" data-filter="gourmet">Gourmet</button>
    <button class="filter-btn" data-filter="vegana">Vegane</button>
    <button class="filter-btn" data-filter="bianca">Bianche</button>
  </div>

  <div class="pizze-grid">
    <?php foreach ($pizze as $pizza):
      $emoji = $emoji_map[$pizza['nome']] ?? '🍕';
      $bs    = $pizza['bestseller'] ? '<span class="pizza-badge">⭐ Best Seller</span>' : '';
    ?>
    <div class="pizza-card" data-cat="<?= htmlspecialchars($pizza['categoria_nome']) ?>">
      <div class="pizza-img"><?= $emoji ?></div>
      <div class="pizza-info">
        <?= $bs ?>
        <h3><?= htmlspecialchars($pizza['nome']) ?></h3>
        <p><?= htmlspecialchars($pizza['descrizione']) ?></p>

        <!-- SELECT IMPASTO per questa pizza -->
        <div class="impasto-select-wrapper">
          <span class="impasto-select-label">🌾 Scegli l'impasto</span>
          <select class="impasto-select" aria-label="Impasto per <?= htmlspecialchars($pizza['nome']) ?>">
            <?php foreach ($impasti as $imp):
              $extra = $imp['costo_extra'] > 0 ? ' (+€' . number_format($imp['costo_extra'], 2, ',', '') . ')' : '';
              $gf    = $imp['gluten_free'] ? ' ✓GF' : '';
            ?>
            <option value="<?= htmlspecialchars($imp['nome']) ?>">
              <?= htmlspecialchars($imp['nome']) . $extra . $gf ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="pizza-footer">
          <span class="pizza-price">€<?= number_format($pizza['prezzo'], 2, ',', '') ?></span>
          <button class="add-btn"
            onclick="addToCart('<?= addslashes($pizza['nome']) ?>', <?= $pizza['prezzo'] ?>, this.closest('.pizza-card'))">
            + Aggiungi
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ===== CHI SIAMO ===== -->
<section id="chi-siamo">
  <div class="about-grid">
    <div class="about-text">
      <span class="section-label">La nostra storia</span>
      <h2>Artigiani della <em style="color:var(--rosso);font-style:italic;">Pizza</em> </h2>

    <p>  Pizzeria asporto dove la fiamma viva esalta la freschezza delle materie prime italiane, in un abbraccio di gusto autentico e passione artigianale. Un'idea semplice ma rivoluzionaria: pizza vera, preparata a mano con ingredienti di altissima qualità, per chi cerca sapore genuino da asporto, sempre croccante e soffice.
Noi crediamo nelle materie prime italiane di altissima qualità, partendo dalle farine biologiche italiane macinate a pietra.
Tutte le materie prime sono ricercate accuratamente tra quelle di prima qualità e prettamente italiane.</p>
      <p>
Offriamo vari impasti, preparati con mani appassionate, a lunga lievitazione e valorizzati dalle scelte di ingredienti artigianali: sinonimo di autenticità e freschezza.</p>
      <div class="about-stats">
        <div class="stat"><strong class="icon-stat">🍕🔥</strong><span>Forno a legna</span></div>
        <div class="stat"><strong data-target="5">0</strong><span>Impasti unici</span></div>
      </div>
    </div>
    <div class="about-visual fade-in">🍕</div>
  </div>
</section>

<!-- ===== ORDINA ===== -->
<section id="ordina">
  <div class="section-header">
    <span class="section-label">Asporto e consegna</span>
    <h2 style="color:white;">Ordina <em>Ora</em></h2>
    <div class="divider"></div>
    <p style="color:#aaa;margin-top:14px;font-size:0.9rem;">
      Ogni pizza nel carrello mostra l'impasto che hai scelto. Puoi avere impasti diversi nello stesso ordine!
    </p>
  </div>

  <div class="order-layout">
    <!-- Form -->
    <div class="order-form-wrapper">
      <h3 style="color:white;font-family:'Playfair Display',serif;font-size:1.35rem;margin-bottom:22px;">Dati per l'Ordine</h3>
      <form id="order-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="nome" required placeholder="Mario">
          </div>
          <div class="form-group">
            <label>Cognome *</label>
            <input type="text" name="cognome" required placeholder="Rossi">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Telefono *</label>
            <input type="tel" name="telefono" required placeholder="+39 333 1234567">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="mario@email.com">
          </div>
        </div>
        <div class="form-group">
          <label>Tipo ordine *</label>
          <select name="tipo_ordine" onchange="toggleDelivery(this.value)">
            <option value="asporto">🏃 Ritiro in negozio</option>
            <option value="consegna">🛵 Consegna a domicilio (+€1.50)</option>
          </select>
        </div>
        <div id="delivery-fields" style="display:none;">
          <div class="form-group">
            <label>Indirizzo di consegna *</label>
            <input type="text" name="indirizzo" placeholder="Via Roma 10, Brescia">
          </div>
        </div>
        <div class="form-group">
          <label>Orario desiderato</label>
          <input type="time" name="orario" min="12:00" max="22:30">
        </div>
        <div class="form-group">
          <label>Note aggiuntive</label>
          <textarea name="note" placeholder="Allergie, richieste speciali, citofono..."></textarea>
        </div>
        <button type="submit" class="submit-btn">🍕 Conferma Ordine</button>
      </form>
    </div>

    <!-- Carrello -->
    <div class="cart-sidebar">
      <h3>🛒 Il tuo Carrello</h3>
      <div id="cart-items">
        <div class="cart-empty">Il carrello è vuoto</div>
      </div>
      <div class="cart-total">
        <div class="cart-total-row"><span>Subtotale</span><span id="cart-subtotal">€0.00</span></div>
        <div class="cart-total-row"><span>Consegna</span><span id="cart-delivery">Gratis</span></div>
        <div class="cart-total-row final"><span>Totale</span><span id="cart-total">€0.00</span></div>
      </div>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <a href="#hero" class="nav-logo">Fiamma <span>Fresca</span></a>
      <p>Tre generazioni di pizzaioli napoletani. La vera pizza artigianale con impasto a tua scelta, pronta per te.</p>
    </div>
    <div>
      <h4>Navigazione</h4>
      <ul>
        <li><a href="#impasti">I Nostri Impasti</a></li>
        <li><a href="#menu">Il Menu</a></li>
        <li><a href="#chi-siamo">Chi Siamo</a></li>
        <li><a href="#ordina">Ordina Ora</a></li>
      </ul>
    </div>
    <div>
      <h4>Contatti</h4>
      <ul>
        <li>📍 Via Toledo 45, Brescia</li>
        <li>📞 081 123 4567</li>
        <li>⏰ Lun–Dom 12:00–23:00</li>
        <li>🛵 Consegna entro 5 km</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 Fiamma Fresca di Brescia — P.IVA 01234567890</p>
  </div>
</footer>

<!-- ===== MODAL SUCCESSO ===== -->
<div id="success-modal" class="modal-overlay">
  <div class="modal">
    <div class="check">✅</div>
    <h2>Ordine Confermato!</h2>
    <p>Il tuo ordine <strong id="ordine-id"></strong> è stato ricevuto.<br>Prepariamo le tue pizze con gli impasti scelti. Ti contatteremo al più presto!</p>
    <button class="modal-close" onclick="closeModal()">Ottimo, grazie!</button>
  </div>
</div>

<div id="boxing-anim" class="boxing-animation-wrap">
  <div class="box-container">
    <div class="pizza-box"></div>
    <div class="anim-pizza">🍕</div>
    <div class="box-lid">LA FORNACE</div>
  </div>
</div>

<script src="js/main.js"></script>
</body>
</html>
