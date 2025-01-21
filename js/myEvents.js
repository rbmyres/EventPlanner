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

const searchInput = document.getElementById("searchBar");
const select = document.querySelector("select");
const eventBoxes = document.getElementsByClassName("eventBox");
const eventNames = document.getElementsByClassName("eventName");
const managerNames = document.getElementsByClassName("managerName");
const attendeeNum = document.getElementsByClassName("numAttendees");
const attendeeCapacity = document.getElementsByClassName("totalAttendees");
const workerNum = document.getElementsByClassName("numWorkers");
const workerCapacity = document.getElementsByClassName("totalWorkers");
const positions = document.getElementsByClassName("position");

// Function to apply filters based on both search and select dropdown
function applyFilters() {
    const searchValue = searchInput.value.toLowerCase();
    const selected = select.value;

    for (let i = 0; i < eventBoxes.length; i++) {
        const eventNameText = eventNames[i].textContent.toLowerCase();
        const managerNameText = managerNames[i].textContent.toLowerCase();
        const numAttendees = parseInt(attendeeNum[i].textContent, 10);
        const totalAttendees = parseInt(attendeeCapacity[i].textContent, 10);
        const numWorkers = parseInt(workerNum[i].textContent, 10);
        const totalWorkers = parseInt(workerCapacity[i].textContent, 10);
        let positionText = '';
        if(positions[i] !== undefined && positions[i] !== null){
            positionText = positions[i].textContent;
        }
        


        // Attendee and Worker full conditions
        const attendeeFull = numAttendees === totalAttendees;
        const workerFull = numWorkers === totalWorkers;
        const bothFull = attendeeFull || workerFull;

        // Position conditions
        let attending = true;
        let working = true;
        let managing = true;
        if(positions[i] !== undefined && positions[i] !== null){
            attending = positionText === "Attending";
            working = positionText === "Working";
            managing = positionText === "Managing"
        }


        // Check if event matches search or select filter
        const matchesSearch = eventNameText.includes(searchValue) || managerNameText.includes(searchValue);
        let matchesFilter = true;

        if (selected === "bothOption") {
            matchesFilter = !bothFull;
        } else if (selected === "attendeeOption") {
            matchesFilter = !attendeeFull;
        } else if (selected === "workerOption") {
            matchesFilter = !workerFull;
        }
        else if(selected === "attendingOption"){
            matchesFilter = attending;
        }
        else if(selected === "workingOption"){
            matchesFilter = working;
        }
        else if(selected === "managingOption"){
            matchesFilter = managing;
        }

        // Determine visibility: show event box if it matches both conditions
        const isVisible = matchesSearch && matchesFilter;
        eventBoxes[i].classList.toggle("hide", !isVisible);
    }
}

// Event listeners for search input and select dropdown
searchInput.addEventListener("input", applyFilters);
select.addEventListener("change", applyFilters);

// Initial filter application (in case there's a default value or page load)
applyFilters();

