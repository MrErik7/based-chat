// Set global variables 
let display_name = "";
let username = "";
let current_chatroom = "all";
let timestamp = new Date().toUTCString(); //initialize with current timestamp

// This is called when a message is supposed to be displayed
function addMessage(text, sender, recipient_name, timestamp, db) {
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
        xhr.send("sender_name=" + sender + "&recipient_name=" + recipient_name + "&message_text=" + text + "&timestamp=" + timestamp);
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
    let receiver = document.getElementById("chat-contact-input").value;
    var timestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

    addMessage(messageText, display_name, receiver, timestamp, true); 
    document.getElementById("chat-msg-input").value = "";
}

// This method adds a new contact to the database, 
function addContactDB(username, display_name, contact_display_name) {
    // First send a request to upload the contact to the database
    $.ajax({
        type: "POST",
        url: "php/insert_contact.php",
        data: { username: username, display_name: display_name, contact_display_name: contact_display_name },
        success: function(response) {
          if (response == "added") {
            document.getElementById("contact-input-response").textContent = "Contact added sucessfully.";
            addContactGUI(contact_display_name);

          } else if (response == "existent") {
            document.getElementById("contact-input-response").textContent = "Contact already added.";

          } else if (response == "non-existent") {
            document.getElementById("contact-input-response").textContent = "Contact doesnt exist.";
          }

          console.log(response);
        }
      });
}

// This method adds a new contact graphiclly
function addContactGUI(contact_display_name) {
    // Create the "div" element to hold the contact
   let contact = document.createElement("div");
   contact.className = "message";
   contact.innerHTML = `${contact_display_name}`;
   
   // Append the message to the contact-list
   let contactList = document.getElementById("contact-list");
   contactList.appendChild(contact);
}

// This method handles the "add new contact" button
function addContactWithClick() {
    let contact_display_name = document.getElementById("contact-user-input").value;

    // Add the contact to the database, this function will also check if the contact already exists
    addContactDB(username, display_name, contact_display_name);
    
    document.getElementById("contact-user-input").value = "";
}

function addChatroomDB(username, chatroom_id, password, whitelisted_people) {
    console.log(whitelisted_people);
    $.ajax({
        type: "POST",
        url: "php/insert_chatroom.php",
        data: { username: username, chatroom_id: chatroom_id, password: password, whitelisted_people: whitelisted_people },
        success: function(response) {
        

          console.log(response);
        }
      });

}

// Create a new chatroom
function createChatroom() {
    // Generate credentials
    // Generate room id
    chatroom_id = "";
    for (let x = 0; x < 9 ;x++) {
        chatroom_id+=Math.floor(Math.random() * 10);
    }
    
    // Get chatroom password
    password = document.getElementById("chatroom-password-input").value;

    // Get whitelisted people
    whitelisted_people = document.getElementById("chatroom-whitelist-input").value;
    
    // Add the chatroom to the DB
    addChatroomDB(username, chatroom_id, password, whitelisted_people);

    document.getElementById("chatroom-password-input").value = "";
    document.getElementById("chatroom-whitelist-input").value = "";
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
    xhr.open("GET", "php/retrieve_messages.php?display_name=" + display_name, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let messages = JSON.parse(xhr.responseText);

            // Iterate through the messages and add them to the chat log
            for (let i = 0; i < messages.length; i++) {
                let message = messages[i];
                addMessage(message.message_text, message.sender_name, display_name, message.timestamp, false);
            }
        }
    };
    xhr.send();

    // Retrieve the contacts from the database and display them
    // Send a GET request to retrieve the contacts
    let xhr_contacts = new XMLHttpRequest();
    xhr_contacts.open("GET", "php/retrieve_contacts.php?username=" + username + "&display_name=" + display_name, true);
    xhr_contacts.onreadystatechange = function () {
        if (xhr_contacts.readyState === 4 && xhr_contacts.status === 200) {
            let contacts = JSON.parse(xhr_contacts.responseText);

            console.log(contacts);

             // Iterate through the messages and add them to the chat log
            for (let i = 0; i < contacts.length; i++) {
                let contact = contacts[i];
                addContactGUI(contact);
            }
        }
        
    };
    xhr_contacts.send();


    // Set the chatroom header
    document.getElementById("welcome-current-chat-room").innerHTML = "Chatroom: " + current_chatroom;

    // For debug - will delete later
    let response = document.getElementById("contact-input-response");
    response.textContent = "-  response  -";
}

// When the site has been fully loaded --> run the setup
window.onload = setup;


