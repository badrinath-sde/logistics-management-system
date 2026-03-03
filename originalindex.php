<?php

$conn = new mysqli('localhost', 'root', '', 'logistics_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/SMTP.php";
require "PHPMailer/src/Exception.php";

if (isset($_POST['send_mail'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = nl2br(htmlspecialchars($_POST['message']));

    $adminEmail = "svlogistics.sales@gmail.com";
    $subject = "New Contact Form Submission";

    $body = "
        <h2>Contact Form Submission</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Message:</strong><br>$message</p>
    ";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email_here';
        $mail->Password = 'your_app_password_here';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('svlogistics.sales@gmail.com', 'Website Contact Form');
        $mail->addAddress('svlogistics.sales@gmail.com', 'Admin');
        $mail->addReplyTo($email, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        echo "<div class='alert alert-success mt-3'>Message sent successfully!</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger mt-3'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
    }
}
if (isset($_GET['trackid'])) {
    header('Content-Type: application/json');
    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "DB connection failed"]);
        exit;
    }

    $trackId = $_GET['trackid'];
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE billid = ?");
    $stmt->bind_param("s", $trackId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "not_found"]);
    } else {
        $data = $result->fetch_assoc();
        echo json_encode(["status" => "ok", "data" => $data]);
    }
    exit; // stop executing HTML part
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SV Logistics - Logistics Solutions in Coimbatore Region</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #0b8b19;
            --dark: rgb(46, 41, 121);
            --light: #f8f9fa;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            color: #444;
            overflow-x: hidden;
            background-color: #f9fbfd;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--dark);
        }

        .top-bar {
            background-color: var(--dark);
            color: white;
            font-size: 0.9rem;
            padding: 8px 0;
        }

        .top-bar a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .top-bar a:hover {
            color: white;
        }

        .social-icons a {
            margin-left: 15px;
            font-size: 16px;
        }

        .navbar {
            padding: 15px 0;
            transition: all 0.4s;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 28px;
            color: var(--primary);
        }

        .navbar-brand span {
            color: var(--secondary);
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            font-size: 16px;
            padding: 8px 15px;
            color: var(--dark);
            transition: all 0.3s;
            position: relative;
        }

        .navbar-nav .nav-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary);
            bottom: 0;
            left: 15px;
            transition: width 0.3s;
        }

        .navbar-nav .nav-link:hover:after,
        .navbar-nav .nav-link.active:after {
            width: calc(100% - 30px);
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--primary);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #0d5cb6;
            border-color: #0d5cb6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.3);
        }

        .btn-secondary {
            background-color: #e05e00;
            border-color: #e05e00;
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background-color: #e05e00;
            border-color: #e05e00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 0, 0.3);
        }

        .hero-section {
            background: linear-gradient(90deg, rgba(26, 115, 232, 0.8) 0%, rgba(42, 137, 255, 0.8) 100%), url('./Asset/Header.jpg') center/cover;
            padding: 140px 0 100px;
            color: white;
        }

        .hero-heading {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-subheading {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .section-title {
            position: relative;
            margin-bottom: 50px;
            text-align: center;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            display: inline-block;
            padding-bottom: 15px;
        }

        .section-title p {
            padding-bottom: 15px;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: var(--primary);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .service-card {
            background: white;
            border-radius: 8px;
            overflow: visible;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            margin-bottom: 30px;
            height: 100%;
            position: relative;
            border: 1px solid rgba(26, 115, 232, 0.1);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border-color: rgba(26, 115, 232, 0.2);
        }

        .service-icon {
            background: var(--primary);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -40px auto 20px;
            color: white;
            font-size: 30px;
        }

        .service-card h3 {
            color: var(--dark);
            font-size: 1.5rem;
        }

        .location-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--secondary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .about-img {
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .tracking-section {
            background-color: #f0f7ff;
            padding: 80px 0;
        }

        .tracking-box {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .stats-section {
            background: linear-gradient(90deg, var(--primary) 0%, #3d8bfd 100%);
            color: white;
            padding: 80px 0;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin: 15px;
            position: relative;
        }

        .testimonial-card:before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 60px;
            font-family: Georgia, serif;
            color: var(--primary);
            opacity: 0.2;
        }

        .client-img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 20px;
            border: 3px solid var(--primary);
        }

        .cta-section {
            background: linear-gradient(90deg, var(--dark) 0%, #3a506b 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .footer {
            background: #1e2a38;
            color: rgba(255, 255, 255, 0.7);
            padding: 80px 0 0;
        }

        .footer h4 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
        }

        .footer h4:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: var(--secondary);
            bottom: 0;
            left: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: white;
            padding-left: 5px;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            margin-right: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .copyright {

            background: #1e2a38;
            padding: 20px 0;
            margin-top: 50px;
        }

        .page-section {
            padding: 100px 0;
        }

        .service-locations {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .location-list li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .location-list li:before {
            content: '\f3c5';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: var(--primary);
            margin-right: 10px;
        }

        .contact-form {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .contact-info-box {
            background: var(--primary);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .contact-info-box h3 {
            color: white;
        }

        .map-container {
            border-radius: 10px;
            overflow: hidden;
            height: 300px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .page-header {
            background: linear-gradient(90deg, rgba(26, 115, 232, 0.9) 0%, rgba(42, 137, 255, 0.9) 100%), url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80') center/cover;
            padding: 120px 0 80px;
            color: white;
            text-align: center;
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 30px;
            display: inline-flex;
        }

        .breadcrumb a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: white;
        }

        .tab-content {
            padding: 30px 0;
        }

        .nav-tabs .nav-link {
            font-weight: 600;
            padding: 15px 25px;
            border: none;
            color: var(--dark);
            background: #f8f9fa;
            border-radius: 0;
            margin-right: 5px;
        }

        .nav-tabs .nav-link.active {
            background: var(--primary);
            color: white;
            border: none;
        }

        .service-detail {
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .hero-heading {
                font-size: 2.8rem;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .page-header {
                padding: 100px 0 60px;
            }
        }

        @media (max-width: 768px) {
            .hero-heading {
                font-size: 2.2rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .page-header h1 {
                font-size: 2.2rem;
            }

            .contact-form {
                padding: 25px;
            }
        }

        /* Floating Social Media Bar */
        .floating-social-bar {
            position: fixed;
            right: 0;
            top: 50%;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: all 0.3s ease;
            opacity: 0;
        }

        .social-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin: 5px;
            border-radius: 50% 0 0 50%;
            transform: translateX(20px);
            transition: all 0.3s ease;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .social-icon:hover {
            transform: translateX(0);
            box-shadow: -2px 0 15px rgba(0, 0, 0, 0.2);
        }

        .social-icon.facebook {
            background: #3b5998;
        }

        .social-icon.whatsapp {
            background: #05e824;
        }

        .social-icon.phone {
            background: #094de0;
        }

        .social-icon.twitter {
            background: #1da1f2;
        }

        .social-icon.linkedin {
            background: #0077b5;
        }

        .social-icon.instagram {
            background: #e1306c;
        }

        /* Animation for initial load */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .floating-social-bar {
            animation: slideIn 0.5s ease forwards;
            animation-delay: 1s;
        }

        /* Make it responsive */
        @media (max-width: 768px) {
            .floating-social-bar {
                flex-direction: row;
                top: auto;
                bottom: 0;
                left: 0;
                right: 0;
                transform: none;
                justify-content: center;
                background: rgba(255, 255, 255, 0.9);
                padding: 10px;
            }

            .social-icon {
                border-radius: 50%;
                transform: translateY(20px);
                margin: 0 5px;
            }

            .social-icon:hover {
                transform: translateY(0);
            }

            @keyframes slideIn {
                from {
                    transform: translateY(100%);
                }

                to {
                    transform: translateY(0);
                }
            }
        }

        .logo-img {
            height: 40px;
            /* Match your navbar height */
            width: auto;
            transition: all 0.3s ease;
        }

        /* For retina displays */
        @media (-webkit-min-device-pixel-ratio: 2),
        (min-resolution: 192dpi) {
            .logo-img {
                height: 50px;
                /* Slightly larger for HD screens */
            }
        }

        /* Modal Header */
        .modal-header.bg-primary {
            background-color: #0d6efd !important;
            color: #fff;
        }

        /* Tracking Progress Container */
        .tracking-progress {
            border-left: 4px solid #0d6efd;
            padding-left: 20px;
            margin-top: 20px;
        }

        /* Each Step Block */
        .step {
            position: relative;
            margin-bottom: 30px;
        }

        /* Step Dot Icon (default) */
        .step::before {
            content: "\f111";
            /* solid dot */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: -32px;
            top: 0;
            font-size: 14px;
            color: #0d6efd;
            background-color: #fff;
            border-radius: 50%;
        }

        /* Completed Step with Check Icon */
        .step.active::before {
            content: "\f00c";
            /* check icon */
        }

        /* Stopped/Failed Step */
        .step.stopped::before {
            content: "\f071";
            /* warning icon */
            color: red;
        }

        /* Step Label Styling */
        .step .step-label {
            font-weight: bold;
            color: #0d6efd;
        }

        /* Step Date (optional) */
        .step .step-date {
            font-size: 12px;
            color: #666;
        }

        /* Shipment & Recipient Sections */
        .shipment-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .shipment-details h6 {
            font-weight: 600;
            margin-bottom: 10px;
            color: #0d6efd;
        }

        .shipment-details ul {
            padding-left: 15px;
        }

        .shipment-details li {
            font-size: 14px;
            margin-bottom: 6px;
        }

        /* Button styling inside modal */
        .modal-footer .btn {
            min-width: 140px;
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="d-flex flex-wrap justify-content-end">
                        <div class="me-4"><i class="fas fa-phone me-2"></i> +91 95851 56857</div>
                        <div><i class="fas fa-envelope me-2"></i> svlogistics.sales@gmail.com</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#home">
                <img src="./Asset/Logo.png" alt="SV Logistics" class="logo-img" style="height: 80px;">
                <!-- Adjust height as needed -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div class="d-flex ms-lg-3 mt-3 mt-lg-0">

                    <a href="#contact" class="btn btn-secondary">Get Quote</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="page-content">
        <!-- Home Page -->
        <div id="home">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <h1 class="hero-heading" style="color:rgb(24, 15, 91);">Logistics Solutions for Coimbatore
                                Region</h1>
                            <p class="hero-subheading">Fast, reliable, and efficient delivery services across
                                Coimbatore, Pollachi, Tiruppur, Ooty, Kothagiri and surrounding areas.</p>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="#services" class="btn btn-light btn-lg">Our Services</a>
                                <a href="#contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-5 mt-lg-0">
                            <div class="bg-white p-4 rounded">
                                <h3 class="mb-4">Track Your Shipment</h3>
                                <p class="mb-3">Enter your tracking number to get real-time updates</p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="trackingInput"
                                        placeholder="Enter Tracking ID">
                                    <button class="btn btn-primary" onclick="redirectToTrack()">Track</button>

                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <div>Order Received</div>
                                    <div>In Transit</div>
                                    <div>Delivered</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="floating-social-bar">
                <!-- Facebook -->
                <!-- <a href="https://facebook.com" class="social-icon facebook" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a> -->

                <!-- Instagram -->
                <!-- <a href="https://instagram.com" class="social-icon instagram" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a> -->

                <!-- WhatsApp (with message and phone number) -->
                <a href="https://wa.me/9585156817?text=Hello%2C%20I%20am%20interested%20in%20your%20services"
                    class="social-icon whatsapp" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                </a>

                <!-- Phone call -->
                <a href="tel:+919585156857" class="social-icon phone">
                    <i class="fas fa-phone"></i>
                </a>
            </div>
            <!-- Services Preview -->
            <section class="py-5">
                <div class="container py-5">
                    <div class="section-title">
                        <h2>Our Services</h2>
                        <p class="mt-3">We provide comprehensive logistics solutions across Tamil Nadu</p>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div class="service-card text-center p-4">
                                <div class="service-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <h3>Parcel Services</h3>
                                <p>Secure delivery of packages of all sizes with real-time tracking.</p>
                                <a href="#services" class="btn btn-link">Learn More <i
                                        class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="service-card text-center p-4">
                                <div class="service-icon">
                                    <i class="fas fa-truck-loading"></i>
                                </div>
                                <h3>Full Truck Load</h3>
                                <p>Dedicated trucks for your cargo with temperature control options.</p>
                                <a href="#services" class="btn btn-link">Learn More <i
                                        class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="service-card text-center p-4">
                                <div class="service-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <h3>Point to Point</h3>
                                <p>Direct delivery between any two locations with no intermediate stops.</p>
                                <a href="#services" class="btn btn-link">Learn More <i
                                        class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="service-card text-center p-4">
                                <div class="service-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h3>Local Services</h3>
                                <p>Fast intra-city deliveries with same-day options available.</p>
                                <a href="#services" class="btn btn-link">Learn More <i
                                        class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About Preview -->
            <section class="py-5 bg-light">
                <div class="container py-5">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-5 mb-lg-0">
                            <img src="./Asset/About.jpg" alt="About Us" class="img-fluid about-img">
                        </div>
                        <div class="col-lg-6">
                            <h2 class="mb-4">About SV Logistics</h2>
                            <p class="lead">Your trusted logistics partner in the Coimbatore region since 2010.</p>
                            <p>Founded with a mission to transform logistics in Tamil Nadu, SV Logistics has grown to
                                become a leading provider of transportation and delivery services. With a fleet of over
                                50 vehicles and a dedicated team of professionals, we serve businesses and individuals
                                across the region.</p>

                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5>Local Expertise</h5>
                                            <p>Deep knowledge of Coimbatore region routes and logistics</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex">
                                        <div class="me-3 text-primary">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5>Technology Driven</h5>
                                            <p>Advanced tracking and logistics management systems</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="#about" class="btn btn-primary mt-2">Learn More About Us</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Preview -->
            <section class="py-5">
                <div class="container py-5">
                    <div class="section-title">
                        <h2>Contact Us</h2>
                        <p class="mt-3">Get in touch for your logistics needs</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-5 mb-lg-0">
                            <div class="contact-info-box">
                                <h3 class="mb-4">Our Office</h3>
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-map-marker-alt me-3"></i> 3/206,Kulathur
                                        Road,Venkittapuram,Coimbatore -641062</li>
                                    <li class="mb-3"><i class="fas fa-phone me-3"></i> +91 95851 56857</li>
                                    <li class="mb-3"><i class="fas fa-envelope me-3"></i>svlogistics.sales@gmail.com
                                    </li>
                                    <li><i class="fas fa-clock me-3"></i> Mon-Sat: 8AM - 8PM</li>
                                </ul>
                            </div>
                            <div class="map-container">
                                <div style="position: relative; width: 100%; height: 0; padding-bottom: 56.25%;">
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m12!1m8!1m3!1d2125.0710682543763!2d76.9317908!3d11.0242438!3m2!1i1024!2i768!4f13.1!2m1!1s3%2F206%2CKulathurRoad%2CVenkittapuram%2CCoimbatore%20-641062!5e1!3m2!1sen!2sin!4v1752671402977!5m2!1sen!2sin"
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>

                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="contact-form">
                                <h3 class="mb-4">Send us a message</h3>
                                <p class="mb-4">Have questions about our services? Reach out to us!</p>
                                <form>
                                    <div class="mb-3">
                                        <input type="text" class="form-control form-control-lg" placeholder="Your Name"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="email" class="form-control form-control-lg"
                                            placeholder="Your Email" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control form-control-lg" placeholder="Subject"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control form-control-lg" rows="4"
                                            placeholder="Your Message" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 btn-lg">Send Message</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- About Page -->
        <div id="about" class="d-none">
            <section class="page-header">
                <div class="container">
                    <h1>About SV Logistics</h1>
                    <nav aria-label="breadcrumb" class="mt-4">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="#home">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">About Us</li>
                        </ol>
                    </nav>
                </div>
            </section>

            <section class="page-section">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-5 mb-lg-0">
                            <img src="./Asset/OurStory.jpg" alt="Our Story" class="img-fluid rounded shadow">
                        </div>
                        <div class="col-lg-6">
                            <h2 class="mb-4">Our Story</h2>
                            <p>Founded in 2010 by logistics professionals with decades of experience, SV Logistics began
                                with a simple mission: to provide reliable, efficient logistics services to businesses
                                in the Coimbatore region.</p>
                            <p>What started as a small operation with two trucks has grown into one of the region's most
                                trusted logistics providers. Today, we operate a fleet of over 50 vehicles and serve
                                hundreds of satisfied customers across Tamil Nadu.</p>
                            <p>Our growth has been fueled by our commitment to customer satisfaction, technological
                                innovation, and deep understanding of local logistics challenges.</p>
                        </div>
                    </div>

                    <div class="row mt-5 pt-5">
                        <div class="col-lg-6 order-lg-2 mb-5 mb-lg-0">
                            <img src="./Asset/OurValues.jpg" alt="Our Values" class="img-fluid rounded shadow">
                        </div>
                        <div class="col-lg-6 order-lg-1">
                            <h2 class="mb-4">Our Values</h2>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle p-3"
                                        style="width: 70px; height: 70px;">
                                        <i class="fas fa-bolt fa-2x"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-4">
                                    <h4>Speed & Efficiency</h4>
                                    <p>We optimize every route and process to ensure your goods reach their destination
                                        as quickly as possible.</p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle p-3"
                                        style="width: 70px; height: 70px;">
                                        <i class="fas fa-shield-alt fa-2x"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-4">
                                    <h4>Reliability</h4>
                                    <p>With contingency plans and well-maintained vehicles, we ensure your shipments
                                        arrive on time, every time.</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle p-3"
                                        style="width: 70px; height: 70px;">
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-4">
                                    <h4>Customer Focus</h4>
                                    <p>We listen to your needs and tailor our services to provide the best logistics
                                        solutions for your business.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Services Page -->
        <div id="services" class="d-none">
            <section class="page-header">
                <div class="container">
                    <h1>Our Services</h1>
                    <nav aria-label="breadcrumb" class="mt-4">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="#home">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Services</li>
                        </ol>
                    </nav>
                </div>
            </section>

            <section class="page-section">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="service-locations">
                                <h3 class="mb-4">Service Areas</h3>
                                <p>We provide logistics services across Coimbatore and surrounding districts:</p>
                                <ul class="location-list list-unstyled">
                                    <li>Coimbatore</li>
                                    <li>Pollachi</li>
                                    <li>Tiruppur</li>
                                    <li>Ooty (Udagamandalam)</li>
                                    <li>Kothagiri</li>
                                    <li>Mettupalayam</li>
                                    <li>Erode</li>
                                    <li>Salem</li>
                                    <li>Palakkad</li>
                                    <li>All major cities in Tamil Nadu</li>
                                </ul>
                            </div>
                            <div class="service-locations">
                                <h3 class="mb-4">Why Choose Us?</h3>
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Local
                                        expertise in Coimbatore region</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Real-time
                                        shipment tracking</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Competitive
                                        pricing</li>
                                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Dedicated
                                        customer support</li>
                                    <li><i class="fas fa-check-circle text-success me-2"></i> On-time delivery guarantee
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <ul class="nav nav-tabs" id="serviceTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="parcel-tab" data-bs-toggle="tab"
                                        data-bs-target="#parcel" type="button" role="tab">Parcel Services</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="local-tab" data-bs-toggle="tab" data-bs-target="#local"
                                        type="button" role="tab">Local Services</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="ftl-tab" data-bs-toggle="tab" data-bs-target="#ftl"
                                        type="button" role="tab">Full Truck Load</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="point-tab" data-bs-toggle="tab" data-bs-target="#point"
                                        type="button" role="tab">Point to Point</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="part-tab" data-bs-toggle="tab" data-bs-target="#part"
                                        type="button" role="tab">Part Delivery</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="serviceTabsContent">
                                <div class="tab-pane fade show active" id="parcel" role="tabpanel">
                                    <div class="service-detail">
                                        <h3 class="mb-4">Parcel Services</h3>
                                        <p>Our parcel delivery service is designed for businesses and individuals who
                                            need to send packages of various sizes across Coimbatore and surrounding
                                            regions.</p>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h5>Key Features:</h5>
                                                <ul class="mb-4">
                                                    <li>Same-day delivery in Coimbatore</li>
                                                    <li>Next-day delivery to nearby cities</li>
                                                    <li>Package weight up to 50kg</li>
                                                    <li>Real-time tracking</li>
                                                    <li>Signature on delivery</li>
                                                    <li>Insurance options available</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Coverage Areas:</h5>
                                                <ul>
                                                    <li>Coimbatore City</li>
                                                    <li>Pollachi & surrounding</li>
                                                    <li>Tiruppur District</li>
                                                    <li>Ooty & Kothagiri</li>
                                                    <li>Erode & Salem</li>
                                                    <li>All major cities in Tamil Nadu</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <a href="#contact" class="btn btn-primary mt-3">Request a Quote</a>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="local" role="tabpanel">
                                    <div class="service-detail">
                                        <h3 class="mb-4">Local Delivery Services</h3>
                                        <p>Our local delivery services ensure fast and efficient transportation within
                                            Coimbatore and nearby towns.</p>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h5>Key Features:</h5>
                                                <ul class="mb-4">
                                                    <li>Same-day delivery guarantee</li>
                                                    <li>Dedicated delivery vehicles</li>
                                                    <li>Regular route services</li>
                                                    <li>Flexible pickup times</li>
                                                    <li>Document and parcel delivery</li>
                                                    <li>Affordable pricing</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Coverage Areas:</h5>
                                                <ul>
                                                    <li>Coimbatore City (All zones)</li>
                                                    <li>Saravanampatti</li>
                                                    <li>Peelamedu</li>
                                                    <li>Gandhipuram</li>
                                                    <li>Saibaba Colony</li>
                                                    <li>R.S. Puram</li>
                                                    <li>Ukkadam</li>
                                                    <li>Singanallur</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <a href="#contact" class="btn btn-primary mt-3">Request a Quote</a>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="ftl" role="tabpanel">
                                    <div class="service-detail">
                                        <h3 class="mb-4">Full Truck Load (FTL)</h3>
                                        <p>For businesses that need to transport large quantities of goods, our Full
                                            Truck Load service offers dedicated transportation solutions.</p>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h5>Key Features:</h5>
                                                <ul class="mb-4">
                                                    <li>Dedicated trucks for your cargo</li>
                                                    <li>Various truck sizes available</li>
                                                    <li>Temperature-controlled options</li>
                                                    <li>Secure loading/unloading</li>
                                                    <li>Experienced drivers</li>
                                                    <li>Route optimization</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Truck Types:</h5>
                                                <ul>
                                                    <li>Mini Trucks (500kg-1 ton)</li>
                                                    <li>Tata Ace (1-1.5 ton)</li>
                                                    <li>Pickup Trucks (2-3 ton)</li>
                                                    <li>6-Wheeler Trucks (5-7 ton)</li>
                                                    <li>10-Wheeler Trucks (9-16 ton)</li>
                                                    <li>Trailers (20+ ton)</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <a href="#contact" class="btn btn-primary mt-3">Request a Quote</a>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="point" role="tabpanel">
                                    <div class="service-detail">
                                        <h3 class="mb-4">Point to Point Delivery</h3>
                                        <p>Direct delivery service between any two locations with no intermediate stops
                                            or handling.</p>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h5>Key Features:</h5>
                                                <ul class="mb-4">
                                                    <li>Direct route between origin and destination</li>
                                                    <li>Faster transit times</li>
                                                    <li>Reduced handling of goods</li>
                                                    <li>Enhanced security</li>
                                                    <li>Real-time tracking</li>
                                                    <li>Customized solutions</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Ideal For:</h5>
                                                <ul>
                                                    <li>High-value shipments</li>
                                                    <li>Time-sensitive deliveries</li>
                                                    <li>Fragile items</li>
                                                    <li>Confidential documents</li>
                                                    <li>Temperature-sensitive goods</li>
                                                    <li>Large volume shipments</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <a href="#contact" class="btn btn-primary mt-3">Request a Quote</a>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="part" role="tabpanel">
                                    <div class="service-detail">
                                        <h3 class="mb-4">Part Load Delivery</h3>
                                        <p>Cost-effective solution for shipments that don't require a full truck. We
                                            consolidate shipments to optimize costs.</p>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h5>Key Features:</h5>
                                                <ul class="mb-4">
                                                    <li>Pay only for space you use</li>
                                                    <li>Daily services to major locations</li>
                                                    <li>Secure handling</li>
                                                    <li>Consolidation centers</li>
                                                    <li>Tracking available</li>
                                                    <li>Economical pricing</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Benefits:</h5>
                                                <ul>
                                                    <li>Reduced shipping costs</li>
                                                    <li>Regular schedules</li>
                                                    <li>Environmentally friendly</li>
                                                    <li>Flexible volume options</li>
                                                    <li>Ideal for SMEs</li>
                                                    <li>No minimum shipment size</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <a href="#contact" class="btn btn-primary mt-3">Request a Quote</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Contact Page -->
        <div id="contact" class="d-none">
            <section class="page-header">
                <div class="container">
                    <h1>Contact Us</h1>
                    <nav aria-label="breadcrumb" class="mt-4">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="#home">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Contact</li>
                        </ol>
                    </nav>
                </div>
            </section>

            <section class="page-section">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-5 mb-5 mb-lg-0">
                            <div class="contact-info-box">
                                <h3 class="mb-4">Get In Touch</h3>
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-map-marker-alt me-3"></i> 3/206,Kulathur
                                        Road,Venkittapuram,Coimbatore -641062</li>
                                    <li class="mb-3"><i class="fas fa-phone me-3"></i>+91 95851 56857</li>
                                    <li class="mb-3"><i class="fas fa-envelope me-3"></i>svlogistics.sales@gmail.com
                                    </li>
                                    <li><i class="fas fa-clock me-3"></i>Mon-Sat: 8AM - 8PM</li>
                                </ul>
                            </div>

                            <div class="map-container mt-4">

                                <div style="position: relative; width: 100%; height: 0; padding-bottom: 56.25%;">

                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m12!1m8!1m3!1d2125.0710682543763!2d76.9317908!3d11.0242438!3m2!1i1024!2i768!4f13.1!2m1!1s3%2F206%2CKulathurRoad%2CVenkittapuram%2CCoimbatore%20-641062!5e1!3m2!1sen!2sin!4v1752671402977!5m2!1sen!2sin"
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>


                                </div>

                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="contact-form">
                                <h3 class="mb-4">Send us a message</h3>
                                <p class="mb-4">Have questions about our services? Need a quote? Reach out to us!</p>
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <input type="text" class="form-control form-control-lg" name="name"
                                                placeholder="Your Name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <input type="email" class="form-control form-control-lg" name="email"
                                                placeholder="Your Email" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control form-control-lg" name="message" rows="5"
                                            placeholder="Your Message" required></textarea>
                                    </div>
                                    <button type="submit" name="send_mail" class="btn btn-primary w-100 btn-lg">
                                        Send Message <i class="fas fa-paper-plane ms-2"></i>
                                    </button>
                                </form>

                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="mt-5">
                            <h4 class="mb-4">Frequently Asked Questions</h4>
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq1">
                                            What areas do you serve?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse show"
                                        data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            We serve all areas in Coimbatore, Pollachi, Tiruppur, Ooty, Kothagiri,
                                            and surrounding regions. We also provide services to all major cities in
                                            Tamil Nadu.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faq2">
                                            How can I track my shipment?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            You can track your shipment using the tracking ID provided to you. Enter
                                            it in the tracking box on our homepage or contact our customer support
                                            for assistance.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#faq3">
                                            What are your business hours?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Our offices are open Monday to Saturday from 8:00 AM to 8:00 PM. Pickup
                                            and delivery services operate 7 days a week.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Track Your Shipment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- TRACKING STATUS -->
                        <div class="tracking-progress mb-4">
                            <div class="progress-steps vertical-steps">
                                <div id="step1" class="step">
                                    <div class="step-icon"><i class="fas fa-circle-check text-success"></i></div>
                                    <div class="step-label">Parcel Received</div>
                                </div>
                                <div id="step2" class="step">
                                    <div class="step-icon"><i class="fas fa-circle-check text-success"></i></div>
                                    <div class="step-label">Parcel Confirmed</div>
                                </div>
                                <div id="step3" class="step">
                                    <div class="step-icon"><i class="fas fa-circle-check text-success"></i></div>
                                    <div class="step-label" id="step3Label">Ready to Dispatch</div>
                                </div>
                                <div id="step4" class="step">
                                    <div class="step-icon"><i class="fas fa-circle-check text-success"></i></div>
                                    <div class="step-label" id="step4Label">Dispatched</div>
                                </div>
                                <div id="step5" class="step">
                                    <div class="step-icon"><i class="fas fa-circle-check text-success"></i></div>
                                    <div class="step-label" id="step5Label">Received at Destination</div>
                                </div>
                                <div id="step6" class="step">
                                    <div class="step-icon"><i class="fas fa-circle-check text-success"></i></div>
                                    <div class="step-label">Delivered</div>
                                </div>
                            </div>
                        </div>

                        <!-- SHIPMENT & BILL INFO -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Shipment Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Tracking ID:</strong> <span id="trackBillId">-</span></li>
                                    <li><strong>Origin:</strong> <span id="trackOrigin">-</span></li>
                                    <li><strong>Destination:</strong> <span id="trackDest">-</span></li>
                                    <li><strong>Weight:</strong> <span id="trackWeight">-</span></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Recipient Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Name:</strong> <span id="trackName">-</span></li>
                                    <li><strong>Contact:</strong> <span id="trackPhone">-</span></li>
                                    <li><strong>Address:</strong> <span id="trackAddress">-</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="downloadBillBtn">
                            <i class="fas fa-download me-2"></i>Download Bill
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <h4>SV Logistics</h4>
                        <p class="mt-4">Providing innovative logistics solutions across Coimbatore region with a focus
                            on
                            reliability, efficiency, and customer satisfaction since 2010.</p>
                        <div class="social-links mt-4">
                            <!-- Facebook -->
                            <!-- <a href="https://facebook.com" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a> -->

                            <!-- Instagram -->
                            <!-- <a href="https://instagram.com" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a> -->

                            <!-- WhatsApp (with message and phone number) -->
                            <a href="https://wa.me/9585156817?text=Hello%2C%20I%20am%20interested%20in%20your%20services"
                                target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>

                            <!-- Phone call -->
                            <a href="tel:+919585156857">
                                <i class="fas fa-phone"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-5 mb-md-0">
                        <h4>Quick Links</h4>
                        <ul class="footer-links list-unstyled mt-4">
                            <li><a href="#home">Home</a></li>
                            <li><a href="#about">About Us</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-5 mb-md-0">
                        <h4>Our Services</h4>
                        <ul class="footer-links list-unstyled mt-4">
                            <li><a href="#services">Parcel Services</a></li>
                            <li><a href="#services">Local Services</a></li>
                            <li><a href="#services">Full Truck Load</a></li>
                            <li><a href="#services">Point to Point</a></li>
                            <li><a href="#services">Part Delivery</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <h4>Service Areas</h4>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-2">Coimbatore</li>
                            <li class="mb-2">Pollachi</li>
                            <li class="mb-2">Tiruppur</li>
                            <li class="mb-2">Ooty (Udagamandalam)</li>
                            <li class="mb-2">Kothagiri</li>
                            <li>All surrounding areas</li>
                        </ul>
                    </div>
                </div>
                <div class="copyright">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-md-12 text-center text-md-center mb-3 mb-md-0">
                                <p class="mb-0">&copy; <span id="currentYear"></span> SVLogistics. All Rights Reserved.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function redirectToTrack() {
                const trackId = document.getElementById('trackingInput').value.trim();
                if (!trackId) {
                    alert('Please enter a Tracking ID');
                    return;
                }
                window.location.href = `track.php?trackid=${encodeURIComponent(trackId)}`;
            }
            document.getElementById("currentYear").textContent = new Date().getFullYear();
            // Simple page navigation
            document.addEventListener('DOMContentLoaded', function () {
                // Show home page by default
                document.getElementById('home').classList.remove('d-none');

                // Navigation click handler
                document.querySelectorAll('nav a.nav-link').forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();

                        // Hide all pages
                        document.querySelectorAll('.page-content > div').forEach(page => {
                            page.classList.add('d-none');
                        });

                        // Show the selected page
                        const targetPage = this.getAttribute('href').substring(1);
                        document.getElementById(targetPage).classList.remove('d-none');

                        // Update active navigation
                        document.querySelectorAll('nav a.nav-link').forEach(navLink => {
                            navLink.classList.remove('active');
                        });
                        this.classList.add('active');

                        // Scroll to top
                        window.scrollTo(0, 0);
                    });
                });

                // Home page navigation
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function (e) {
                        const targetId = this.getAttribute('href').substring(1);
                        const targetElement = document.getElementById(targetId);

                        if (targetElement) {
                            // Hide all pages
                            document.querySelectorAll('.page-content > div').forEach(page => {
                                page.classList.add('d-none');
                            });

                            // Show the target page
                            targetElement.classList.remove('d-none');

                            // Update active navigation
                            document.querySelectorAll('nav a.nav-link').forEach(navLink => {
                                navLink.classList.remove('active');
                                if (navLink.getAttribute('href').substring(1) === targetId) {
                                    navLink.classList.add('active');
                                }
                            });

                            // Scroll to top
                            window.scrollTo(0, 0);
                        }
                    });
                });
            });
        </script>
</body>

</html>