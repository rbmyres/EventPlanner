function validate(){

    event.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    if ( username == "u" && password == "p"){
        window.location.href = "home.html";
        // alert ("Login successfull!"); 
    }
    else{
        alert ("Incorrect username or password. Please try again.")
    }
}
