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
    <p>We are writing to confirm that we have received your payment for the following:</p>
    <ul>
        @if($payment->water_bill > 0)
            <li>Water Bill: ₱{{ number_format($payment->water_bill, 2) }}</li>
        @endif
        @if($payment->electricity_bill > 0)
            <li>Electricity Bill: ₱{{ number_format($payment->electricity_bill, 2) }}</li>
        @endif
        @if($payment->rent_bill > 0)
            <li>Rent: ₱{{ number_format($payment->rent_bill, 2) }}</li>
        @endif
    </ul>
    <p>Total Amount Paid: ₱{{ number_format($payment->amount, 2) }}</p>
    <p>Space: {{ $space->name }}</p>
    <p>Payment Method: {{ $payment->payment_method }}</p>
    <p>Payment Date: {{ $payment->created_at->format('F j, Y, g:i a') }}</p>
    <p>Thank you for your prompt payment.</p>
    <p>Best regards,<br>Your Property Management Team</p>
</body>
</html>
