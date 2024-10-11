<?php
include_once '../../config/config.php'; // Include the database connection file

if (isset($_GET['admissionNumber'])) {
    $admissionNumber = $_GET['admissionNumber'];

    // Prepare and execute the delete query
    $sql = "DELETE FROM Students WHERE AdmissionNumber = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $admissionNumber);
        if ($stmt->execute()) {
            echo "Record deleted successfully.";
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
    
    // Redirect back to the confirmation page
    header("Location: index.php?page=admission/confirmation");
    exit;
} else {
    echo "Admission Number not specified.";
}
?>
