<?php
// Include database configuration and any necessary files
require_once __DIR__ . '/../../config/config.php';

$file = $_GET['file'] ?? '';
$errorMessage = '';

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
    <title>Examination Result Slips</title>
</head>
<body>

<h2>Examination Result Slips</h2>

<?php if ($errorMessage): ?>
    <p><?php echo htmlspecialchars($errorMessage); ?></p>
<?php else: ?>
    <!-- Embed PDF for viewing -->
    <iframe src="<?php echo 'uploads/' . htmlspecialchars($file); ?>" width="100%" height="600px"></iframe>


<?php endif; ?>

</body>
</html>
