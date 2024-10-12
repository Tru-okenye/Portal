<?php
include_once __DIR__ . '/../../config/config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data. If these are arrays, loop through them.
    $categoryId = $_POST['category'] ?? '';
    $courseName = $_POST['course'] ?? '';
    $year = $_POST['year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $unitCode = $_POST['unit'] ?? '';
    $admissionNumbers = $_POST['student_id'] ?? [];
    $studentNames = $_POST['student_name'] ?? [];
    $examMarksList = $_POST['exam_marks'] ?? [];
    $intake = $_POST['intake'] ?? '';

    // Make sure that the input fields for student_id, student_name, and exam_marks are arrays
    if (is_array($admissionNumbers) && is_array($studentNames) && is_array($examMarksList)) {
        foreach ($admissionNumbers as $index => $admissionNumber) {
            $studentName = $studentNames[$index] ?? '';
            $examMarks = $examMarksList[$index] ?? 0;

            // Fetch the student status
            $statusSql = "SELECT status FROM students WHERE AdmissionNumber = ?";
            if ($statusStmt = $conn->prepare($statusSql)) {
                $statusStmt->bind_param("s", $admissionNumber);
                $statusStmt->execute();
                $statusStmt->bind_result($studentStatus);
                $statusStmt->fetch();
                $statusStmt->close();

                // Check if the student is discontinued
                if ($studentStatus === 'Discontinued') {
                    echo "<p style='color: red;'>Student {$studentName} ({$admissionNumber}) has been discontinued and cannot have further inputs.</p>";
                    continue;  // Skip further processing for this student
                }
            } else {
                echo "Error: " . $conn->error;
                continue;
            }

            // Fetch category name and unit name based on IDs
            $categorySql = "SELECT CategoryName FROM categories WHERE CategoryID = ?";
            if ($categoryStmt = $conn->prepare($categorySql)) {
                $categoryStmt->bind_param("i", $categoryId);
                $categoryStmt->execute();
                $categoryStmt->bind_result($categoryName);
                $categoryStmt->fetch();
                $categoryStmt->close();
            }

            $unitSql = "SELECT UnitName FROM units WHERE UnitCode = ?";
            if ($unitStmt = $conn->prepare($unitSql)) {
                $unitStmt->bind_param("s", $unitCode);
                $unitStmt->execute();
                $unitStmt->bind_result($unitName);
                $unitStmt->fetch();
                $unitStmt->close();
            }

            // Check if the student has passed a previous supplementary exam
            $passCheckSql = "SELECT total_marks FROM supplementary_exam_marks WHERE admission_number = ? AND unit_code = ? AND semester = ? AND year = ? AND total_marks >= 40";
            if ($passCheckStmt = $conn->prepare($passCheckSql)) {
                $passCheckStmt->bind_param("ssss", $admissionNumber, $unitCode, $semester, $year);
                $passCheckStmt->execute();
                $passResult = $passCheckStmt->get_result();

                if ($passResult->num_rows > 0) {
                    // Student has already passed, no further attempts allowed
                    echo "<p style='color: red;'>Student {$studentName} ({$admissionNumber}) has already passed the supplementary exam for this unit. No further attempts are allowed.</p>";
                    continue;  // Skip further processing for this student
                }

                $passCheckStmt->close();
            } else {
                echo "Error: " . $conn->error;
                continue;
            }

            // Check previous attempts for the student in this unit
            $attemptSql = "SELECT attempt_number, total_marks FROM supplementary_exam_marks WHERE admission_number = ? AND unit_code = ? AND semester = ? AND year = ? ORDER BY attempt_number DESC LIMIT 1";
            if ($attemptStmt = $conn->prepare($attemptSql)) {
                $attemptStmt->bind_param("ssss", $admissionNumber, $unitCode, $semester, $year);
                $attemptStmt->execute();
                $attemptResult = $attemptStmt->get_result();

                $previousAttempt = $attemptResult->fetch_assoc();
                $attemptNumber = $previousAttempt ? $previousAttempt['attempt_number'] + 1 : 1;

                // If 4th attempt and still fail, mark the student as discontinued
                if ($attemptNumber > 4) {
                    echo "<p style='color: red;'>Student {$studentName} ({$admissionNumber}) has reached the maximum of 4 attempts. Further attempts are not allowed.</p>";
                    continue;  // Skip further processing for this student
                }

                $totalMarks = $examMarks; // Only exam marks for supplementary exams
                $grade = '';
                $class = '';
                $studentStatus = 'Registered'; // Default student status

                if ($totalMarks >= 40) {
                    $grade = 'D';
                    $class = 'Pass';
                } else {
                    $grade = 'E';
                    $class = 'Fail';
                }

                // If it's the 4th attempt and the student fails, update to Discontinued
                if ($attemptNumber == 4 && $totalMarks < 40) {
                    $studentStatus = 'Discontinued'; // Mark as Discontinued on 4th failure
                    echo "<p style='color: red;'>Student {$studentName} ({$admissionNumber}) has failed the 4th attempt and has been discontinued.</p>";
                }

                // Update student status
                $updateStatusSql = "UPDATE students SET status = ? WHERE AdmissionNumber = ?";
                if ($statusUpdateStmt = $conn->prepare($updateStatusSql)) {
                    $statusUpdateStmt->bind_param("ss", $studentStatus, $admissionNumber);
                    if ($statusUpdateStmt->execute()) {
                        echo "Student status updated to {$studentStatus} for {$studentName} ({$admissionNumber}).";
                    } else {
                        echo "Error: " . $statusUpdateStmt->error;
                    }
                    $statusUpdateStmt->close();
                } else {
                    echo "Error: " . $conn->error;
                }

                // Insert the new supplementary exam marks
                $sql = "INSERT INTO supplementary_exam_marks (category_name, course_name, year, semester, unit_code, unit_name, admission_number, student_name, exam_marks, total_marks, grade, class, intake, attempt_number)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param(
                        'ssisssssddsssi',
                        $categoryName,
                        $courseName,
                        $year,
                        $semester,
                        $unitCode,
                        $unitName,
                        $admissionNumber,
                        $studentName,
                        $examMarks,
                        $totalMarks,
                        $grade,
                        $class,
                        $intake,
                        $attemptNumber
                    );

                    // Execute the statement
                    if ($stmt->execute()) {
                        echo "Supplementary exam marks submitted successfully for {$studentName} ({$admissionNumber})!";
                    } else {
                        echo "Error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    echo "Error: " . $conn->error;
                }

                $attemptStmt->close();
            } else {
                echo "Error: " . $conn->error;
            }
        }
    } else {
        echo "Error: Invalid input data.";
    }

    $conn->close();
}

?>
