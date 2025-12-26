<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
$allowedExtensions = ['pdf', 'doc', 'docx'];
$uploadBaseDir = 'Uploads';
$maxRevisions = 3;

// Function to send JSON response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Function to sanitize booking code
function sanitizeBookingCode($code) {
    $sanitized = preg_replace('/[^a-zA-Z0-9\-_]/', '', $code);
    return empty($sanitized) ? false : $sanitized;
}

// Function to get next revision number
function getNextRevisionNumber($bookingDir) {
    if (!is_dir($bookingDir)) {
        return 1;
    }
    
    $revisions = array_filter(glob($bookingDir . '/rev_*'), 'is_dir');
    $maxRev = 0;
    
    foreach ($revisions as $revDir) {
        $revNum = (int)str_replace($bookingDir . '/rev_', '', $revDir);
        if ($revNum > $maxRev) {
            $maxRev = $revNum;
        }
    }
    
    return $maxRev + 1;
}

// Function to count existing revisions
function countRevisions($bookingDir) {
    if (!is_dir($bookingDir)) {
        return 0;
    }
    
    $revisions = array_filter(glob($bookingDir . '/rev_*'), 'is_dir');
    return count($revisions);
}

// Function to get booking code info
function getBookingInfo($bookingCode) {
    global $uploadBaseDir;
    
    $sanitizedCode = sanitizeBookingCode($bookingCode);
    if (!$sanitizedCode) {
        return ['revision_count' => 0, 'last_upload' => null];
    }
    
    $bookingDir = $uploadBaseDir . '/' . $sanitizedCode;
    $revisionCount = countRevisions($bookingDir);
    $lastUpload = null;
    
    if ($revisionCount > 0) {
        $lastRevDir = $bookingDir . '/rev_' . $revisionCount;
        if (is_dir($lastRevDir)) {
            $files = glob($lastRevDir . '/*');
            if (!empty($files)) {
                $lastUpload = date('Y-m-d H:i:s', filemtime($files[0]));
            }
        }
    }
    
    return [
        'revision_count' => $revisionCount,
        'last_upload' => $lastUpload
    ];
}

// Function to search documents
function searchDocuments($bookingCode) {
    global $uploadBaseDir, $allowedExtensions;
    
    $sanitizedCode = sanitizeBookingCode($bookingCode);
    if (!$sanitizedCode) {
        return ['success' => false, 'message' => 'Invalid booking code'];
    }
    
    $bookingDir = $uploadBaseDir . '/' . $sanitizedCode;
    
    if (!is_dir($bookingDir)) {
        return ['success' => false, 'message' => 'No documents found'];
    }
    
    $files = [];
    $revisions = array_filter(glob($bookingDir . '/rev_*'), 'is_dir');
    
    // Sort revisions numerically
    usort($revisions, function($a, $b) use ($bookingDir) {
        $revA = (int)str_replace($bookingDir . '/rev_', '', $a);
        $revB = (int)str_replace($bookingDir . '/rev_', '', $b);
        return $revA - $revB;
    });
    
    foreach ($revisions as $revDir) {
        $revNum = (int)str_replace($bookingDir . '/rev_', '', $revDir);
        $revFiles = array_filter(glob($revDir . '/*'), function($file) use ($allowedExtensions) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return is_file($file) && in_array($extension, $allowedExtensions);
        });
        
        foreach ($revFiles as $file) {
            $fileName = basename($file);
            $fileSize = filesize($file);
            $fileTime = filemtime($file);
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            $files[] = [
                'name' => $fileName,
                'size' => $fileSize,
                'upload_date' => date('Y-m-d H:i:s', $fileTime),
                'extension' => $extension,
                'revision' => $revNum,
                'path' => $sanitizedCode . '/rev_' . $revNum . '/' . $fileName
            ];
        }
    }
    
    if (empty($files)) {
        return ['success' => false, 'message' => 'No documents found'];
    }
    
    return [
        'success' => true,
        'booking_code' => $bookingCode,
        'files' => $files
    ];
}

