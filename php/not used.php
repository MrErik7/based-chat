<?php

$keys_file = file_get_contents("/path/to/encrypted_keys.txt");
$keys_lines = explode("\n", $keys_file);

foreach ($keys_lines as $key_line) {
    $key_parts = explode(" - ", $key_line);
    if ($key_parts[0] === "example_username") {
        $key = $key_parts[1];
        break;
    }
}

if (!isset($key)) {
    // Handle case where the key for the desired user was not found
}

?>