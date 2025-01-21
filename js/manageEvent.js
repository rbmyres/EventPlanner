const editEvent = document.getElementById("editEvent");
const viewAttendees = document.getElementById("viewAttendees");
const viewWorkers = document.getElementById("viewWorkers");
const sendMessage = document.getElementById("sendMessage");
const editContent = document.getElementById("editContent");
const attendeeContent = document.getElementById("attendeeContent");
const workerContent = document.getElementById("workerContent");
const messageContent = document.getElementById("messageContent");

editEvent.addEventListener("click", function(){
    editContent.hidden = false;
    attendeeContent.hidden = true;
    workerContent.hidden = true;
    messageContent.hidden = true;
})

viewAttendees.addEventListener("click", function(){
    editContent.hidden = true;
    attendeeContent.hidden = false;
    workerContent.hidden = true;
    messageContent.hidden = true;
})

viewWorkers.addEventListener("click", function(){
    editContent.hidden = true;
    attendeeContent.hidden = true;
    workerContent.hidden = false;
    messageContent.hidden = true;
})

sendMessage.addEventListener("click", function(){
    editContent.hidden = true;
    attendeeContent.hidden = true;
    workerContent.hidden = true;
    messageContent.hidden = false;
})
