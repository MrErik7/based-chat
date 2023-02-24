<?php
// Start the session
session_start();

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "baseddb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the display name and contact name from the request
$display_name = $_POST["display_name"];
$contact_name = $_POST["contact_name"];

// -- Check if the record exists for the user --
$check_sql = "SELECT * FROM login WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $display_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Record exists, check number of contacts for user
    $num_contacts_sql = "SELECT COUNT(*) FROM login WHERE display_name='$display_name' AND contacts IS NOT NULL";
    $num_contacts_result = $conn->query($num_contacts_sql);
    $num_contacts_row = $num_contacts_result->fetch_row();
    $num_contacts_response = $num_contacts_row[0];

    if ($num_contacts_response > 1) {
        // There are more than one contact, remove the contact
        // Retrieve current contacts for user
        $sql = "SELECT contacts FROM login WHERE display_name='$display_name'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $contacts = $row["contacts"];
        } else {
            echo "No contacts found for user $display_name.";
            $conn->close();
            exit();
        }

        // Remove contact name from list of contacts
        $contacts_arr = explode(",", $contacts);
        $key = array_search($contact_name, $contacts_arr);
        if ($key !== false) {
            unset($contacts_arr[$key]);
        }
        $new_contacts = implode(",", $contacts_arr);

        // Update contact requests for user in database
        $sql = "UPDATE login SET contacts='$new_contacts' WHERE display_name='$display_name'";

        if ($conn->query($sql) === TRUE) {
            echo "Contact for $contact_name removed successfully from user $display_name.";
        } else {
            echo "Error updating contact requests: " . $conn->error;
        }

        $conn->close();
        
    } else {
        // This is the last contact, replace it with null
        $sql = "UPDATE login SET contacts=NULL WHERE display_name='$display_name'";

        if ($conn->query($sql) === TRUE) {
            echo "Contact for $contact_name replaced with null successfully for user $display_name.";
        } else {
            echo "Error updating contact requests: " . $conn->error;
        }

        $conn->close();
    }
} else {
    echo "non-existent";
    return;
}

// -- Check if the record exists for the contact --
$check_sql = "SELECT * FROM login WHERE display_name = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $contact_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // Record exists, check number of contacts for user
    $num_contacts_sql = "SELECT COUNT(*) FROM login WHERE display_name='$contact_name' AND contacts IS NOT NULL";
    $num_contacts_result = $conn->query($num_contacts_sql);
    $num_contacts_row = $num_contacts_result->fetch_row();
    $num_contacts_response = $num_contacts_row[0];

    if ($num_contacts_response > 1) {
        // There are more than one contact, remove the contact
        // Retrieve current contacts for user
        $sql = "SELECT contacts FROM login WHERE display_name='$contact_name'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $contacts = $row["contacts"];
        } else {
            echo "No contacts found for user $contact_name.";
            $conn->close();
            exit();
        }

        // Remove contact name from list of contacts
        $contacts_arr = explode(",", $contacts);
        $key = array_search($display_name, $contacts_arr);
        if ($key !== false) {
            unset($contacts_arr[$key]);
        }
        $new_contacts = implode(",", $contacts_arr);

        // Update contact requests for user in database
        $sql = "UPDATE login SET contacts='$new_contacts' WHERE display_name='$contact_name'";

        if ($conn->query($sql) === TRUE) {
            echo "Contact for $display_name removed successfully from user $contact_name.";
        } else {
            echo "Error updating contact requests: " . $conn->error;
        }

        $conn->close();
        
    } else {
        // This is the last contact, replace it with null
        $sql = "UPDATE login SET contacts=NULL WHERE display_name='$contact_name'";

        if ($conn->query($sql) === TRUE) {
            echo "Contact for $contact_name replaced with null successfully for user $display_name.";
        } else {
            echo "Error updating contact requests: " . $conn->error;
        }

        $conn->close();
    }
} else {
    echo "non-existent";
    return;
}

// Close the statement
$check_stmt->close();

