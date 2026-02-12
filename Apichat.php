
<?php

// Function to call the local Ollama model
function callLocalModel($prompt) {
    $url = 'http://localhost:11434/api/generate';
    $data = json_encode([
        "model" => "qwen2.5-coder:3b",
        "prompt" => $prompt,
        "stream" => false 
    ]);

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json",
            "method"  => "POST",
            "content" => $data
        ]
    ];

    // Send request to Ollama API
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // Handle errors
    if ($result === false) {
        return "Error: Failed to connect to the local model.";
    }

    // Decode JSON response
    $response = json_decode($result, true);
    return $response["response"] ?? "Unexpected API response format";
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = trim($_POST['message'] ?? '');
    $prompt = $userInput;

    // Call local AI model with the selected prompt
    $response = callLocalModel($prompt);

    // Prepare the JSON response
    $jsonResponse = [
        "user_input" => $userInput,
        "ai_response" => [
            [
                "type" => "message",
                "message" => $response
            ]
        ]
    ];

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($jsonResponse);
    exit;
}

?>