<x-mail::message>
    <div>
        <p>Dear {{ $tenantName }},</p>

        <p>Here is your bill for the month of {{ $month }}:</p>

        @if(isset($waterConsumption))
        <p>Water Consumption: {{ $waterConsumption }}</p>
        <p>Water Rate: ₱{{ number_format($waterRate, 2) }}</p>
        <p>Total Water Bill: ₱{{ number_format($waterBill, 2) }}</p>
        @endif

        @if(isset($electricityConsumption))
        <p>Electricity Consumption: {{ $electricityConsumption }}</p>
        <p>Electricity Rate: ₱{{ number_format($electricityRate, 2) }}</p>
        <p>Total Electricity Bill: ₱{{ number_format($electricityBill, 2) }}</p>
        @endif

        <p>Subtotal: ₱{{ number_format($subtotal, 2) }}</p>

        <p>Please note that your due date is {{ $dueDate }}. To avoid a penalty of {{ $penalty }}%, 
            please make your payment on or before this date.</p>

        <p>Regards,<br>COMS</p>
    </div>
</x-mail::message>
