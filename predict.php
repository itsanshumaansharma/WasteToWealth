<?php
// API Key
$apiKey = "AIzaSyBZdDngL6phB3pQCE8wCL0CgKdjrVVCwak";

// Image path from the request
$data = json_decode(file_get_contents("php://input"), true);
$imagePath = $data['image'] ?? '';

if (!$imagePath || !file_exists($imagePath)) {
    echo json_encode(["error" => "Invalid or missing image path."]);
    exit;
}

// Encode image to base64
$imageData = base64_encode(file_get_contents($imagePath));

// Prepare the payload
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "This is a photo of waste. Predict its waste type (Plastic, Organic, Metal, E-waste) and estimated weight in kg."],
                ["inline_data" => ["mime_type" => "image/jpeg", "data" => $imageData]]
            ]
        ]
    ]
];

// API URL with the updated model
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo json_encode(["error" => 'Curl error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

// Close cURL
curl_close($ch);

// Decode the response
$result = json_decode($response, true);

// Extract the text response
$text = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Unknown';

// Use regular expressions to extract waste type and weight
preg_match('/(Plastic|Organic|Metal|E-waste)/i', $text, $typeMatch);
preg_match('/(\d+(\.\d+)?)\s?kg/i', $text, $weightMatch);

$type = $typeMatch[1] ?? 'Unknown';
$weight = $weightMatch[1] ?? 0;

// Return the results as JSON
echo json_encode(["type" => $type, "weight" => $weight]);
?>
