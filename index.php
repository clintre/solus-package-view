<?php
// Will want to create a better display for failures. Just a place holder.
if (empty($_GET['pkg'])){
    die("Error: Invalid or missing package name!");
}

$packageName    = sanitizePackageName($_GET['pkg']);
$pkgPath        = getPackagePth($packageName);
$pymlUrl        = 'https://raw.githubusercontent.com/getsolus/packages/refs/heads/main/packages/'. $pkgPath .'package.yml';
$pkgRepo        = 'https://github.com/getsolus/packages/tree/main/packages/' . $pkgPath;

// Home folder (root or in sub folder. If in root of domain just set to /
$homeRoot = '/pkgs/';

// Get the package.yml content
$yamlContent = @file_get_contents($pymlUrl);

// If does not exist, die
// Will want to create a better display for failures. Just a place holder.
if ($yamlContent === false || trim($yamlContent) === '404: Not Found') {
    die("Error: Could not find package.yml for package '{$packageName}'. Please check the package name.\n");
}

// This allows us to customize which yaml keys to display
// If empty it will display all keys. Add keys 'key1','key2', etc.
$allowedKeys = [];

/**
 * KEY OPTIONS
 * name
 * version
 * homepage
 * license
 * release
 * source
 * component
 * summary
 * description
 * builddeps
 * rundeps
 * checkdeps
 * clang
 * optimize
 * setup
 * build
 * install
 * check
 * pattern
 */

// Just some clean up for display
$yamlContent = preg_replace('/^[\xef\xbb\xbf]+/', '', $yamlContent);
$yamlContent = str_replace("\r\n", "\n", $yamlContent);


$lines = explode("\n", $yamlContent);
$output = [];
$isKeeping = false;

foreach ($lines as $line) {
    // Drop root-level comments )
    if (preg_match('/^#/', $line)) {
        continue;
    }


    if (preg_match('/^\s*([a-zA-Z0-9_-]+)\s*:/', $line, $matches)) {
        $currentKey = $matches[1];

        // If the key is in our allowed list, turn saving ON. Otherwise, turn it OFF.
        if ((empty($allowedKeys)) || (in_array($currentKey, $allowedKeys))) {
            $isKeeping = true;
            $cleanLine = preg_replace('/\s*\|\s*$/', '', $line);
            $output[] = $cleanLine;
        } else {
            $isKeeping = false;
        }
    }
    else {
        if ($isKeeping) {
            $output[] = $line;
        }
    }
}
$packageInfo = implode("\n", $output);
$packageInfo = trim($packageInfo);

// This will get the proper folder. Will have to test if it catches non-python packages incorrectly
function getPackagePth($packageName) {
    if (str_starts_with($packageName, 'py')) {
        $folder = 'py';
    } else {
        $folder = substr($packageName, 0, 1);
    }

    return sprintf(
        "%s/%s/",
        $folder,
        $packageName
    );
}

// Clean up the get input and sanitize it
function sanitizePackageName($rawName) {

    if (empty($rawName)) {
        die("Error: Invalid package name provided.\n");
    }
    $name = strtolower(trim($rawName));
    $name = preg_replace('/[^a-z0-9\-\+\.]/', '', $name);
    return $name;
}

// HTML output is below. I am using bootstrap, but we can change this if we want
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solus Package - <?= $packageName ?></title>
    <link rel="icon" type="image/png" href="<?= $homeRoot ?>assets/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?= $homeRoot ?>assets/favicon.svg" />
    <link rel="shortcut icon" href="<?= $homeRoot ?>assets/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $homeRoot ?>assets/apple-touch-icon.png" />
    <link rel="manifest" href="<?= $homeRoot ?>assets/site.webmanifest" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fork-awesome@1.2.0/css/fork-awesome.min.css" integrity="sha256-XoaMnoYC5TH6/+ihMEnospgm0J1PM/nioxbOUdnM8HY=" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" rel="stylesheet" />
    <style>
    /* Style the pre block to look like a textbox */
    pre.textbox-style {
        border: 1px solid #ccc;
                border-radius: 4px;
                padding: 12px;
                background-color: #0A0E14;
                overflow-y: auto; /* Adds a scrollbar if lines are too long */
                font-family: monospace;
                font-size: 14px;
            }
    /* Override Prism.js defaults for this specific block */
    pre.textbox-style,
    pre.textbox-style code.language-yaml {
        white-space: pre-wrap !important;
        overflow-wrap: break-word !important;
        word-wrap: break-word !important;
    }
    </style>
</head>
<body class="bg-dark text-light" data-bs-theme="dark">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="background-color: rgba(245, 245, 245, 0.05);">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="assets/solus-logo.png" alt="Logo" height="24" width="auto" class="d-inline-block align-text-top">
Solus Package - <?= $packageName ?>
</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="https://getsol.us/">Website</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://discuss.getsol.us/">Forum</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://github.com/getsolus/packages">Packages</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<br>
<div class="container">
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col-8">
                    <p><b>Package Information</b></p>
                </div>
                <div class="col-4 text-end">
                    <a class="btn btn-sm btn-primary" href="<?= $pkgRepo ?>">Package Repo</a>
                </div>
            </div>

            <pre class="textbox-style" style="margin: 0;"><code class="language-yaml"><?= $packageInfo ?></code></pre>
        </div>
    </div>
    <div class="row">
        <div class="col"><hr></div>
    </div>
</div>
<footer class="bg-body-tertiary text-center text-lg-start">
    <div class="text-center p-3" style="background-color: rgba(245, 245, 245, 0.05);">
        Copyright © 2015-<?= date('Y') ?> Solus Project. The Solus logo is Copyright © 2016-<?= date('Y') ?> Solus Project.
    </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-yaml.min.js"></script>
</body>
</html>
