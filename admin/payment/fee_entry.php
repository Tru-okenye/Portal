<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment</title>
    <style>
     
        form {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h2>Add Payment Record</h2>
<form method="POST" action="index.php?page=payment/process_payment" enctype="multipart/form-data">

    <label for="transaction_ref">Transaction Reference Code</label>
    <input type="text" id="transaction_ref" name="transaction_ref">

    <label for="transaction_date">Transaction Date</label>
    <input type="datetime-local" id="transaction_date" name="transaction_date">

    <label for="total_amount">Total Amount</label>
    <input type="text" id="total_amount" name="total_amount" required>

    <label for="currency">Currency</label>
    <input type="text" id="currency" name="currency">

    <label for="doc_ref">Document Reference Number</label>
    <input type="text" id="doc_ref" name="doc_ref" required>

    <label for="bank_code">Bank Code</label>
    <input type="text" id="bank_code" name="bank_code">

    <label for="branch_code">Branch Code</label>
    <input type="text" id="branch_code" name="branch_code">

    <label for="payment_date">Payment Date</label>
    <input type="datetime-local" id="payment_date" name="payment_date">

    <label for="payment_ref">Payment Reference Code</label>
    <input type="text" id="payment_ref" name="payment_ref">

    <label for="payment_mode">Payment Mode</label>
    <input type="text" id="payment_mode" name="payment_mode" required>

    <label for="payment_amount">Payment Amount</label>
    <input type="text" id="payment_amount" name="payment_amount" required>

    <label for="additional_info">Additional Info</label>
    <textarea id="additional_info" name="additional_info"></textarea>

    <label for="account_number">Account Number</label>
    <input type="text" id="account_number" name="account_number" required>

    <label for="account_name">Account Name</label>
    <input type="text" id="account_name" name="account_name">

    <label for="institution_name">Institution Name</label>
    <input type="text" id="institution_name" name="institution_name">

    <label for="institution_code">Institution Code</label>
    <input type="number" id="institution_code" name="institution_code" required>

    <button type="submit">Submit Payment</button>
</form>

</body>
</html>
