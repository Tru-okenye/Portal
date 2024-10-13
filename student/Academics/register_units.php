<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['admission_number'])) {
        $admission_number = $_POST['admission_number'];
        $semester = $_POST['semester'];
        $year_of_study = $_POST['year_of_study'];

        // Check if units are already registered for the same semester and year
        $check_registration_query = "
            SELECT COUNT(*) as count 
            FROM unit_registrations 
            WHERE admission_number = ? 
            AND semester = ? 
            AND year_of_study = ?";
        
        $stmt_check = $conn->prepare($check_registration_query);
        $stmt_check->bind_param("sii", $admission_number, $semester, $year_of_study);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        $count = $check_result->fetch_assoc()['count'];

        if ($count > 0) {
            echo "Units have already been registered for Semester $semester, Year $year_of_study. You cannot register again.";
            exit();
        }

        // Proceed with registration if not already registered
        if (isset($_POST['units']) && is_array($_POST['units'])) {
            $selected_units = $_POST['units'];

            // Prepare the statement to insert unit registrations
            $registration_query = "
                INSERT INTO unit_registrations (admission_number, unit_code, semester, year_of_study, status) 
                VALUES (?, ?, ?, ?, 'pending')";
            $stmt_registration = $conn->prepare($registration_query);

            if ($stmt_registration === false) {
                die("Prepare failed: " . htmlspecialchars($conn->error));
            }

            // Bind and execute for each selected unit
            foreach ($selected_units as $unit_code) {
                $stmt_registration->bind_param("ssii", $admission_number, $unit_code, $semester, $year_of_study);
                if (!$stmt_registration->execute()) {
                    echo "Error registering unit $unit_code: " . htmlspecialchars($stmt_registration->error);
                }
            }

            echo "Units registered successfully with pending status!";
            echo '<meta http-equiv="refresh" content="2;url=index.php?page=Academics/units">';
            exit();

        } else {
            echo "No units selected for registration.";
        }
    } else {
        echo "Admission number not provided.";
    }
}

?>
