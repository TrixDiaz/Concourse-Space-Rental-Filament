<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Rejected</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        h1 {
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <h1>Application Rejected</h1>

    <p>Dear {{ $application->owner_name }},</p>

    <p>We regret to inform you that your application for space rental has been rejected.</p>

    <h2>Application Details:</h2>
    <p>
        <strong>Business Name:</strong> {{ $application->business_name }}<br>
        <strong>Space Name:</strong> {{ $application->space->name }}<br>
        <strong>Space Address:</strong> {{ $application->concourse->address }}
    </p>

    <p>If you have any questions regarding this decision, please don't hesitate to contact us.</p>

    <p>Thank you for your interest in our services.</p>

    <p>
        Best regards,<br>
        {{ config('app.name') }}
    </p>
</body>

</html>
