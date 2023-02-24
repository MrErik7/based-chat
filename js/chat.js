// Set global variables 
let display_name = "";
let username = "";
let timestamp = new Date().toUTCString(); //initialize with current timestamp
let current_chatroom = "all";

// Notifications
let notification;
let current_contact_requests = [];

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

// This method adds a new contact request to the database, 
function addContactDB(username, display_name, contact_display_name) {
  // First send a request to upload the contact request to the database
  $.ajax({
    type: "POST",
    url: "php/insert_contact_request.php",
    data: { username: username, display_name: display_name, contact_display_name: contact_display_name },
    success: function (response) {
      if (response == "sent") {
        document.getElementById("contact-input-response").textContent = "Contact request sent successfully.";

      } else if (response == "existent") {
        document.getElementById("contact-input-response").textContent = "Contact already added.";

      } else if (response == "non-existent") {
        document.getElementById("contact-input-response").textContent = "Contact doesnt exist.";

      } else if (response == "already sent") {
        document.getElementById("contact-input-response").textContent = "Request already sent.";

      } else if (response == "same-name") {
        document.getElementById("contact-input-response").textContent = "You cant add yourself idiot.";

      }

      console.log(response);
    }
  });
}

function addContactGUI(contact_display_name) {
  // Create the "div" element to hold the contact
  let contact = document.createElement("div");
  contact.className = "contact";
  contact.innerHTML = `${contact_display_name}`;

  // Create the "remove contact" button
  let removeButton = document.createElement("button");
  removeButton.innerText = "Remove Contact";
  removeButton.onclick = function() {
    $.ajax({
      type: "POST",
      url: "php/remove_contact.php",
      data: { display_name: display_name, contact_name: contact_display_name },
      success: function(response) {
        console.log(response);
        document.getElementById("contact-list").removeChild(contact)
      }
    });
    

  }

  // Create the "open dm" button
  let dmButton = document.createElement("button");
  dmButton.innerText = "Open DM";
  dmButton.onclick = function() {
    // open DM with contact
    // Set variables
    current_chatroom = contact_display_name;

    // Set the chatroom header
    document.getElementById("welcome-current-chat-room").innerHTML = "Chatting with: " + current_chatroom;
    
    // Retreive the messages from the person
    $.ajax({
      type: "POST",
      url: "php/message-related/retrieve_messages.php",
      data: { display_name: display_name, contact_name: contact_display_name },
      success: function(response) {
        console.log(response);
        let messages = JSON.parse(response);

        // Iterate through the messages and add them to the chat log
        for (let i = 0; i < messages.length; i++) {
          let message = messages[i];
          addMessage(message.message_text, message.sender_name, display_name, message.timestamp, false);
        }
        }
    });

  }

  // Append buttons to contact div
  contact.appendChild(removeButton);
  contact.appendChild(dmButton);

  // Append the message to the contact-list
  let contactList = document.getElementById("contact-list");
  contactList.appendChild(contact);
}


function checkContactRequests() {
  console.log("searching for friend requests");
  $.ajax({
    type: "POST",
    url: "php/retrieve_contact_requests.php",
    data: { display_name: display_name },
    success: function (response) {
      console.log(current_contact_requests)

      if (response == "non-existent") {
        return;
      }

      response = JSON.parse(response);

      if (response == null ) {
        return;
      }
      var response_array = response.split(",");
      console.log(response);

      // Display a separate notification for each new contact request
      for (var i = 0; i < response_array.length; i++) {

        // Check if the contact request already exist
        if (current_contact_requests.includes(response_array[i])) {
          continue;
        }

        console.log("lopp throug");
        var notificationContainer = $("#notification-container");
        notification = $("<div>", { class: "notification", id: "notification-" + i });
        var notificationText = $("<p>", { class: "notification-text", text: "You have a new contact request from " + String(response_array[i]) });
        var acceptButton = $("<button>", { class: "notification-button accept-button", text: "Accept" });
        var denyButton = $("<button>", { class: "notification-button deny-button", text: "Deny" });
        
        notification.append(notificationText);
        notification.append(acceptButton);
        notification.append(denyButton);
        notificationContainer.append(notification);
        notification.show();

        // Set the accept and deny button click handlers
        acceptButton.click(function() { notificationAccept() })
        denyButton.click(function() { notificationDeny() })
        
        // Add it to the contact request array
        current_contact_requests.push(response_array[i])


      }
    }
  });
}

