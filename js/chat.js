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
  console.log(username);
  if (db) {
    // First send a request to upload the message to the database logs
    $.ajax({
      type: "POST",
      url: "php/message-related/insert_message.php",
      data: { sender_name: sender, recipient_name: recipient_name, message_text: text, timestamp: timestamp, username: username },
      success: function(response) {
        console.log(response);
      }
    });
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

  addMessage(messageText, display_name, current_chatroom, timestamp, true);
  document.getElementById("chat-msg-input").value = "";
}

// This method adds a new contact request to the database, 
function addContactDB(username, display_name, contact_display_name) {
  // First send a request to upload the contact request to the database
  $.ajax({
    type: "POST",
    url: "php/contact-related/insert_contact_request.php",
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
      url: "php/contact-related/remove_contact.php",
      data: { display_name: display_name, contact_name: contact_display_name },
      success: function(response) {
        console.log(response);
        document.getElementById("contact-list").removeChild(contact)
      }
    });

    $.ajax({
      type: "POST",
      url: "php/message-related/remove_messages.php",
      data: { display_name: display_name, contact_name: contact_display_name },
      success: function(response) {
        console.log(response);
      }
    });

    // Switch back to the public chatroom
    switchChatroomAll();

    alert("Contact successfully removed.")

    

  }

    // Create the "open dm" button
    let dmButton = document.createElement("button");
    dmButton.innerText = "Open DM";
    dmButton.onclick = function() {

      // First check so that this person isnt already active
      if (current_chatroom == contact_display_name) {
        console.log("already active");
        return;
      }

      // Before we retrieve the messages we need to clear the chatlog
      document.getElementById("chat-log").innerHTML = "";

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

          if (response == "no-found") {
            return;
          }

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
    url: "php/contact-related/retrieve_contact_requests.php",
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
    url: "php/contact-related/insert_contact.php",
    data: { display_name: display_name, contact_name: current_contact_requests[0] },
    success: function(response) {
      console.log(response);
      notification.hide();
    }
  });

  // Send an AJAX request to add the contact to the users contacts
  $.ajax({
    type: "POST",
    url: "php/contact-related/insert_contact.php",
    data: { display_name: current_contact_requests[0], contact_name: display_name },
    success: function(response) {
      console.log(response);
    }
  });

  // And then finally remove the request from the database
  // Send an AJAX request to remove the contact request from the database
  $.ajax({
    type: "POST",
    url: "php/contact-related/remove_contact_request.php",
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
    url: "php/contact-related/remove_contact_request.php",
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


// This method simply sets the chatroom back to "all"
function switchChatroomAll() {
  current_chatroom = "all";
  document.getElementById("chat-log").innerHTML = "";

  // Retrieve the messages
  retrieveMessages(display_name);
  
  // Set the chatroom header
  document.getElementById("welcome-current-chat-room").innerHTML = "Chatting with: " + current_chatroom;

}


function retrieveMessages(display_name) {
  // Send a request to retrieve the messages
  $.ajax({
    type: "POST",
    url: "php/message-related/retrieve_messages.php",
    data: { display_name: display_name, contact_name: current_chatroom },
    success: function(response) {
      console.log(response);

      if (response == "no-found") {
        return;
      }
      
      let messages = JSON.parse(response);

      // Iterate through the messages and add them to the chat log
      for (let i = 0; i < messages.length; i++) {
        let message = messages[i];
        addMessage(message.message_text, message.sender_name, display_name, message.timestamp, false);
      }
    }
  });
}

function retrieveContacts(display_name) {
  $.ajax({
    type: "POST",
    url: "php/contact-related/retrieve_contacts.php",
    data: { display_name: display_name },
    success: function(response) {
      let contacts = JSON.parse(response);

      if (contacts == null) {
        return;
      }

      contacts_array = contacts.split(",")

      // Iterate through the contacts and add them to the contact list
      for (let i = 0; i < contacts_array.length; i++) {
        let contact = contacts_array[i];
        console.log(contact);
        addContactGUI(contact);
      }
    }

  });
}


function toggleDarkMode() {
  document.body.classList.toggle("dark-mode");

}

// This runs once when the site has been fully loaded
function setup() {
  // Create a promise for retrieving the display name
  const displayNamePromise = new Promise((resolve, reject) => {
    $.ajax({
      type: "GET",
      url: "php/info-related/getSessionVariable.php?name=display_name",
      success: function(response) {
        console.log(response);
        display_name = response;
        document.getElementById("welcome-display-name").innerHTML = "Logged in as " + display_name;
        resolve(display_name);
      },
      error: function(error) {
        reject(error);
      }
    });
  });

  // Chain the promise for retrieving the display name with a promise for retrieving the username
  displayNamePromise.then((display_name) => {
    $.ajax({
      type: "POST",
      url: "php/info-related/retrieve_username.php",
      data: { display_name: display_name },
      success: function(response) {
        username = response;
      }
    });

    // Retrieve the messages and contacts once the display name and username have been retrieved
    retrieveMessages(display_name);
    retrieveContacts(display_name);

  }).catch((error) => {
    console.error(error);
  });


  // Set the chatroom header
  document.getElementById("welcome-current-chat-room").innerHTML = "Chatting with: " + current_chatroom;

  // Fix the notification interval
  checkContactRequests();
  setInterval(checkContactRequests, 1000);

  // Set the event listener for the send button - so the user can press enter instead
  // Execute a function when the user presses a key on the keyboard
  document.getElementById("chat-msg-input").addEventListener("keypress", function(event) {
    // If the user presses the "Enter" key on the keyboard
    if (event.key === "Enter") {
      // Cancel the default action, if needed
      event.preventDefault();
      
      // Add the message
      addMessageWithClick();
    }
  });


  // For debug - will delete later
  let response = document.getElementById("contact-input-response");
  response.textContent = "-  response  -";
}

// When the site has been fully loaded --> run the setup
window.onload = setup;


