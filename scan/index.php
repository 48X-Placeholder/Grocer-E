<?php

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Scanner</title>
    <script src="https://unpkg.com/quagga"></script>
    <style>
        #scanner-container {
            text-align: center;
            margin: 20px;
        }
        #scanner {
            width: 100%;
            max-width: 600px;
            margin: auto;
            display: block;
        }
        #loading {
            display: none;
            color: red;
            font-weight: bold;
        }
        #errorMessage {
            color: red;
            font-weight: bold;
        }
        #startScan {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <h2>Scan a Product</h2>
    <div id="scanner-container">
        <video id="scanner" autoplay playsinline></video>
        <p id="barcodeResult"></p>
        <p id="loading">Fetching product details...</p>
        <p id="errorMessage"></p> <!-- Error message display -->
        <button id="startScan">Start Scanner</button> <!-- Button to manually request camera access -->
    </div>

    <script>
        let lastScannedCode = "";
        let scanAttempts = {}; // Tracks barcode stability

        document.getElementById("startScan").addEventListener("click", function () {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                document.getElementById("errorMessage").innerText = "Error: Camera access not supported in this browser.";
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(function (stream) {
                    document.getElementById("scanner").srcObject = stream;
                    document.getElementById("errorMessage").innerText = "Camera access granted. Initializing scanner...";
                    setTimeout(startQuagga, 1000);
                })
                .catch(function (error) {
                    document.getElementById("errorMessage").innerText = "Error: Camera access was denied or blocked.";
                    console.error("Camera access error:", error);
                });
        });

        function startQuagga() {
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector("#scanner-container"),
                    constraints: {
                        width: 640,
                        height: 480,
                        facingMode: "environment"
                    }
                },
                decoder: { 
                    readers: [
                        "ean_reader", 
                        "ean_8_reader",
                        "upc_reader", 
                        "upc_e_reader",
                        "code_128_reader"
                    ]
                },
                locate: true,
                locator: {
                    patchSize: "x-large", // Increases detection area
                    halfSample: false
                },
                numOfWorkers: 4, // Uses multiple CPU cores for faster detection
                frequency: 5, // Lowers frequency to prevent misreads
                multiple: false
            }, function (err) {
                if (err) {
                    console.error("Quagga initialization failed:", err);
                    document.getElementById("errorMessage").innerText = "Error: QuaggaJS failed to initialize.";
                    return;
                }
                Quagga.start();
                document.getElementById("errorMessage").innerText = "Scanner initialized. Ready to scan!";
            });

            // Log detected barcodes for debugging
            Quagga.onProcessed(function(result) {
                if (result && result.codeResult) {
                    console.log("Barcode detected but not confirmed:", result.codeResult.code);
                } else {
                    console.log("Barcode detected but not confirmed: undefined");
                }
            });

            // When a barcode is successfully detected
            Quagga.onDetected(function (data) {
                if (!data || !data.codeResult || !data.codeResult.code) {
                    console.warn("Barcode detection failed: no codeResult found");
                    return;
                }

                let barcode = data.codeResult.code;
                
                // Prevent duplicate reads and require stability
                if (!scanAttempts[barcode]) {
                    scanAttempts[barcode] = 1;
                } else {
                    scanAttempts[barcode]++;
                }

                console.log(`Barcode detected: ${barcode}, Attempts: ${scanAttempts[barcode]}`);

                // Only accept if it has been seen 3 times in a row
                if (scanAttempts[barcode] >= 3) {
                    lastScannedCode = barcode;
                    scanAttempts = {}; // Reset attempts tracking

                    console.log("Scanned barcode confirmed:", barcode);
                    document.getElementById("barcodeResult").innerText = "Scanned: " + barcode;
                    
                    // Show loading message
                    document.getElementById("loading").style.display = "block";

                    // Send barcode to backend PHP script
                    fetch('process_barcode.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ barcode: barcode })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert("Error: " + data.error);
                        } else {
                            alert("Product Added: " + data.product_name);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById("errorMessage").innerText = "Error: Could not connect to server.";
                    })
                    .finally(() => {
                        document.getElementById("loading").style.display = "none";
                        setTimeout(() => { lastScannedCode = ""; }, 3000); // Reset after 3 seconds
                    });
                }
            });
        }
    </script>

</body>
</html>