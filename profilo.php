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


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="profilo.css?v=1.1">

<style> /*
body {
    font-family: Arial;
}

/* Navbar */
.profile-container {
    width: 60px;
    height: 60px;
}
/*
#profil-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    overflow: hidden;
    background: #eee;
}

#profil-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Viewer *//*
.viewer-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    justify-content: center;
    align-items: center;
}

.viewer-content {
    text-align: center;
    color: white;
}

#full-image {
    max-width: 300px;
    border-radius: 10px;
}

.viewer-actions button {
    margin: 10px;
    padding: 10px;
    cursor: pointer;
}*/
</style>
</head>

<body>

<?php session_start(); $_SESSION['user_id'] = 1; ?>

<nav>
    <div class="profile-container">
        <button id="profil-image" onclick="viewImage()">
            <img id="display-img" style="display:none;">
            <i id="user-icon" class="fa fa-user"></i>
        </button>
    </div>
</nav>

<input type="file" id="file-input" accept="image/*" hidden onchange="updateImage(event)">

<div id="image-viewer" class="viewer-overlay">
    <div class="viewer-content">
        <span onclick="closeViewer()" style="cursor:pointer;">✖</span>

        <div>
            <img id="full-image" style="display:none;">
            <i id="full-icon" class="fa fa-user" style="font-size:100px;"></i>
        </div>

        <div class="viewer-actions">
            <button onclick="fileInput.click()">Modifica</button>
            <button onclick="removeImage()"class="fas fa-trash">Rimuovi</button>
        </div>
    </div>
</div>

<script>
const viewer = document.getElementById('image-viewer');
const displayImg = document.getElementById('display-img');
const fullImg = document.getElementById('full-image');
const fullIcon = document.getElementById('full-icon');
const userIcon = document.getElementById('user-icon');
const fileInput = document.getElementById('file-input');

function viewImage() {
    viewer.style.display = 'flex';

    const hasImage = displayImg.src && displayImg.style.display !== 'none';

    if (hasImage) {
        fullImg.src = displayImg.src;
        fullImg.style.display = 'block';
        fullIcon.style.display = 'none';
    } else {
        fullImg.style.display = 'none';
        fullIcon.style.display = 'block';
    }
}

function closeViewer() {
    viewer.style.display = 'none';
}

async function updateImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert("Max 2MB");
        return;
    }

    // preview
    const reader = new FileReader();
    reader.onload = e => {
        displayImg.src = e.target.result;
        displayImg.style.display = 'block';
        userIcon.style.display = 'none';
        fullImg.src = e.target.result;
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('foto_profilo', file);

    try {
        const res = await fetch('upload_foto.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (!data.success) alert("Errore upload");

    } catch (err) {
    console.error("Dettagli errore:", err);
        alert("Errore server");
    }
}

async function removeImage() {
    if (!confirm("Rimuovere foto?")) return;

    await fetch('remove_foto.php', { method: 'POST' });

    displayImg.style.display = 'none';
    userIcon.style.display = 'block';
    fullImg.style.display = 'none';
    fullIcon.style.display = 'block';

    closeViewer();
}
</script>

</body>
</html>