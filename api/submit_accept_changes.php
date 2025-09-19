<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['json_file']) || !isset($data['changed'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$jsonFile = $data['json_file'];
$jsonPath = __DIR__ . "/../jsons/$jsonFile";

if (!file_exists($jsonPath)) {
    echo json_encode(['success' => false, 'error' => 'JSON file not found']);
    exit;
}

$jsonContent = json_decode(file_get_contents($jsonPath), true);

if (!$jsonContent || !isset($jsonContent['paragraphs'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON format']);
    exit;
}

$updatedCount = 0;

foreach ($data['changed'] as $change) {
    $targetParagraphId = $change['paragraph_index']; // This is actually paragraph_id
    $tokensToUpdate = $change['changed_tokens'];
    $paragraphText = $change['paragraph_text'] ?? '';

    // Find the correct paragraph by paragraph_id
    foreach ($jsonContent['paragraphs'] as &$paragraph) {
        if ((int)$paragraph['paragraph_id'] === (int)$targetParagraphId) {
            // Update full proofread text
            $paragraph['proofread'] = $paragraphText;

            // Update proofread_token words
            foreach ($tokensToUpdate as $tokenChange) {
                $targetIdx = $tokenChange['idx'];
                $newWord = $tokenChange['word'];

                foreach ($paragraph['proofread_token'] as &$token) {
                    if ((int)$token['idx'] === (int)$targetIdx) {
                        $token['word'] = $newWord;
                        $updatedCount++;
                        break;
                    }
                }

                // Update revised_text suggestions
                foreach ($paragraph['revised_text'] as &$revisedEntry) {
                    if ((int)$revisedEntry['index'] === (int)$targetIdx) {
                        $revisedEntry['suggestions'] = array_values(array_filter(
                            $revisedEntry['suggestions'],
                            fn($suggestion) => $suggestion !== $newWord
                        ));
                        break;
                    }
                }
            }
            break; // Paragraph found, no need to keep looping
        }
    }
}
unset($paragraph); // Clean up reference

// Build paragraph ID string for FastAPI based on incoming changes
$paragraphIds = [];

foreach ($data['changed'] as $change) {
    if (isset($change['paragraph_index'])) {
        $paragraphIds[] = $change['paragraph_index'];
    }
}

// Remove duplicates and sort
$paragraphIds = array_unique($paragraphIds);
sort($paragraphIds);

// Format as string: [1,3,5]
$paragraphIdString = '[' . implode(',', $paragraphIds) . ']';



file_put_contents($jsonPath, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// // Optional: define mode if not set
$mode = 1;
$originalFileName = $data['pdf_file'];
$pdf_file = pathinfo($originalFileName, PATHINFO_FILENAME);


// Build API URL and call FastAPI
$api_url = "http://localhost:5000/api/grammar-check?mode=$mode&file_code=" . urlencode($pdf_file) . "&paragraph_id=" . $paragraphIdString;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$api_response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($api_response === false) {
    echo json_encode([
        'success' => false,
        'error' => 'Curl error: ' . $curl_error
    ]);
    exit;
}

$responseData = json_decode($api_response, true);
if (!$responseData) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON response from FastAPI.'
    ]);
    exit;
}


echo json_encode([
    'success' => true,
    'updated_tokens' => $updatedCount,
    'paragraph_id_text' => $paragraphIdString,
    'json_file' => $jsonFile,
    'pdf_file' => $pdf_file,
    'changed' => $data['changed'],
    'fastapi' => [
        'json_filename' => $responseData['json_filename'] ?? '',
        'final_pdf_filename' => $responseData['final_pdf_filename'] ?? '',
        'elapsed_time_seconds' => $responseData['elapsed_time_seconds'] ?? 0,
        'total_improvements' => $responseData['total_improvements'] ?? 0
    ]
]);
