<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #18181b;
            background-color: #f4f4f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .field {
            margin-bottom: 20px;
        }
        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: #71717a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .field-value {
            font-size: 16px;
            color: #18181b;
        }
        .message-box {
            background-color: #f4f4f5;
            border-radius: 8px;
            padding: 20px;
            margin-top: 10px;
        }
        .message-box p {
            margin: 0;
            white-space: pre-wrap;
        }
        .footer {
            background-color: #f4f4f5;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #71717a;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nouveau message de contact</h1>
        </div>

        <div class="content">
            <div class="field">
                <div class="field-label">Nom</div>
                <div class="field-value">{{ $name }}</div>
            </div>

            <div class="field">
                <div class="field-label">Email</div>
                <div class="field-value">
                    <a href="mailto:{{ $email }}" style="color: #2563eb; text-decoration: none;">{{ $email }}</a>
                </div>
            </div>

            @if($phone)
            <div class="field">
                <div class="field-label">Telephone</div>
                <div class="field-value">{{ $phone }}</div>
            </div>
            @endif

            <div class="field">
                <div class="field-label">Sujet</div>
                <div class="field-value">{{ $subject }}</div>
            </div>

            <div class="field">
                <div class="field-label">Message</div>
                <div class="message-box">
                    <p>{{ $contactMessage }}</p>
                </div>
            </div>
        </div>

        <div class="footer">
            Message envoye depuis le formulaire de contact de
            <a href="{{ config('app.url') }}">Le Laboratoire Numerique</a>
        </div>
    </div>
</body>
</html>
