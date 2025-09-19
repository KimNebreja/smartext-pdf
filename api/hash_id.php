<?php

if (isset($_GET['processed_id'])) {
    $processedId = $_GET['processed_id'];

    // Reversibly encode the processed_id
    $encodedId = base64_encode($processedId);

    // Return the encoded ID as a JSON response
    echo json_encode(['encoded_id' => $encodedId]);
} else {
    // If no processed_id is provided, return an error
    echo json_encode(['error' => 'No processed_id provided']);
}