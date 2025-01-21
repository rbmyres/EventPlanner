const collapsibles = document.getElementsByClassName("collapsible");
const descriptions = document.getElementsByClassName("description");
const searchInput = document.getElementById("searchBar");

for(let i = 0; i < collapsibles.length; i++){
    collapsibles[i].addEventListener("click", function(){
        collapsibles[i].classList.toggle("active")
        descriptions[i].classList.toggle("collapsed");
    })
}

document.addEventListener("DOMContentLoaded", () => {
    // Toggle Notifications Dropdown
    const notificationButton = document.getElementById("notificationButton");
    const notificationDropdown = document.getElementById("notificationDropdown");

    notificationButton.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent dropdown from closing when clicking the button
        notificationDropdown.classList.toggle("hidden");
    });

    document.addEventListener("click", () => {
        notificationDropdown.classList.add("hidden");
    });

    // Edit User Name
    const editNameButton = document.getElementById("editNameButton");
    const saveNameButton = document.getElementById("saveNameButton");
    const editNameInput = document.getElementById("editNameInput");
    const nameContainer = document.getElementById("name-container");
    const editNameContainer = document.getElementById("edit-name-container");
    const userName = document.getElementById("userName");

    editNameButton.addEventListener("click", () => {
        nameContainer.classList.add("hidden");
        editNameContainer.classList.remove("hidden");
    });

    saveNameButton.addEventListener("click", () => {
        userName.textContent = editNameInput.value;
        nameContainer.classList.remove("hidden");
        editNameContainer.classList.add("hidden");
    });
});

// Search bar

searchInput.addEventListener("input", (e) =>{
    const value = e.target.value;
    console.log(value);
})