function notificationAccept() {
  console.log("Accepted contact request from " + current_contact_requests[0]);
  
  // Send an AJAX request to add the contact to the requesting persons contacts
  $.ajax({
    type: "POST",
    url: "php/insert_contact.php",
    data: { display_name: display_name, contact_name: current_contact_requests[0] },
    success: function(response) {
      console.log(response);
      notification.hide();
    }
  });

  // Send an AJAX request to add the contact to the users contacts
  $.ajax({
    type: "POST",
    url: "php/insert_contact.php",
    data: { display_name: current_contact_requests[0], contact_name: display_name },
    success: function(response) {
      console.log(response);
    }
  });

  // And then finally remove the request from the database
  // Send an AJAX request to remove the contact request from the database
  $.ajax({
    type: "POST",
    url: "php/remove_contact_request.php",
    data: { display_name: display_name, contact_name: current_contact_requests[0] },
    success: function(response) {
      console.log(response);
      notification.hide();
    }
  });

  // Update the visual part
  addContactGUI(current_contact_requests[0]);

  // And at last reset the array
  current_contact_requests.length = 0;
}

function notificationDeny() {
  console.log("Denied contact request from " + current_contact_requests[0]);
  
  // Send an AJAX request to remove the contact request from the database
  $.ajax({
    type: "POST",
    url: "php/remove_contact_request.php",
    data: { display_name: display_name, contact_name: current_contact_requests[0] },
    success: function(response) {
      console.log(response);
      notification.hide();
    }
  });
}

// This method handles the "add new contact" button
function addContactWithClick() {
  let contact_display_name = document.getElementById("contact-user-input").value;

  // Add the contact to the database, this function will also check if the contact already exists
  addContactDB(username, display_name, contact_display_name);

  document.getElementById("contact-user-input").value = "";
}

// Add a chatroom to the DB
function addChatroomDB(username, chatroom_id, password, whitelisted_people) {
  console.log(whitelisted_people);
  $.ajax({
    type: "POST",
    url: "php/insert_chatroom.php",
    data: { username: username, chatroom_id: chatroom_id, password: password, whitelisted_people: whitelisted_people },
    success: function (response) {


      console.log(response);
    }
  });

}

// Create a new chatroom
function createChatroom() {
  // Generate credentials
  // Generate room id
  chatroom_id = "";
  for (let x = 0; x < 9; x++) {
    chatroom_id += Math.floor(Math.random() * 10);
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

  // Check for keydowns (more specifically enter)
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
  $.ajax({
    type: "POST",
    url: "php/message-related/retrieve_messages.php",
    data: { display_name: display_name, contact_name: current_chatroom },
    success: function(response) {
      console.log(response);
      let messages = JSON.parse(response);

      // Iterate through the messages and add them to the chat log
      for (let i = 0; i < messages.length; i++) {
        let message = messages[i];
        addMessage(message.message_text, message.sender_name, display_name, message.timestamp, false);
      }
      }
  });

  // Retrieve the contacts from the database and display them
  // Send a GET request to retrieve the contacts
  let xhr_contacts = new XMLHttpRequest();
  xhr_contacts.open("GET", "php/retrieve_contacts.php?display_name=" + display_name, true);
  xhr_contacts.onreadystatechange = function () {
    if (xhr_contacts.readyState === 4 && xhr_contacts.status === 200) {
      let contacts = JSON.parse(xhr_contacts.responseText);

      if (contacts == null) {
        return;
      }

      contacts_array = contacts.split(",")

      // Iterate through the contacts and add them to the ccontact list
      for (let i = 0; i < contacts_array.length; i++) {
        let contact = contacts_array[i];
        console.log(contact);
        addContactGUI(contact);
      }
    }

  };
  xhr_contacts.send();

  // Set the chatroom header
  document.getElementById("welcome-current-chat-room").innerHTML = "Chatting with: " + current_chatroom;

  // Fix the notification interval
  checkContactRequests();
  setInterval(checkContactRequests, 1000);
  

  // For debug - will delete later
  let response = document.getElementById("contact-input-response");
  response.textContent = "-  response  -";
}

// When the site has been fully loaded --> run the setup
window.onload = setup;


