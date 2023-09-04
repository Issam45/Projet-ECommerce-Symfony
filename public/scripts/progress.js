const progress = document.getElementById("progress");
const stepCircles = document.querySelectorAll(".circle");
const etatTexte = document.querySelectorAll("#notSet");
let currentActive = 1;
var etat = document.getElementById("select");
var etatID = etat.selectedIndex;
const etatForm = document.getElementById("etatForm");


//NOTE CHANGER DE 1-4 POUR CHOISIR LE CERCLE
//1=25%
//2=50%
//3=75%
//4=100%

// Mettre à jour les cercles
maj(etatID + 1);

// Pour submit le form et appeler la fonction maj
etat.addEventListener("change", function () {

    etatForm.submit();
    maj(etatID + 1);

});

function maj(currentActive) {
    stepCircles.forEach((circle, i) => {
        if (i < currentActive) {
            circle.classList.remove("inactive");
            circle.classList.add("active");
            $(".circle.active").children("#icons").addClass("fas fa-check fa-2xl text-light");
        } else {
            circle.classList.remove("active");
            $(".circle.inactive").children("#icons").addClass("fas fa-close fa-xl");
        }
    });

    etatTexte.forEach((etat, i) => {
        if (i < currentActive) {
            etat.classList.add("text-primary");
        } else {
            etat.classList.remove("text-primary");
        }
    });

    // Changer la progression de la bar de progression
    const activeCircles = document.querySelectorAll(".active");
    progress.style.width =
        ((activeCircles.length - 1) / (stepCircles.length)) * 100 + "%";

}