<?php
// Include database configuration and necessary files
require_once __DIR__ . '/../../config/config.php';

$file = $_GET['file'] ?? '';
$filePath = __DIR__ . '/../../uploads/transcripts/' . $file;

$errorMessage = '';
if ($file) {
    if (file_exists($filePath)) {
        // The PDF exists
        $fileUrl = 'uploads/transcripts/' . htmlspecialchars($file);
    } else {
        $errorMessage = 'File not found.';
    }
} else {
    $errorMessage = 'No file specified.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript</title>
</head>
<body>

<h2>Academic Transcript</h2>

<?php if ($errorMessage): ?>
    <p><?php echo htmlspecialchars($errorMessage); ?></p>
<?php elseif (isset($fileUrl)): ?>
    <!-- Embed PDF for viewing -->
    <embed src="<?php echo $fileUrl; ?>" width="100%" height="600px" />

    <!-- Download Link -->
    <a href="<?php echo $fileUrl; ?>" download>Download PDF</a>
<?php endif; ?>

</body>
</html>
