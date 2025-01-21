const collapsibles = document.getElementsByClassName("collapsible");
const descriptions = document.getElementsByClassName("dropdown");

for(let i = 0; i < collapsibles.length; i++){
    collapsibles[i].addEventListener("click", function(){
        collapsibles[i].classList.toggle("active")
        descriptions[i].classList.toggle("collapsed");
    })
}



