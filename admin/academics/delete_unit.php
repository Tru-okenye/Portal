<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unitCode = $_POST['unitCode'];
    
    // Prepare SQL statement to delete the unit
    $sql = "DELETE FROM Units WHERE UnitCode = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $unitCode);

    if ($stmt->execute()) {
        echo "Unit deleted successfully.";
    } else {
        echo "Error deleting unit: " . $stmt->error;
    }

    // Redirect back to the units page after deletion
    echo '<meta http-equiv="refresh" content="2;url=index.php?page=academics/courses">';

    exit();
}
?>
