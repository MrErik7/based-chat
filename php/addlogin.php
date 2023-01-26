<?php
    //connect to the database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "baseddb";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //hash the password with Argon2
    $options = [
        'memory_cost' => 1<<17, // 128 MB
        'time_cost' => 4,
        'threads' => 2
    ];
    $display_name = $_POST['display_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_ARGON2I, $options);

    // Now check if the specified username already exists --> then dont add it
    $sql = "SELECT * FROM login WHERE username='$username'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "Username already exists";
    } else {

        //insert the username, hashed password into the database
        $sql = "INSERT INTO login (display_name, username, password) VALUES ('$display_name', '$username', '$hashed_password')";
        $result = $conn->query($sql);

        if ($result === TRUE) {
            echo "New record created successfully";
            error_log("a user has been crteated");
            header("Location: /login.html");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
    exit;

?>