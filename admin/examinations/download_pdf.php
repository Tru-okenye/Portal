<?php
// Include necessary configuration
require_once __DIR__ . '/../../config/config.php';

// Retrieve the file from the URL parameters
$file = $_GET['file'] ?? '';
$errorMessage = '';

// Check if file exists
if ($file) {
    $filePath = __DIR__ . '/../../uploads/' . $file;
    if (!file_exists($filePath)) {
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
    <title>Pass and Supplementary List</title>
</head>
<body>

<h2>Pass and Supplementary List</h2>

<?php if ($errorMessage): ?>
    <p><?php echo htmlspecialchars($errorMessage); ?></p>
<?php else: ?>
    <!-- Embed CSV file for viewing -->
    <iframe src="<?php echo 'uploads/' . htmlspecialchars($file); ?>" width="100%" height="600px"></iframe>

 
<?php endif; ?>

</body>
</html>
