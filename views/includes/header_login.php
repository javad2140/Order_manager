<?php
// This file is a VIEW part.
// session_start() and error_reporting are handled by the central router (public/index.php)
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'ورود'; ?></title>
    
    <!-- Corrected Path for Local Bootstrap CSS -->
    <!-- All asset paths should be relative to the public/ folder (web root) for the browser -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome CSS for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Corrected Path for Custom CSS -->
    <!-- All asset paths should be relative to the public/ folder (web root) for the browser -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css"> 
</head>
<body>