// Function to handle file download
function handleDownload($filePath) {
    global $uploadBaseDir;
    
    $filePath = str_replace(['../', '..\\'], '', $filePath);
    $fullPath = $uploadBaseDir . '/' . $filePath;
    
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        die('File not found');
    }
    
    $realUploadDir = realpath($uploadBaseDir);
    $realFilePath = realpath($fullPath);
    
    if (!$realFilePath || strpos($realFilePath, $realUploadDir) !== 0) {
        http_response_code(403);
        die('Access denied');
    }
    
    $fileName = basename($fullPath);
    $fileSize = filesize($fullPath);
    $fileExtension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    
    $contentTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $contentType = isset($contentTypes[$fileExtension]) ? $contentTypes[$fileExtension] : 'application/octet-stream';
    
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, no-cache');
    header('Pragma: public');
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    $handle = fopen($fullPath, 'rb');
    if ($handle) {
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
    } else {
        http_response_code(500);
        die('Unable to read file');
    }
    
    exit;
}

// Function to handle file preview (for viewing in iframe)
function handleView($filePath) {
    global $uploadBaseDir;
    
    $filePath = str_replace(['../', '..\\'], '', $filePath);
    $fullPath = $uploadBaseDir . '/' . $filePath;
    
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        die('File not found');
    }
    
    $realUploadDir = realpath($uploadBaseDir);
    $realFilePath = realpath($fullPath);
    
    if (!$realFilePath || strpos($realFilePath, $realUploadDir) !== 0) {
        http_response_code(403);
        die('Access denied');
    }
    
    $fileName = basename($fullPath);
    $fileSize = filesize($fullPath);
    $fileExtension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    
    $contentTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    $contentType = isset($contentTypes[$fileExtension]) ? $contentTypes[$fileExtension] : 'application/octet-stream';
    
    // For preview, use inline disposition (not attachment)
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, no-cache');
    header('Pragma: public');
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    $handle = fopen($fullPath, 'rb');
    if ($handle) {
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
    } else {
        http_response_code(500);
        die('Unable to read file');
    }
    
    exit;
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'check_revisions':
            $bookingCode = $_GET['booking_code'] ?? '';
            if (empty($bookingCode)) {
                sendResponse(false, 'Booking code required');
            }
            
            $info = getBookingInfo($bookingCode);
            echo json_encode($info);
            exit;
            
        case 'search':
            $bookingCode = $_GET['booking_code'] ?? '';
            if (empty($bookingCode)) {
                sendResponse(false, 'Booking code required');
            }
            
            $results = searchDocuments($bookingCode);
            echo json_encode($results);
            exit;
            
        case 'download':
            $filePath = $_GET['file'] ?? '';
            if (empty($filePath)) {
                http_response_code(400);
                die('File parameter required');
            }
            
            handleDownload($filePath);
            break;
            
        case 'view':
            $filePath = $_GET['file'] ?? '';
            if (empty($filePath)) {
                http_response_code(400);
                die('File parameter required');
            }
            
            handleView($filePath);
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

// Handle POST request (file upload)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Check required fields
if (empty($_POST['bookingCode'])) {
    sendResponse(false, 'Booking code is required');
}

if (!isset($_FILES['document'])) {
    sendResponse(false, 'No file uploaded');
}

$bookingCode = trim($_POST['bookingCode']);
$file = $_FILES['document'];

// Validate booking code
if (strlen($bookingCode) < 3 || strlen($bookingCode) > 50) {
    sendResponse(false, 'Booking code must be between 3 and 50 characters');
}

$sanitizedBookingCode = sanitizeBookingCode($bookingCode);
if (!$sanitizedBookingCode) {
    sendResponse(false, 'Invalid booking code format. Only letters, numbers, hyphens, and underscores are allowed');
}

// Check revision limit
$bookingDir = $uploadBaseDir . '/' . $sanitizedBookingCode;
$currentRevisions = countRevisions($bookingDir);

if ($currentRevisions >= $maxRevisions) {
    sendResponse(false, 'Maximum revision limit (' . $maxRevisions . ') reached for this booking code');
}

