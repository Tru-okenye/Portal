<?php

// Include the database connection file
include_once __DIR__ . '/../../config/config.php';

// Define the endpoint for receiving payment advice requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data (the request body)
    $jsonRequest = file_get_contents('php://input');
    
    // Decode the JSON request to an associative array
    $requestData = json_decode($jsonRequest, true);

    // Check if required headers and body fields are present
    if (
        isset($_SERVER['HTTP_SERVICENAME']) &&
        isset($_SERVER['HTTP_MESSAGEID']) &&
        isset($_SERVER['HTTP_CONNECTIONID']) &&
        isset($_SERVER['HTTP_CONNECTIONPASSWORD']) &&
        isset($requestData['request']['TransactionReferenceCode']) &&
        isset($requestData['request']['TransactionDate']) &&
        isset($requestData['request']['TotalAmount']) &&
        isset($requestData['request']['AccountNumber']) &&
        isset($requestData['request']['InstitutionCode']) &&
        isset($requestData['request']['InstitutionName'])
    ) {
        // Extract headers
        $serviceName = $_SERVER['HTTP_SERVICENAME'];
        $messageID = $_SERVER['HTTP_MESSAGEID'];
        $connectionID = $_SERVER['HTTP_CONNECTIONID'];
        $connectionPassword = $_SERVER['HTTP_CONNECTIONPASSWORD'];

        // Extract body parameters
        $transactionReferenceCode = $requestData['request']['TransactionReferenceCode'];
        $transactionDate = $requestData['request']['TransactionDate'];
        $totalAmount = $requestData['request']['TotalAmount'];
        $accountNumber = $requestData['request']['AccountNumber'];
        $institutionCode = $requestData['request']['InstitutionCode'];
        $institutionName = $requestData['request']['InstitutionName'];

        // Validate the connectionID with your database
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
                // Validate the TransactionReferenceCode against the students table
                $sql = "SELECT * FROM students WHERE AdmissionNumber = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $transactionReferenceCode);
                $stmt->execute();
                $studentResult = $stmt->get_result();

                // Check if the student exists
                if ($studentResult->num_rows > 0) {
                    // Prepare a successful payment advice response
                    $response = [
                        "header" => [
                            "messageID" => $messageID,
                            "statusCode" => "200",
                            "statusDescription" => "Payment successfully received"
                        ],
                        "response" => [
                            "TransactionReferenceCode" => $transactionReferenceCode,
                            "TransactionDate" => $transactionDate,
                            "TransactionAmount" => $totalAmount,
                            "AccountNumber" => $accountNumber,
                            "AccountName" => "", // Set the account name here if available
                            "InstitutionCode" => $institutionCode,
                            "InstitutionName" => $institutionName
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
                        "response" => []
                    ];
                }
            } else {
                // Invalid connection password
                $response = [
                    "header" => [
                        "messageID" => $messageID,
                        "statusCode" => "401",
                        "statusDescription" => "Unauthorized access. Invalid connectionID or connectionPassword."
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
?>
