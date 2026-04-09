<?php
include "db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errore = "";
$mostra_recupero = false;
$data_max_16 = date('Y-m-d', strtotime('-16 years'));
$data_oggi = date('Y-m-d'); // Impedisce date oltre oggi
$form_attivo = "login"; // Default

// LOGICA REGISTRAZIONE
if(isset($_POST['register'])){
    $form_attivo = "register"; // Se c'è un errore, resta su registrazione
    // GENERAZIONE AUTOMATICA USERNAME
$username_base = strtolower($nome . "." . $cognome);
$username_base = preg_replace('/[^a-z0-9\.]/', '', $username_base); // pulizia caratteri

$username = $username_base;
$counter = 1;

// Controllo se esiste già
while (true) {
    $check = mysqli_query($conn, "SELECT id FROM utenti WHERE username = '$username'");
    if (mysqli_num_rows($check) == 0) break; // username libero
    $username = $username_base . $counter;   // aggiunge numero
    $counter++;
}
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $data_nascita = $_POST['data_nascita'];

    $oggi = new DateTime();
    $nascita = new DateTime($data_nascita);
    $diff = $oggi->diff($nascita);
    $eta = $diff->y;

    if($eta < 16) {
        $errore = "Spiacenti, devi avere almeno 16 anni per registrarti.";
    } else {
        $check_email = "SELECT id FROM utenti WHERE email = '$email'";
        $res = mysqli_query($conn, $check_email);

        if(mysqli_num_rows($res) > 0) {
            $errore = "È già presente un account con questa email.";
            $mostra_recupero = true;
        } else {
            $sql = "INSERT INTO utenti(nome, cognome, email, password, data_nascita, username)
        VALUES('$nome', '$cognome', '$email', '$password', '$data_nascita', '$username')";
            
            if(mysqli_query($conn, $sql)){
                $_SESSION['id'] = mysqli_insert_id($conn);
                header("Location: home.php");
                exit();
            }
        }
    }
}

// LOGICA LOGIN
if(isset($_POST['login'])){
    $form_attivo = "login";
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM utenti WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['id'] = $user['id'];
        header("Location: home.php");
        exit();
    } else {
        $errore = "Email o Password errati.";
    }
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Social Access</title>
    <link rel="stylesheet" href="css/style.css?v=1.1">
</head>
<body>
<div class="container">
    <div class="form-box" id="formBox">
        
      

        <div class="form-container login-form" id="loginForm">
            <h1>Bentornato</h1>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button name="login">Accedi</button>
            </form>
            <p>Non hai un account? <a href="javascript:void(0)" onclick="toggleForm()">Registrati</a></p>
        </div>

        <div class="form-container register-form" id="registerForm" style="display: none;">
            <h1>Crea Account</h1>
            <form method="POST">
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="cognome" placeholder="Cognome" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="date" name="data_nascita" max="<?php echo $data_oggi; ?>" required>
                <button name="register">Registrati</button>
            </form>
            <p>Hai già un account? <a href="javascript:void(0)" onclick="toggleForm()">Accedi</a></p>
            <?php if($errore != ""): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #f5c6cb; font-size: 13px;">
                <?php echo $errore; ?>
                <?php if($mostra_recupero): ?>
                    <a href="recover_password.php" style="color:#721c24; font-weight:bold;">Recuperalo</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Se PHP ci dice che il form attivo deve essere register, lo cambiamo all'avvio
<?php if($form_attivo == "register"): ?>
    document.getElementById('loginForm').style.display = "none";
    document.getElementById('registerForm').style.display = "block";
<?php endif; ?>

function toggleForm() {
    const login = document.getElementById('loginForm');
    const register = document.getElementById('registerForm');
    const box = document.getElementById('formBox');

    box.style.transform = "rotateY(90deg)";
    box.style.opacity = "0";

    setTimeout(() => {
        if (login.style.display === "none") {
            login.style.display = "block";
            register.style.display = "none";
        } else {
            login.style.display = "none";
            register.style.display = "block";
        }
        box.style.transform = "rotateY(0deg)";
        box.style.opacity = "1";
    }, 300);
}
</script>
</body>
</html>