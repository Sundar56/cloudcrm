<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        .container {
            background-color: aliceblue;
            padding: 20px;
        }

        .body-content {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            border: 1px solid #54585F;
            padding: 21px;
            border-radius: 8px;
        }

        .email-container {
            max-width: 520px;
            margin: 50px auto;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            max-width: 250px;
            width: 100%;
            height: auto;
        }

        .password-notify {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .password-box-container {
            background-color: #eaf4ff;
            padding: 15px;
            margin: 20px 0;
            border-left: 5px solid #3498db;
            border-radius: 6px;
        }

        .password-box {
            font-size: 18px;
            color: #333;
            font-weight: bold;
            text-align: center;
            background-color: #ffffff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }

        .footer {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-top: 40px;
        }

        .footer a {
            color: #3498db;
            text-decoration: none;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                padding: 20px;
                margin: 20px auto;
                width: 90%;
            }

            .header img {
                max-width: 200px;
            }

            .password-notify {
                font-size: 14px;
                margin-bottom: 15px;
            }

            .password-box {
                font-size: 14px;
                padding: 10px 15px;
            }

            .footer {
                font-size: 10px;
            }
        }

        @media only screen and (max-width: 400px) {
            .header img {
                max-width: 180px;
            }

            .password-notify {
                font-size: 14px;
            }

            .password-box {
                font-size: 16px;
                padding: 8px 12px;
            }

            .footer {
                font-size: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                @if($portalType == 'system')
                <img src="https://crmapi.sitecare.org/assets/img/cloud-crm-logo.png{{ Config::get('app.assets_version') }}" alt="CloudCRM Logo" style="width:240px;">
                @else
                <img src="{{ 'https://crmapi.sitecare.org' . $companyLogo }}" alt="Company Logo" style="width:90px;height:90px">
                @endif 
            </div>

            <!-- Password generation notification -->
            <div class="password-notify">
                Verify OTP
            </div>

            <!-- Body content -->
            <div class="body-content">
                <p>Hi {{$userName}}</p>
                <p>Below is your one time password:</p>

                <!-- Password Box with Border -->
                <div class="password-box-container">
                    <div class="password-box">
                        {{$otp}}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy;CloudCrm. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>