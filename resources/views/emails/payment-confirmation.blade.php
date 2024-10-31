<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .penalty {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <h1>Payment Confirmation</h1>
    <p>Dear {{ $user->name }},</p>
    <p>We are writing to confirm that we have received your payment for the following:</p>

    <table>
        <thead>
            <tr>
                <th>Bill Type</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Penalty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @if($payment->water_bill > 0)
                @php
                    $waterPenalty = $payment->is_water_late ? ($payment->water_bill * 0.02) : 0;
                    $waterTotal = $payment->water_bill + $waterPenalty;
                @endphp
                <tr>
                    <td>Water Bill</td>
                    <td>₱{{ number_format($payment->water_bill, 2) }}</td>
                    <td>{{ $payment->water_due ? Carbon\Carbon::parse($payment->water_due)->format('F j, Y') : 'N/A' }}</td>
                    <td class="penalty">
                        @if($payment->is_water_late)
                            ₱{{ number_format($waterPenalty, 2) }} (2%)
                        @else
                            -
                        @endif
                    </td>
                    <td>₱{{ number_format($waterTotal, 2) }}</td>
                </tr>
            @endif
            @if($payment->electricity_bill > 0)
                @php
                    $electricityPenalty = $payment->is_electricity_late ? ($payment->electricity_bill * 0.02) : 0;
                    $electricityTotal = $payment->electricity_bill + $electricityPenalty;
                @endphp
                <tr>
                    <td>Electricity Bill</td>
                    <td>₱{{ number_format($payment->electricity_bill, 2) }}</td>
                    <td>{{ $payment->electricity_due ? Carbon\Carbon::parse($payment->electricity_due)->format('F j, Y') : 'N/A' }}</td>
                    <td class="penalty">
                        @if($payment->is_electricity_late)
                            ₱{{ number_format($electricityPenalty, 2) }} (2%)
                        @else
                            -
                        @endif
                    </td>
                    <td>₱{{ number_format($electricityTotal, 2) }}</td>
                </tr>
            @endif
            @if($payment->rent_bill > 0)
                @php
                    $rentPenalty = $payment->is_rent_late ? ($payment->rent_bill * 0.02) : 0;
                    $rentTotal = $payment->rent_bill + $rentPenalty;
                @endphp
                <tr>
                    <td>Rent</td>
                    <td>₱{{ number_format($payment->rent_bill, 2) }}</td>
                    <td>{{ $payment->rent_due ? Carbon\Carbon::parse($payment->rent_due)->format('F j, Y') : 'N/A' }}</td>
                    <td class="penalty">
                        @if($payment->is_rent_late)
                            ₱{{ number_format($rentPenalty, 2) }} (2%)
                        @else
                            -
                        @endif
                    </td>
                    <td>₱{{ number_format($rentTotal, 2) }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><strong>Total Amount Paid</strong></td>
                <td><strong>₱{{ number_format($payment->amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <p><strong>Payment Details:</strong></p>
    <ul>
        <li>Space: {{ $space->name }}</li>
        <li>Payment Method: {{ $payment->payment_method }}</li>
        <li>Payment Date: {{ $payment->created_at->format('F j, Y, g:i a') }}</li>
    </ul>

    <p>Thank you for your prompt payment.</p>
    <p>Best regards,<br>Your Property Management Team</p>
</body>
</html>
