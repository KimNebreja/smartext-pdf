<?php
set_time_limit(0);
header('Content-Type: application/json');

$message = '';
$output = [];

if (isset($_GET['dbFilename'])) {

    $dbFilename = $_GET['dbFilename'];
    $mode = "0";
    $paragraph_id = "";  // Set this if needed

    // Build API URL with query parameters
    $api_url = "http://localhost:5000/api/grammar-check?mode=$mode&file_code=" . urlencode($dbFilename) . "&paragraph_id=" . urlencode($paragraph_id);

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json'
    ));

    $api_response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($api_response === false) {
        echo json_encode([
            'message' => 'error',
            'error' => 'Curl error: ' . $curl_error
        ]);
        exit;
    }

    $responseData = json_decode($api_response, true);
    if (!$responseData) {
        echo json_encode([
            'message' => 'error',
            'error' => 'Invalid JSON response from FastAPI.'
        ]);
        exit;
    }

    $output = [
        "json_filename" => $responseData['json_filename'] ?? '',
        "final_pdf_filename" => $responseData['final_pdf_filename'] ?? '',
        "elapsed_time_seconds" => $responseData['elapsed_time_seconds'] ?? 0,
        "total_improvements" => $responseData['total_improvements'] ?? 0
    ];

    $message = 'success';

} else {
    $message = 'error';
    $output = ['error' => 'dbFilename parameter is missing.'];
}

echo json_encode([
    'message' => $message,
    'info' => $output
]);