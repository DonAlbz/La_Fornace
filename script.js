function invia(){

let msg = document.getElementById("messaggio").value;

fetch("invia_messaggio.php",{

method:"POST",

headers:{
"Content-Type":"application/x-www-form-urlencoded"
},

body:"messaggio="+msg

});

}

setInterval(function(){

fetch("carica_messaggi.php")
.then(response=>response.text())
.then(data=>{
document.getElementById("chat").innerHTML=data;
})

},2000);