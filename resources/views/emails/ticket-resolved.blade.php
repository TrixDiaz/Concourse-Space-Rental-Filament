<x-mail::message>
    <p>Dear {{ $data['tenant_name'] }},</p>

    <p>We are pleased to inform you that your ticket submitted for {{ $data['space_name'] }} at {{ $data['concourse_name'] }} has been resolved.</p>

    <p>Ticket Details:</p>
    <p>Ticket Number: {{ $data['ticket_number'] }}</p>
    <p>Concern Type: {{ $data['concern_type'] }}</p>
    <p>Issue Description: {{ $data['description'] }}</p>
    <p>Resolution: {{ $data['resolution'] }}</p>

    <p>If you have any further questions or if the issue persists, please do not hesitate to reach out.</p>

    <p>Thank you for your patience, and we're glad to have resolved this matter for you.</p>

    <p>Regards,<br>COMS</p>
</x-mail::message>