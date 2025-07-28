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
            background:aliceblue;
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
        }
        .header img {
            max-width: 150px;
        }
        .password-notify {
            font-size: 22px;
            font-weight: bold;
            color: #4682b4;
            margin: 20px 0;
        }
        .body-content {
            padding: 15px;
            text-align: left;
            color: #333;
            font-size: 16px;
        }
        .login-button {
            display: inline-block;
            background-color: #4682b4;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
            text-align: center;
        } 
        .note {
            font-style: italic;
            color: #888;
            margin-top: 20px;
        }
        .name-font{
            font-size: 24px;
        }
        .login-button:hover{
            background-color: rgba(70, 130, 180, 0.15);
            color: #4682b4;
        }   
        .footer {
            background-color: rgba(70, 130, 180, 0.15);
            color: #4682b4;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .loginbox{
            text-align: center;
        }
        .password-box-container {
            background-color: rgba(70, 130, 180, 0.15);
            padding: 15px;
            margin-left:175px;
            border-left: 5px solid #4682b4;
            border-radius: 6px;
            text-align: center;
            width:50%;
        }
        .password-box {
            font-size: 15px;
            color: #333;
            font-weight: bold;
            background-color: #ffffff;
            padding: 10px 15px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        /* Responsive Design */
        @media screen and (max-width: 600px) {
            .container {
                width: 95%;
            }
            .body-content {
                padding: 15px;
            }
            .reset-button, .login-button {
                width: 100%;
                text-align: center;
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
            <div class="password-notify">
                Password Reset Request
            </div>
            <div class="body-content">
                <p class="name-font">Hi {{$username}},</p> 
                <p>We’ve generated a new temporary password for you. Below is your new password:</p>
                
                <div class="password-box-container">
                    <div class="password-box">{{$password}}</div>
                </div>
                
                <p>Please log in using this password, and once you’ve logged in, we recommend changing it to something more secure.</p>

                <div class="loginbox">
                    @if($portalType == 'system')
                    <a href="{{ url('https://systemportal.sitecare.org/login') }}" class="login-button" style="color:white;">Click here to login</a>
                    @else
                    <a href="{{ url('https://' . $loginUrl . '/login') }}" class="login-button" style="color:white;">Click here to login</a>
                    @endif  
                    <p class="note">If you did not request this, please ignore this email</p>
                </div>                                              
            </div>
            <!-- Footer -->
            <div class="footer">
                <p>&copy; CloudCRM. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
