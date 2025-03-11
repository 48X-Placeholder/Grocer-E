<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../functions/load.php";

if (!is_user_logged_in()) {
	header("Location: ".SITE_URL.'login'); // Redirect to dashboard
	exit(); // Ensure no further code is executed after redirect
}

$source = isset($_GET['source']) ? $_GET['source'] : 'inventory'; // Default to inventory
if ($source === 'shopping_list') {
    $backLabel = 'Back to Shopping List';
    $backHref = '../shopping-list/index.php';
} else {
    $backLabel = 'Back to Inventory';
    $backHref = '../inventory/index.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Barcode Scanner</title>
  <script src="https://unpkg.com/quagga"></script>
  <style>
    body { margin: 0; padding: 0; font-family: sans-serif; text-align: center; }
    h2 { margin: 20px 0 10px; }
    #buttonContainer { display: inline-flex; gap: 10px; margin-bottom: 20px; }
    #startScan, #backButton { padding: 10px 20px; font-size: 16px; border: none; cursor: pointer; }
    #startScan { background-color: #007bff; color: white; }
    #backButton { background-color: #28a745; color: white; }
    #videoWrapper { display: inline-block; width: 640px; height: 480px; border: 2px solid #ccc; overflow: hidden; margin-bottom: 20px; }
    #scanner { width: 100%; height: 100%; object-fit: cover; }
    #loading, #errorMessage, #barcodeResult { margin: 10px 0; font-weight: bold; }
    #loading { color: red; display: none; }
    #errorMessage { color: red; }
  </style>
</head>
<body>
  <h2>Scan a Product</h2>
  <div id="buttonContainer">
    <button id="startScan">Start Scanner</button>
    <button id="backButton" onclick="window.location.href='<?php echo $backHref; ?>';"><?php echo $backLabel; ?></button>
  </div>
  <p id="errorMessage"></p>
  <p id="barcodeResult"></p>
  <p id="loading">Fetching product details...</p>
  <div id="videoWrapper">
    <video id="scanner" autoplay playsinline></video>
  </div>

  <script>
    let scanSource = "<?php echo $source; ?>"; // Pass source to JavaScript
    let lastScannedCode = "";
    let scanAttempts = {};
    let scanningLocked = false; // Prevents multiple submissions

    document.getElementById("startScan").addEventListener("click", function () {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        document.getElementById("errorMessage").innerText = "Error: Camera access not supported.";
        return;
      }
      navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(function (stream) {
          document.getElementById("scanner").srcObject = stream;
          document.getElementById("errorMessage").innerText = "Camera access granted. Initializing scanner...";
          setTimeout(startQuagga, 1000);
        })
        .catch(function (error) {
          document.getElementById("errorMessage").innerText = "Error: Camera access denied.";
        });
    });

    function startQuagga() {
      Quagga.init({
        inputStream: { name: "Live", type: "LiveStream", target: "#videoWrapper", constraints: { width: 640, height: 480, facingMode: "environment" } },
        decoder: { readers: ["ean_reader", "upc_reader", "code_128_reader"] },
        locate: true
      }, function (err) {
        if (err) {
          document.getElementById("errorMessage").innerText = "Error: Scanner failed to initialize.";
          return;
        }
        Quagga.start();
      });

      Quagga.onDetected(function (data) {
        if (scanningLocked) return; // Prevent multiple submissions
        let barcode = data.codeResult.code;
        scanAttempts[barcode] = (scanAttempts[barcode] || 0) + 1;
        if (scanAttempts[barcode] >= 3) {
          scanningLocked = true; // Lock scanning
          lastScannedCode = barcode;
          scanAttempts = {};
          document.getElementById("loading").style.display = "block";

          fetch('<? echo SITE_URL.'api/scan'?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ barcode: barcode, source: scanSource })
          })
          .then(response => response.json())
          .then(data => {
            if (data.error) alert("Error: " + data.error);
            else alert("Product Added: " + data.product_name);
            window.location.href = "<? echo SITE_URL?>"+((scanSource === "shopping_list") ? 'shopping-list' : 'inventory');
          })
          .catch(() => document.getElementById("errorMessage").innerText = "Server error.")
          .finally(() => {
            document.getElementById("loading").style.display = "none";
            setTimeout(() => { scanningLocked = false; }, 5000); // Unlock scanning after 5 seconds
          });
        }
      });
    }
  </script>
</body>
</html>




