<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeGuard CAPTCHA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .safeguard-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .safeguard-logo {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }
        .captcha-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #007bff;
            margin: 20px auto;
            cursor: pointer;
            position: relative;
        }
        .captcha-circle.verified::after {
            content: '✓';
            font-size: 50px;
            color: #007bff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        #result {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="safeguard-container">
        <svg class="safeguard-logo" viewBox="0 0 24 24" fill="#007bff">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        <h2>SafeGuard</h2>
        <p>Click and move your cursor inside the circle to verify:</p>
        <div id="captcha-circle" class="captcha-circle"></div>
        <p id="result"></p>
    </div>

    <script>
        let startTime, endTime, totalDistance = 0, lastX, lastY, isVerified = false;

        function distance(x1, y1, x2, y2) {
            return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
        }

        document.getElementById('captcha-circle').addEventListener('mouseenter', function(e) {
            if (!isVerified) {
                startTime = new Date().getTime();
                lastX = e.clientX;
                lastY = e.clientY;
            }
        });

        document.getElementById('captcha-circle').addEventListener('mousemove', function(e) {
            if (!isVerified) {
                totalDistance += distance(lastX, lastY, e.clientX, e.clientY);
                lastX = e.clientX;
                lastY = e.clientY;
            }
        });

        document.getElementById('captcha-circle').addEventListener('mouseleave', function() {
            if (!isVerified) {
                endTime = new Date().getTime();
                const duration = (endTime - startTime) / 1000;
                const averageSpeed = totalDistance / duration;

                if (duration < 0.5 || averageSpeed > 1000) {
                    document.getElementById('result').textContent = 'Suspected. Please try again.';
                    document.getElementById('result').style.color = 'red';
                } else {
                    verifyCaptcha();
                }

                totalDistance = 0;
            }
        });

        function verifyCaptcha() {
            fetch('/api/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    api_key: 'your_api_key_here', // Replace with actual API key
                    captcha_data: {
                        duration: (endTime - startTime) / 1000,
                        distance: totalDistance
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('result').textContent = 'Verified';
                    document.getElementById('result').style.color = 'green';
                    document.getElementById('captcha-circle').classList.add('verified');
                    isVerified = true;
                } else {
                    document.getElementById('result').textContent = 'Verification failed. Please try again.';
                    document.getElementById('result').style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').textContent = 'An error occurred. Please try again.';
                document.getElementById('result').style.color = 'red';
            });
        }
    </script>
</body>
</html>