const collapsibles = document.getElementsByClassName("collapsible");
const descriptions = document.getElementsByClassName("dropdown");
const manageButtons = document.getElementsByClassName("manageButton");

for(let i = 0; i < collapsibles.length; i++){
    collapsibles[i].addEventListener("click", function(){
        collapsibles[i].classList.toggle("active")
        descriptions[i].classList.toggle("collapsed");
    })
}

for(let i = 0; i < manageButtons.length; i++){
    manageButtons[i].addEventListener("click", function(){
        location.href = "manageEvent.html";
    })
}



