<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
</head>
<body>
    <h1>Payment Confirmation</h1>
    <p>Dear {{ $user->name }},</p>
    <p>Your payment for the tenant space has been successfully processed.</p>
    <p>Details:</p>
    <ul>
        <li>Concourse: {{ $tenant->concourse->name }}</li>
        <li>Amount Paid: ₱{{ number_format($tenant->monthly_payment, 2) }}</li>
        <li>Payment Status:
            Paid
             <!-- {{ $tenant->payment_status }} -->
        </li>
    </ul>
    <p>Thank you for your payment.</p>
</body>
</html>