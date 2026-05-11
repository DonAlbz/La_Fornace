# TOPING
Progetto per CLIL di GPOI
System Requirements
    • XAMPP >= 8.0 (Apache + PHP 8.x + MySQL 8.x)
    • Browser  (Chrome, Firefox, Edge)
    • Internet connection (to load GSAP and Google Fonts from CDN)
    • Disk space: ~5 MB
File Structure
After extracting the archive, the folder structure should be:
la_fornace/
├── index.php                 ← Pagina principale
├── css/
│   └── style.css             ← Tutti gli stili
├── js/
│   └── main.js               ← Animazioni, carrello, AJAX
├── php/
│   ├── config.php            ← Connessione PDO al database
│   ├── ordine.php            ← Endpoint ricezione ordini
│   └── admin.php             ← Pannello amministratore
└── sql/
    └── la_fornace.sql        ← Script creazione DB

Step 1 — Place the files
 • Start XAMPP Control Panel → Start Apache and MySQL
• Copy the 'la_fornace' folder to: C:xampphtdocs (Windows) 

 Step 2 — Create the Database
    • Open the browser and navigate to: http://localhost/phpmyadmin
    • Click on 'Import' (top tab)
    • Select the file: la_fornace/sql/la_fornace.sql
    • Click 'Run' — the following will be created automatically: database, all 6 tables


 Step 3 — Start the Website
    • Open the browser and navigate to: http://localhost/la_fornace/
    • The site will load the pizzas directly from the database
      
