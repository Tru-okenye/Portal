<?php
// Include the database connection file
include_once __DIR__ . '/../../config/config.php';

// Define the endpoint for receiving validation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data (the request body)
    $rawPostData = file_get_contents('php://input');

    
    // Extract JSON within <content> tags
    if (preg_match('/<content>(.*?)<\/content>/s', $rawPostData, $matches)) {
        $jsonRequest = $matches[1];  // Extracted JSON part
        $requestData = json_decode($jsonRequest, true);
        
        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = [
                "header" => [
                    "messageID" => "unknown",
                    "statusCode" => "400",
                    "statusDescription" => "Invalid JSON format."
                ],
                "response" => []
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    } else {
        // If <content> tag is not found, handle as an error
        $response = [
            "header" => [
                "messageID" => "unknown",
                "statusCode" => "400",
                "statusDescription" => "Invalid request format. JSON payload missing in <content> tag."
            ],
            "response" => []
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Check if required headers and body fields are present
    if (
        isset($requestData['header']['serviceName']) &&
        isset($requestData['header']['messageID']) &&
        isset($requestData['header']['connectionID']) &&
        isset($requestData['header']['connectionPassword']) &&
        isset($requestData['request']['TransactionReferenceCode']) &&
        isset($requestData['request']['TransactionDate']) &&
        isset($requestData['request']['InstitutionCode'])
    ) {
        // Extract headers
        $serviceName = $requestData['header']['serviceName'];
        $messageID = $requestData['header']['messageID'];
        $connectionID = $requestData['header']['connectionID'];
        $connectionPassword = $requestData['header']['connectionPassword'];

        // Extract body parameters
        $transactionReferenceCode = $requestData['request']['TransactionReferenceCode'];
        $transactionDate = $requestData['request']['TransactionDate'];
        $institutionCode = $requestData['request']['InstitutionCode'];

        // Validate the connectionID and connectionPassword with your database
        $sql = "SELECT * FROM connections WHERE connectionID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $connectionID);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a connectionID exists
        if ($result->num_rows > 0) {
            $connection = $result->fetch_assoc();

            // Verify the password (assuming it's hashed in the database)
            if (password_verify($connectionPassword, $connection['connectionPassword'])) {
                // Proceed to validate the TransactionReferenceCode (AdmissionNumber)
                $sql = "SELECT * FROM students WHERE AdmissionNumber = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $transactionReferenceCode);
                $stmt->execute();
                $result = $stmt->get_result();

                // Check if a student with the given AdmissionNumber exists
                if ($result->num_rows > 0) {
                    // Fetch student details
                    $student = $result->fetch_assoc();
                    $studentName = $student['FirstName'] . ' ' . $student['LastName'];
                    $accountNumber = $student['AdmissionNumber'];

                    // Prepare a successful validation response
                    $response = [
                        "header" => [
                            "messageID" => $messageID,
                            "statusCode" => "200",
                            "statusDescription" => "Successfully validated student"
                        ],
                        "response" => [
                            "TransactionReferenceCode" => $transactionReferenceCode,
                            "TransactionDate" => $transactionDate,
                            "TotalAmount" => 0.0,  // default value
                            "Currency" => "",  // default empty value
                            "AdditionalInfo" => $studentName,
                            "AccountNumber" => $accountNumber,
                            "AccountName" => $studentName,
                            "InstitutionCode" => $institutionCode,
                            "InstitutionName" => "IKIGAI COLLEGE OF INTERIOR DESIGN"
                        ]
                    ];
                } else {
                    // Student not found
                    $response = [
                        "header" => [
                            "messageID" => $messageID,
                            "statusCode" => "404",
                            "statusDescription" => "Student not found"
                        ],
                        "response" => [
                            "TransactionReferenceCode" => $transactionReferenceCode,
                        ]
                    ];
                }
            } else {
                // Invalid connection password
                $response = [
                    "header" => [
                        "messageID" => $messageID,
                        "statusCode" => "401",
                        "statusDescription" => "Invalid connection credentials"
                    ],
                    "response" => []
                ];
            }
        } else {
            // Connection ID not found
            $response = [
                "header" => [
                    "messageID" => $messageID,
                    "statusCode" => "404",
                    "statusDescription" => "Connection ID not found"
                ],
                "response" => []
            ];
        }
    } else {
        // Missing required headers or body fields
        $response = [
            "header" => [
                "messageID" => $messageID ?? "unknown",
                "statusCode" => "400",
                "statusDescription" => "Invalid request. Missing required headers or body parameters."
            ],
            "response" => []
        ];
    }

    // Return response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // If not a POST request, return an error
    $response = [
        "header" => [
            "messageID" => "unknown",
            "statusCode" => "405",
            "statusDescription" => "Invalid request method. Only POST requests are allowed."
        ],
        "response" => []
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
