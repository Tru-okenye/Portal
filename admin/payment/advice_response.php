<?php
// database connection 
include_once __DIR__ . '/../../config/config.php';

// Define the endpoint for receiving validation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data (the request body)
    $rawPostData = file_get_contents('php://input');

    // Decode the JSON request directly
    $requestData = json_decode($rawPostData, true);

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
    // Check if required headers and body fields are present
    if (
        isset($requestData['header']['serviceName']) &&
        isset($requestData['header']['messageID']) &&
        isset($requestData['header']['connectionID']) &&
        isset($requestData['header']['connectionPassword']) &&
        isset($requestData['request']['TransactionReferenceCode']) &&
        isset($requestData['request']['TransactionDate']) &&
        isset($requestData['request']['TotalAmount']) &&
        isset($requestData['request']['AccountNumber']) &&
        isset($requestData['request']['DocumentReferenceNumber']) &&
        isset($requestData['request']['BankCode']) &&
        isset($requestData['request']['BranchCode']) &&
        isset($requestData['request']['PaymentDate']) &&
        isset($requestData['request']['PaymentMode']) &&
        isset($requestData['request']['InstitutionCode']) &&
        isset($requestData['request']['InstitutionName']) &&
        isset($requestData['request']['PaymentAmount']) 
    ) {
        // Extract headers
        $serviceName = $requestData['header']['serviceName'];
        $messageID = $requestData['header']['messageID'];
        $connectionID = $requestData['header']['connectionID'];
        $connectionPassword = $requestData['header']['connectionPassword'];
        
        // Extract body parameters
        $transactionReferenceCode = $requestData['request']['TransactionReferenceCode'];
        $transactionDate = $requestData['request']['TransactionDate'];
        $totalAmount = $requestData['request']['TotalAmount'];
        $currency = $requestData['request']['Currency'] ?? ''; // Optional field
        $documentReferenceNumber = $requestData['request']['DocumentReferenceNumber'];
        $bankCode = $requestData['request']['BankCode'];
        $branchCode = $requestData['request']['BranchCode'];
        $paymentDate = $requestData['request']['PaymentDate'];
        $paymentReferenceCode = $requestData['request']['PaymentReferenceCode'] ?? ''; // Optional field
        $paymentMode = $requestData['request']['PaymentMode'];
        $paymentAmount = $requestData['request']['PaymentAmount'];
        $additionalInfo = $requestData['request']['AdditionalInfo'] ?? ''; // Optional field
        $accountNumber = $requestData['request']['AccountNumber'];
        $accountName = $requestData['request']['AccountName'] ?? ''; // Optional field
        $InstitutionCode = $requestData['request']['InstitutionCode'];
        $InstitutionName = $requestData['request']['InstitutionName'] ?? ''; // Optional field
        $paymentCode = $requestData['request']['PaymentCode'] ?? ''; 

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
                // Validate the AccountNumber against the students table
                $sql = "SELECT * FROM students WHERE AdmissionNumber = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $accountNumber);
                $stmt->execute();
                $studentResult = $stmt->get_result();

                // Check if the student exists
                if ($studentResult->num_rows > 0) {
                    // Insert the payment details into the payments table
                    $insertSql = "
                        INSERT INTO payments (
                            TransactionReferenceCode, 
                            TransactionDate, 
                            TotalAmount, 
                            Currency, 
                            DocumentReferenceNumber, 
                            BankCode, 
                            BranchCode, 
                            PaymentDate, 
                            PaymentReferenceCode, 
                            PaymentMode, 
                            PaymentAmount, 
                            AdditionalInfo, 
                            AccountNumber, 
                            AccountName,
                            InstitutionCode,
                            InstitutionName
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bind_param(
                        "sssssssssssdssss", 
                        $transactionReferenceCode, 
                        $transactionDate, 
                        $totalAmount, 
                        $currency, 
                        $documentReferenceNumber, 
                        $bankCode, 
                        $branchCode, 
                        $paymentDate, 
                        $paymentReferenceCode, 
                        $paymentMode, 
                        $paymentAmount, 
                        $additionalInfo, 
                        $accountNumber, 
                        $accountName,
                        $InstitutionCode,
                        $InstitutionName
                    );
                    
                    if ($insertStmt->execute()) {
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
                                "AccountName" => $accountName,
                                "InstitutionCode" => $InstitutionCode,
                                "InstitutionName" => $InstitutionName
                            ]
                        ];
                    } else {
                        // Error while inserting into the database
                        $response = [
                            "header" => [
                                "messageID" => $messageID,
                                "statusCode" => "500",
                                "statusDescription" => "Error storing payment details"
                            ],
                            "response" => []
                        ];
                    }
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




