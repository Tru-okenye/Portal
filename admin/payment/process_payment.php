<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $transaction_ref = $_POST['transaction_ref'];
    $transaction_date = $_POST['transaction_date'];
    $total_amount = $_POST['total_amount'];
    $currency = $_POST['currency'];
    $doc_ref = $_POST['doc_ref'];
    $bank_code = $_POST['bank_code'];
    $branch_code = $_POST['branch_code'];
    $payment_date = $_POST['payment_date'];
    $payment_ref = $_POST['payment_ref'];
    $payment_mode = $_POST['payment_mode'];
    $payment_amount = $_POST['payment_amount'];
    $additional_info = $_POST['additional_info'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];
    $institution_name = $_POST['institution_name'];
    $institution_code = $_POST['institution_code'];

    // Insert into the payments table
    $sql = "
        INSERT INTO payments (
            TransactionReferenceCode, TransactionDate, TotalAmount, Currency, 
            DocumentReferenceNumber, BankCode, BranchCode, PaymentDate, 
            PaymentReferenceCode, PaymentMode, PaymentAmount, AdditionalInfo, 
            AccountNumber, AccountName, InstitutionName, InstitutionCode
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssssssssi",
        $transaction_ref, $transaction_date, $total_amount, $currency,
        $doc_ref, $bank_code, $branch_code, $payment_date,
        $payment_ref, $payment_mode, $payment_amount, $additional_info,
        $account_number, $account_name, $institution_name, $institution_code
    );

    if ($stmt->execute()) {
        echo "Payment record successfully added!";
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=payment/fee_entry">';

    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
