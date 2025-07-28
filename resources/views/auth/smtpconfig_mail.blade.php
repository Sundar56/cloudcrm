<!DOCTYPE html>
<html>
<head>
    <style>           
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
            background: aliceblue;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .email-container {
            padding: 20px;
            text-align: center;
        }
        .header {
            background-color: rgba(70, 130, 180, 0.15);
            padding: 24px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .body-content {
            padding: 20px;
            text-align: left;
            color: #333;
            font-size: 16px;
        }
        .name-font {
            font-size: 24px;
            font-weight: bold;
            color: #2C3E50;
        }
        .config-content {
            margin-top: 20px;
            font-size: 16px;
            line-height: 1.5;
            color: #555;
            text-align: left;
        }
        .footer {
            font-size: 14px;
            color: #777;
            margin-top: 20px;
            text-align: center;
        }
        @media screen and (max-width: 600px) {
            .container {
                width: 95%;
            }
            .body-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-container">
            <div class="header">
                Email Configuration Test Mail
            </div>
            <div class="body-content">
                <p class="name-font">Hi, {{$companyName}}</p> 
                <p>We are pleased to inform you that this is a test email sent to verify your SMTP configuration</p>
                <p>If you have received this message, it indicates that your SMTP setup is correct, and your system is capable of sending emails</p>
                <div class="footer">
                    <p>Thank you for using our service!</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
