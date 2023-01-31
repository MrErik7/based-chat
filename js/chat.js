// Set global variables 
let display_name = "";
let username = "";
let timestamp = new Date().toUTCString(); //initialize with current timestamp

// This is called when a message is supposed to be displayed
function addMessage(text, sender, timestamp, db) {
    // Check if its a new message
    if (db) {
        // First send a request to upload the message to the database logs
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "php/insert_message.php?username=" + username, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Handle the response from the server
                console.log("message sucess");
                console.log(xhr);
            }
        };
        xhr.send("sender_name=" + sender + "&message_text=" + text + "&timestamp=" + timestamp);
    }

    // Create the "div" element to hold the message
    let message = document.createElement("div");
    message.className = "message";
    message.innerHTML = `${timestamp}</span> <span class="sender">${sender}</span>: ${text} <span class="timestamp">`;
    // Append the message to the chat log
    let chatLog = document.getElementById("chat-log");
    chatLog.appendChild(message);

    // Scroll down in the chatlog
    chatLog.scrollTop = chatLog.scrollHeight;
}

// This is called when the user sends a message 
function addMessageWithClick() {
    let messageText = document.getElementById("chat-msg-input").value;
    var timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

    addMessage(messageText, display_name, timestamp, true); 
    document.getElementById("chat-msg-input").value = "";
}

// This method adds a new contact to the database, 
function addContact(contact_display_name) {
    // First send a request to upload the message to the database logs
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/insert_contact.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Handle the response from the server
        }
    };
    xhr.send("user_display_name=" + display_name + "&contact_name=" + contact_display_name);

}

// This runs once when the site has been fully loaded
function setup() {
    // Create a request to get data from the server (the display name in this case)
    var xhr_displayname_session = new XMLHttpRequest();
    xhr_displayname_session.open("GET", "php/getSessionVariable.php?name=display_name", true);
    xhr_displayname_session.onload = function () {
        if (xhr_displayname_session.readyState === 4 && xhr_displayname_session.status === 200) {
            display_name = xhr_displayname_session.responseText;
            console.log(display_name);

            // Display the welcome message
            document.getElementById("welcome-display-name").innerHTML = "Logged in as " + display_name;

        }
    };
    xhr_displayname_session.send();

    // Create a request to get data from the server (the username in this case)
    var xhr_username_session = new XMLHttpRequest();
    xhr_username_session.open("GET", "php/getSessionVariable.php?name=username", true);
    xhr_username_session.onload = function () {
        if (xhr_username_session.readyState === 4 && xhr_username_session.status === 200) {
            username = xhr_username_session.responseText;
            console.log(username);
        }
    };
    xhr_username_session.send();

    // Check for keydowns (more specifically space)
    // While the input is focused --> user can send message by simply pressing enter
    document.getElementById("chat-msg-input").addEventListener("keydown", function (event) {
        if (event.key == "Enter") {
            addMessageWithClick();
        }

    });

    // Clear the chatlog - since this runs evertytime the document is initialized it will create a bunch of copies and this will prevent that :D
    // document.getElementById("chat-log").innerHTML = "";

    // Retrieve the messages from the database and display them
    // Send a GET request to retrieve the messages
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "php/retrieve_messages.php?username=" + username, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let messages = JSON.parse(xhr.responseText);

            // Check if any messages were found
            if (messages == "No messages found") {
                console.log("no message found its ok");
            }


            // Iterate through the messages and add them to the chat log
            for (let i = 0; i < messages.length; i++) {
                let message = messages[i];
                addMessage(message.message_text, message.sender_name, message.timestamp, false);
            }
        }
    };
    xhr.send();
}

// When the site has been fully loaded --> run the setup
window.onload = setup;


