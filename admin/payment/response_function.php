<?php

// Define the endpoint for receiving validation requests
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
        isset($requestData['request']['InstitutionCode'])
    ) {
        // Extract headers
        $serviceName = $_SERVER['HTTP_SERVICENAME'];
        $messageID = $_SERVER['HTTP_MESSAGEID'];
        $connectionID = $_SERVER['HTTP_CONNECTIONID'];
        $connectionPassword = $_SERVER['HTTP_CONNECTIONPASSWORD'];

        // Extract body parameters
        $transactionReferenceCode = $requestData['request']['TransactionReferenceCode'];
        $transactionDate = $requestData['request']['TransactionDate'];
        $institutionCode = $requestData['request']['InstitutionCode'];

        // Here you would validate the headers and the body parameters
        // Example: Check if the connection ID and password match with your database

        // Dummy data for a successful validation (you can replace this with actual student data)
        $studentName = "Wanyama Jostine Anyango";
        $institutionName = "Eldoret University";

        // If validation is successful, return a response
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
                "AccountNumber" => $transactionReferenceCode,
                "AccountName" => $studentName,
                "InstitutionCode" => $institutionCode,
                "InstitutionName" => $institutionName
            ]
        ];

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

