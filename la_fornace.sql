CREATE DATABASE IF NOT EXISTS la_fornace
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE la_fornace;

-- ============================================================
-- TABELLA 1: categorie
-- ============================================================
CREATE TABLE IF NOT EXISTS categorie (
  id_categoria   INT AUTO_INCREMENT PRIMARY KEY,
  nome           VARCHAR(50)  NOT NULL UNIQUE,
  descrizione    TEXT,
  created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categorie (nome, descrizione) VALUES
  ('classica',  'Le pizze tradizionali della cucina napoletana'),
  ('gourmet',   'Pizze con ingredienti ricercati e abbinamenti creativi'),
  ('vegana',    'Pizze senza ingredienti di origine animale'),
  ('bianca',    'Pizze senza pomodoro con base crema o olio');

-- ============================================================
-- TABELLA 2: pizze
-- ============================================================
CREATE TABLE IF NOT EXISTS pizze (
  id_pizza       INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria   INT            NOT NULL,
  nome           VARCHAR(100)   NOT NULL,
  descrizione    TEXT,
  prezzo         DECIMAL(5,2)   NOT NULL,
  disponibile    TINYINT(1)     DEFAULT 1,
  bestseller     TINYINT(1)     DEFAULT 0,
  created_at     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_categoria) REFERENCES categorie(id_categoria) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO pizze (id_categoria, nome, descrizione, prezzo, bestseller) VALUES
  -- Classiche
  (1, 'Margherita',          'Pomodoro San Marzano, fior di latte, basilico fresco, EVO',                  7.50, 1),
  (1, 'Diavola',             'Pomodoro San Marzano, mozzarella, salame piccante calabrese',                9.00, 0),
  (1, 'Quattro Stagioni',    'Pomodoro, mozzarella, prosciutto cotto, funghi, olive, carciofi',           10.50, 0),
  (1, 'Capricciosa',         'Pomodoro, mozzarella, prosciutto, funghi, carciofini, uovo',                10.50, 0),
  (1, 'Napoletana',          'Pomodoro San Marzano, acciughe del Cantabrico, capperi, origano',            9.00, 0),
  -- Gourmet
  (2, 'Bufala e Datterini',  'Pomodorini datterini, mozzarella di bufala DOP, basilico, EVO',             12.00, 1),
  (2, "Nduja e Stracciatella",'Fiordilatte, nduja calabrese, stracciatella, cipolla rossa di Tropea',     13.00, 0),
  (2, 'Tartufo Nero',        'Crema di tartufo, mozzarella, funghi porcini, scaglie di tartufo nero',     15.00, 0),
  (2, 'Salmone e Rucola',    'Crema di zucchine, mozzarella, salmone affumicato, rucola, stracchino',     13.50, 0),
  -- Vegane
  (3, 'Marinara',            'Pomodoro San Marzano, aglio, origano selvatico, EVO (senza formaggio)',      6.00, 0),
  (3, 'Ortolana',            'Verdure grigliate di stagione, pomodoro, vegan mozzarella',                 10.00, 0),
  -- Bianche
  (4, 'Prosciutto e Rucola', 'Base crema, mozzarella, prosciutto crudo DOP, rucola, grana',              11.50, 0),
  (4, 'Patate e Rosmarino',  'Base olio, mozzarella, patate a fette, rosmarino, sale integrale',           9.50, 0);

-- ============================================================
-- TABELLA 3: clienti
-- ============================================================
CREATE TABLE IF NOT EXISTS clienti (
  id_cliente     INT AUTO_INCREMENT PRIMARY KEY,
  nome           VARCHAR(80)   NOT NULL,
  cognome        VARCHAR(80)   NOT NULL,
  telefono       VARCHAR(20)   NOT NULL UNIQUE,
  email          VARCHAR(120),
  indirizzo      TEXT,
  note_cliente   TEXT,
  created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_telefono (telefono)
) ENGINE=InnoDB;

-- ============================================================
-- TABELLA 4: ordini
-- ============================================================
CREATE TABLE IF NOT EXISTS ordini (
  id_ordine          INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente         INT          NOT NULL,
  tipo_ordine        ENUM('asporto','consegna')  DEFAULT 'asporto',
  indirizzo_consegna TEXT,
  orario_richiesto   TIME,
  impasto_scelto     VARCHAR(80)  DEFAULT 'Classica Napoletana',
  note               TEXT,
  totale             DECIMAL(7,2) NOT NULL DEFAULT 0.00,
  stato              ENUM('ricevuto','preparazione','pronto','consegnato','annullato')
                                  DEFAULT 'ricevuto',
  created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_cliente) REFERENCES clienti(id_cliente) ON DELETE RESTRICT,
  INDEX idx_stato    (stato),
  INDEX idx_created  (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- TABELLA 5: dettagli_ordine
-- ============================================================
CREATE TABLE IF NOT EXISTS dettagli_ordine (
  id_dettaglio   INT AUTO_INCREMENT PRIMARY KEY,
  id_ordine      INT           NOT NULL,
  nome_pizza     VARCHAR(100)  NOT NULL,
  impasto        VARCHAR(80)   DEFAULT 'Classica Napoletana',
  prezzo_unitario DECIMAL(5,2) NOT NULL,
  quantita       INT           NOT NULL DEFAULT 1,
  subtotale      DECIMAL(7,2)  GENERATED ALWAYS AS (prezzo_unitario * quantita) STORED,
  FOREIGN KEY (id_ordine) REFERENCES ordini(id_ordine) ON DELETE CASCADE,
  INDEX idx_ordine (id_ordine)
) ENGINE=InnoDB;

-- ============================================================
-- TABELLA 6 (bonus): impasti  — catalogo impasti
-- ============================================================
CREATE TABLE IF NOT EXISTS impasti (
  id_impasto   INT AUTO_INCREMENT PRIMARY KEY,
  nome         VARCHAR(80)  NOT NULL,
  descrizione  TEXT,
  lievitazione VARCHAR(40),
  gluten_free  TINYINT(1)   DEFAULT 0,
  costo_extra  DECIMAL(4,2) DEFAULT 0.00,
  disponibile  TINYINT(1)   DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO impasti (nome, descrizione, lievitazione, gluten_free, costo_extra) VALUES
  ('Classica Napoletana',  'Farina 00 macinata fine, tradizione napoletana',            '24h', 0, 0.00),
  ('Integrale',            'Farina integrale macinata a pietra, ricca di fibre',         '24h', 0, 0.50),
  ('Lunga Lievitazione',   'Fermentazione lenta 72h, altamente digeribile',             '72h', 0, 1.00),
  ('Farro e Semola',       'Blend di farro antico e semola rimacinata di grano duro',   '48h', 0, 0.80),
  ('Senza Glutine',        'Mix certificato riso e mais, adatto ai celiaci',            '24h', 1, 1.50);

-- ============================================================
-- VISTE UTILI
-- ============================================================

-- Vista ordini completi
CREATE OR REPLACE VIEW v_ordini_completi AS
SELECT
  o.id_ordine,
  CONCAT(c.nome, ' ', c.cognome) AS cliente,
  c.telefono,
  o.tipo_ordine,
  o.impasto_scelto,
  o.totale,
  o.stato,
  o.created_at,
  COUNT(d.id_dettaglio) AS num_pizze
FROM ordini o
JOIN clienti c ON o.id_cliente = c.id_cliente
LEFT JOIN dettagli_ordine d ON o.id_ordine = d.id_ordine
GROUP BY o.id_ordine;

-- Vista pizza 
CREATE OR REPLACE VIEW v_pizze_top AS
SELECT
  d.nome_pizza,
  COUNT(*) AS volte_ordinata,
  SUM(d.subtotale) AS ricavo_totale
FROM dettagli_ordine d
GROUP BY d.nome_pizza
ORDER BY volte_ordinata DESC;
