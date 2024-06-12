<?php
// Function to sanitize user inputs

function blockUnicode($input) {
    // Replace any characters outside the ASCII range with an empty string
    return preg_replace('/[^\x20-\x7E]/u', '', $input);
}


function sanitizeInput($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Get user input from POST request
$userInput = $_POST['text'];
$sanitizedInput = sanitizeInput($userInput);
$cleanValue = str_replace('|', '', $sanitizedInput);

echo blockUnicode($cleanValue);
?>
