<?php

include "db.php";

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}
if (!isset($_SESSION['id'])) {

    header("Location: login.php");

}



$id = $_SESSION['id'];



?>





<!DOCTYPE html>

<html lang="it">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<link rel="stylesheet" href="home.css?v=1.1">

</head>



<body>







    <hr>

    <nav>

        <button id="profil-image" onclick="window.location.href='profilo.php';">

            <i class="fa fa-user"></i>

        </button>

        <div class="nav-section">

            <i class="fa-solid fa-user-group icons"></i>

            <i class="fas fa-paper-plane icons"></i>

            <i class="fas fa-bell icons"></i>

        </div>

    </nav>



    <div class="create-post">

        <img src="profile.jpg" class="profile-img" onerror="this.src='https://via.placeholder.com/40'">
<button id="profil-image" onclick="window.location.href='profilo.php';">

            <i class="fa fa-user"></i>

        </button>
        <div class="post-input-trigger" onclick="openPost()">

            A cosa stai pensando, Marco?

        </div>

    </div>



    <div class="overlay" id="overlay">

        <div class="post-modal">

            <div class="modal-header">

                <h3>Crea post</h3>

                <div class="close-btn" onclick="closePost()">✕</div>

            </div>



            <div class="modal-content">

                <div class="user-info">

                    <img src="profile.jpg" class="profile-img" onerror="this.src='https://via.placeholder.com/40'">

                    <div>

                        <button id="profil-image" onclick="window.location.href='profilo.php';">

            <i class="fa fa-user"></i>

        </button>
        <strong>Marco Salama</strong><br>

                        <form class="privacy-badge">

                            <select name="privacy">



                                <option value="solo-io">🔒 Solo io</option>



                                <option value="solo-amici">👨‍👩‍👧‍👦 Amici</option>



                                <option value="tutti">🌍 Pubblico</option>



                            </select>



                        </form>

                    </div>

                </div>



                <form action="post.php" method="POST" enctype="multipart/form-data">

                    <textarea name="contenuto" placeholder="A cosa stai pensando, Marco?" required></textarea>



                    <div class="post-tools">

                        <span>Aggiungi al tuo post</span>

                        <div class="tool-icons">

                            <label for="file-upload" class="tool-btn" title="Carica foto">

                                🖼️

                                <input type="file" id="file-upload" name="immagine" accept="image/*"

                                    style="display:none;">

                            </label>



                            <button type="button" class="tool-btn" onclick="tagFriends()" title="Tagga amici">

                                👤

                            </button>



                            <button type="button" class="tool-btn" onclick="showEmojiPicker()" title="Emoji">

                                😊

                            </button>



                            <button type="button" class="tool-btn" onclick="tagLocation()" title="Aggiungi luogo">

                                📍

                            </button>



                            <button type="button" class="tool-btn" onclick="openGifSearch()" title="Aggiungi GIF">

                                <small>GIF</small>

                            </button>

                        </div>

                    </div>



                    <button type="submit" class="publish-btn">Pubblica</button>

                </form>

            </div>

        </div>

    </div>





 <hr>

<main>
    <?php
    $sql = "SELECT post.*, utenti.nome, utenti.cognome 
            FROM post 
            JOIN utenti ON post.id_utente = utenti.id 
            ORDER BY data_post DESC";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $id_post = $row['id'];
        $id_user = $_SESSION['id'];

        $liked = mysqli_query($conn, "SELECT * FROM likes WHERE id_post = $id_post AND id_utente = $id_user");
        $ha_messo_like = mysqli_num_rows($liked) > 0;
    ?>
        <div class="post">
            <div class="user-info">
                <img src="profile.jpg" class="profile-img" onerror="this.src='https://via.placeholder.com/40'">
                <strong><?php echo $row['nome'] . " " . $row['cognome']; ?></strong>
            </div>
            
            <p style="margin: 10px 0;"><?php echo htmlspecialchars($row['contenuto']); ?></p>

            <?php
            $media = mysqli_query($conn, "SELECT * FROM media_post WHERE id_post = $id_post");
            while ($m = mysqli_fetch_assoc($media)) {
                echo "<img src='" . $m['percorso_file'] . "' class='post-media'>";
            }
            ?>

            <div class='post-actions'>
                <a href="javascript:void(0)" 
                   class="like-btn <?php echo $ha_messo_like ? 'liked' : ''; ?>" 
                   data-post-id="<?php echo $id_post; ?>"
                   onclick="toggleLike(this)">
                    <i class="<?php echo $ha_messo_like ? 'fa-solid' : 'fa-regular'; ?> fa-thumbs-up"></i> 
                    <span>Like</span>
                </a>
                <a href='commento.php?id=<?php echo $id_post; ?>'>
                    <i class='fa-regular fa-comment'></i> Commenta
                </a>
            </div>
        </div>
    <?php 
    } // fine while 
    ?>
</main>

</body>



<script>

    function openPost() {



        document.getElementById("overlay").style.display = "flex";



    }



    function closePost() {



        document.getElementById("overlay").style.display = "none";





    }



    // Gestione anteprima immagine caricata (opzionale ma utile)

    document.getElementById('file-upload').onchange = function (evt) {

        const [file] = this.files;

        if (file) {

            alert("Immagine selezionata: " + file.name);

            // Qui potresti aggiungere un codice per mostrare l'anteprima nel modal

        }

    };



    function tagFriends() {

        alert("Funzione Tagga Amici: qui potresti aprire una lista dei tuoi contatti.");

    }



    function showEmojiPicker() {

        alert("Funzione Emoji: qui potresti integrare una libreria come 'EmojiMart'.");

    }



    function tagLocation() {

        alert("Funzione Luogo: qui potresti usare le API di Google Maps o Leaflet.");

    }



    function openGifSearch() {

        alert("Funzione GIF: qui potresti collegarti alle API di Giphy.");

    }
function toggleLike(element) {
    const postId = element.getAttribute('data-post-id');
    const icon = element.querySelector('i');
    const isLiked = element.classList.contains('liked');

    // 1. Effetto Visivo Istantaneo (Ottimismo)
    element.classList.toggle('liked');
    if (element.classList.contains('liked')) {
        icon.classList.replace('fa-regular', 'fa-solid');
    } else {
        icon.classList.replace('fa-solid', 'fa-regular');
    }

    // 2. Chiamata AJAX al server
    // Creiamo una richiesta che va a like.php senza cambiare pagina
    fetch('like_ajax.php?id=' + postId)
        .then(response => response.text())
        .then(data => {
            console.log("Database aggiornato per il post: " + postId);
        })
        .catch(error => {
            // Se c'è un errore, torniamo allo stato precedente
            element.classList.toggle('liked');
            alert("Errore durante il like. Riprova.");
        });
}

</script>

</html