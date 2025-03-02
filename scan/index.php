<?php
// You can include any necessary PHP code here if needed.
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Barcode Scanner</title>
  <!-- QuaggaJS Library -->
  <script src="https://unpkg.com/quagga"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: sans-serif;
      text-align: center; /* Centers elements horizontally */
    }

    h2 {
      margin: 20px 0 10px;
    }

    /* Container for buttons */
    #buttonContainer {
      display: inline-flex; /* Puts buttons side by side */
      gap: 10px;            /* Spacing between buttons */
      margin-bottom: 20px;  /* Space below the buttons */
    }

    /* Individual buttons */
    #startScan, #backToDashboard {
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      cursor: pointer;
    }

    #startScan {
      background-color: #007bff;
      color: white;
    }

    #backToDashboard {
      background-color: #28a745;
      color: white;
    }

    /* Container for the scanner/video */
    #videoWrapper {
      display: inline-block;  /* Keep it inline so it centers nicely */
      position: relative;     /* So we can position elements if needed */
      width: 640px;
      height: 480px;
      border: 2px solid #ccc;
      overflow: hidden;
      margin-bottom: 20px;
    }

    #scanner {
      width: 100%;
      height: 100%;
      object-fit: cover; /* Fill the video area nicely */
    }

    #loading {
      display: none;
      color: red;
      font-weight: bold;
    }

    #errorMessage {
      color: red;
      font-weight: bold;
      margin: 10px 0;
    }

    #barcodeResult {
      margin: 10px 0;
    }
  </style>
</head>
<body>
  <h2>Scan a Product</h2>
  
  <!-- Button Container at the top -->
  <div id="buttonContainer">
    <button id="startScan">Start Scanner</button>
    <button id="backToDashboard" onclick="window.location.href='../dashboard/index.php';">
      Back to Dashboard
    </button>
  </div>

  <!-- Display messages -->
  <p id="errorMessage"></p>
  <p id="barcodeResult"></p>
  <p id="loading">Fetching product details...</p>

  <!-- Container for the camera feed -->
  <div id="videoWrapper">
    <video id="scanner" autoplay playsinline></video>
  </div>

  <script>
    let lastScannedCode = "";
    let scanAttempts = {};

    document.getElementById("startScan").addEventListener("click", function () {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        document.getElementById("errorMessage").innerText = 
          "Error: Camera access not supported in this browser.";
        return;
      }

      navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
          document.getElementById("scanner").srcObject = stream;
          document.getElementById("errorMessage").innerText = 
            "Camera access granted. Initializing scanner...";
          setTimeout(startQuagga, 1000);
        })
        .catch(function (error) {
          document.getElementById("errorMessage").innerText = 
            "Error: Camera access was denied or blocked.";
          console.error("Camera access error:", error);
        });
    });

    function startQuagga() {
      Quagga.init({
        inputStream: {
          name: "Live",
          type: "LiveStream",
          target: document.querySelector("#videoWrapper"), // Target only the video container
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
          patchSize: "x-large",
          halfSample: false
        },
        numOfWorkers: 4,
        frequency: 5,
        multiple: false
      }, function (err) {
        if (err) {
          console.error("Quagga initialization failed:", err);
          document.getElementById("errorMessage").innerText = 
            "Error: QuaggaJS failed to initialize.";
          return;
        }
        Quagga.start();
        document.getElementById("errorMessage").innerText = 
          "Scanner initialized. Ready to scan!";
      });

      // Log processed frames for debugging
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

        // Stability check: require the same code to appear 3 times
        scanAttempts[barcode] = (scanAttempts[barcode] || 0) + 1;
        console.log(`Barcode detected: ${barcode}, Attempts: ${scanAttempts[barcode]}`);

        if (scanAttempts[barcode] >= 3) {
          lastScannedCode = barcode;
          scanAttempts = {}; // Reset attempts

          console.log("Scanned barcode confirmed:", barcode);
          document.getElementById("barcodeResult").innerText = "Scanned: " + barcode;
          document.getElementById("loading").style.display = "block";

          // Send barcode to backend
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
            document.getElementById("errorMessage").innerText = 
              "Error: Could not connect to server.";
          })
          .finally(() => {
            document.getElementById("loading").style.display = "none";
            setTimeout(() => { lastScannedCode = ""; }, 3000);
          });
        }
      });
    }
  </script>
</body>
</html>



