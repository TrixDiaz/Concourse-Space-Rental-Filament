<x-mail::message>
    <div>
        <p>Dear {{ $tenantName }},</p>

        <p>This is your rent bill for the month of {{ $month }}:</p>

        <p>Monthly Rent: ₱{{ number_format($rentAmount, 2) }}</p>
        <p>Total Amount Due: ₱{{ number_format($totalAmount, 2) }}</p>

        <p>Please note that your payment is due by {{ $dueDate }}. To avoid a late fee of {{ $penalty }}%,
            please ensure your payment is made on or before this date.</p>

        <p>Regards,<br>COMS</p>
    </div>
</x-mail::message>