// Validate file upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            sendResponse(false, 'File is too large');
            break;
        case UPLOAD_ERR_PARTIAL:
            sendResponse(false, 'File was only partially uploaded');
            break;
        case UPLOAD_ERR_NO_FILE:
            sendResponse(false, 'No file was uploaded');
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            sendResponse(false, 'Missing temporary folder');
            break;
        case UPLOAD_ERR_CANT_WRITE:
            sendResponse(false, 'Failed to write file to disk');
            break;
        default:
            sendResponse(false, 'Unknown upload error');
    }
}

// Validate file size
if ($file['size'] > $maxFileSize) {
    sendResponse(false, 'File size exceeds 10MB limit');
}

// Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    sendResponse(false, 'Invalid file type. Only PDF and Word documents are allowed');
}

// Validate file extension
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    sendResponse(false, 'Invalid file extension. Only .pdf, .doc, and .docx files are allowed');
}

// Additional MIME type validation
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedMimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($detectedMimeType, $allowedTypes)) {
    sendResponse(false, 'File content does not match allowed file types');
}

// Create directories
if (!file_exists($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0755, true)) {
        sendResponse(false, 'Failed to create upload directory');
    }
}

if (!file_exists($bookingDir)) {
    if (!mkdir($bookingDir, 0755, true)) {
        sendResponse(false, 'Failed to create booking directory');
    }
}

// Get next revision number and create revision directory
$nextRevision = getNextRevisionNumber($bookingDir);
$revisionDir = $bookingDir . '/rev_' . $nextRevision;

if (!mkdir($revisionDir, 0755, true)) {
    sendResponse(false, 'Failed to create revision directory');
}

// Generate filename with timestamp to avoid conflicts
$timestamp = date('Ymd_His');
$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$uniqueFilename = $originalName . '_' . $timestamp . '.' . $extension;

$targetPath = $revisionDir . '/' . $uniqueFilename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    rmdir($revisionDir);
    sendResponse(false, 'Failed to save uploaded file');
}

// Set file permissions
chmod($targetPath, 0644);

// Create revision info file
$revisionInfo = [
    'revision' => $nextRevision,
    'booking_code' => $bookingCode,
    'original_filename' => $file['name'],
    'saved_filename' => $uniqueFilename,
    'upload_date' => date('Y-m-d H:i:s'),
    'file_size' => $file['size'],
    'file_type' => $file['type']
];

file_put_contents($revisionDir . '/revision_info.json', json_encode($revisionInfo, JSON_PRETTY_PRINT));

// Update main log
$logFile = $uploadBaseDir . '/upload_log.txt';
$logEntry = date('Y-m-d H:i:s') . " - Booking: " . $bookingCode . " - Revision: " . $nextRevision . " - File: " . $uniqueFilename . " - Size: " . $file['size'] . " bytes\n";
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Create booking summary file
$summaryFile = $bookingDir . '/booking_summary.json';
$summaryData = [
    'booking_code' => $bookingCode,
    'total_revisions' => $nextRevision,
    'last_upload' => date('Y-m-d H:i:s'),
    'created_date' => file_exists($summaryFile) ? json_decode(file_get_contents($summaryFile), true)['created_date'] ?? date('Y-m-d H:i:s') : date('Y-m-d H:i:s')
];

file_put_contents($summaryFile, json_encode($summaryData, JSON_PRETTY_PRINT));

// Success response
$remainingRevisions = $maxRevisions - $nextRevision;
$message = 'File uploaded successfully! ';
$message .= 'Revision ' . $nextRevision . ' of ' . $maxRevisions . ' created.';

if ($remainingRevisions > 0) {
    $message .= ' You have ' . $remainingRevisions . ' revision(s) remaining.';
} else {
    $message .= ' Maximum revisions reached for this booking code.';
}

sendResponse(true, $message, [
    'booking_code' => $bookingCode,
    'revision' => $nextRevision,
    'original_filename' => $file['name'],
    'saved_filename' => $uniqueFilename,
    'file_size' => $file['size'],
    'remaining_revisions' => $remainingRevisions,
    'upload_path' => $revisionDir
]);
?>