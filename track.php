<?php
$shipment = null;
$steps = [];

if (isset($_GET['trackid']) && !empty($_GET['trackid'])) {
    $billid = $_GET['trackid'];

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "logistics_db");
    if ($conn->connect_error) {
        die("Connection failed");
    }

    $stmt = $conn->prepare("SELECT * FROM invoices WHERE billid = ?");
    $stmt->bind_param("s", $billid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();

        $district_consignor = $shipment['consignor_district'];
        $district_consignee = $shipment['consignee_district'];
        $status = $shipment['transit_status'];

        $confirmedTime = !empty($shipment['confirmedtime']) ? date("d-m-Y h:i A", strtotime($shipment['confirmedtime'])) : '';
        $deliveredTime = !empty($shipment['deliveredtime']) ? date("d-m-Y h:i A", strtotime($shipment['deliveredtime'])) : '';
        $allSteps = [
            'Mailed' => 'Parcel Confirmed' . ($confirmedTime ? " <small class=\"text-muted\">($confirmedTime)</small>" : ''),
            'New' => "Ready to Dispatch from $district_consignor",
            'Moved' => "Dispatched from $district_consignor<br><i class='fa fa-map-marker'></i> $district_consignor",
            'Received' => "Received at $district_consignee. Delivery Soon<br><i class='fa fa-map-marker'></i> $district_consignee",
            'Delivered' => 'Delivered' . ($deliveredTime ? " <small class=\"text-muted\">($deliveredTime)</small>" : '')
        ];

        $statuses = array_keys($allSteps);

        if ($status === 'Not_initiated') {
            $steps[] = [
                'label' => "No shipment found for the given tracking ID.",
                'status' => 'stopped'
            ];
        } elseif (!in_array($status, $statuses)) {
            $steps[] = [
                'label' => "Shipment stopped due to transit issue: $status",
                'status' => 'stopped'
            ];
        } else {
            $stopFlag = false;
            foreach ($statuses as $stepStatus) {
                $steps[] = [
                    'label' => $allSteps[$stepStatus],
                    'status' => ($stepStatus === $status) ? 'current' : (
                        $stopFlag ? 'pending' : 'done'
                    )
                ];
                if ($stepStatus === $status) {
                    $stopFlag = true;
                }
            }
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Shipment Tracker | Logistics Company</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            --danger: #e05e00;
            /* Changed to match your secondary button */
            --success: #0b8b19;
            /* Changed to match your secondary color */
            --warning: #f59e0b;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --primary-light: #3a516f;
            /* Darker shade of primary */
        }

        body {
            font-family: 'Roboto', sans-serif;
            color: #444;
            background-color: #f9fbfd;
            margin: 0;
            padding: 0;
        }

        /* Navigation Styles */
        .navbar {
            padding: 15px 0;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand img {
            height: 80px;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--dark);
            padding: 8px 15px;
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

        /* Button Styles */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 4px;
        }

        .btn-secondary {
            background-color: var(--danger);
            /* Using your danger color */
            border-color: var(--danger);
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 4px;
            color: white;
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 4px;
            color: white;
        }

        /* Tracker Styles */
        .tracker-container {
            max-width: 1000px;
            margin: 1rem auto 3rem;
            padding: 0 1.5rem;
        }

        .tracker-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }

        .tracker-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(135deg, #f0f7ff 0%, #e1effe 100%);
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.in-transit {
            background: #dbeafe;
            color: var(--primary);
        }

        .status-badge.delivered {
            background: #dcfce7;
            color: var(--success);
        }

        .status-badge.problem {
            background: #fee2e2;
            color: var(--danger);
        }

        /* Progress Tracking Styles */
        .tracking-progress {
            position: relative;
            padding-left: 1.5rem;
        }

        .progress-line {
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--gray-200);
            z-index: 1;
        }

        .progress-line-fill {
            position: absolute;
            left: 0.5rem;
            top: 0;
            width: 2px;
            background: var(--primary);
            z-index: 2;
            height: 0;
            transition: height 1.2s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .step {
            position: relative;
            margin-bottom: 1.75rem;
            padding-bottom: 1.75rem;
            z-index: 3;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .step::before {
            content: "";
            position: absolute;
            left: -1.65rem;
            top: 0;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--gray-200);
            z-index: 4;
            transition: all 0.4s ease;
        }

        .step.animate {
            opacity: 1;
            transform: translateY(0);
        }

        .step.done::before {
            background: var(--primary);
            border-color: var(--primary);
            transform: scale(1.1);
            box-shadow: 0 0 0 4px rgba(44, 62, 80, 0.2);
        }

        .step.done::after {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: -1.5rem;
            top: 0.15rem;
            font-size: 0.6rem;
            color: white;
            z-index: 5;
            animation: checkIn 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55) both;
        }

        .step.current::before {
            background: white;
            border: 4px solid var(--primary);
            transform: scale(1.2);
            animation: pulse 1.5s infinite;
        }

        .step.stopped::before {
            background: white;
            border: 4px solid var(--danger);
            animation: pulseDanger 1.5s infinite;
        }

        /* Footer Styles */
        .footer {
            background: var(--dark);
            color: rgba(255, 255, 255, 0.7);
            padding: 80px 0 0;
        }

        /* Animations */
        @keyframes checkIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            80% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(44, 62, 80, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(44, 62, 80, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(44, 62, 80, 0);
            }
        }

        @keyframes pulseDanger {
            0% {
                box-shadow: 0 0 0 0 rgba(224, 94, 0, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(224, 94, 0, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(224, 94, 0, 0);
            }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .navbar-brand img {
                height: 60px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
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

        .step-label small {
            font-size: 0.75rem;
            margin-left: 6px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php#home">
                <img src="./Asset/Logo.png" alt="SV Logistics" class="logo-img" style="height: 80px;">
                <!-- Adjust height as needed -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                </ul>
                <div class="d-flex ms-lg-3 mt-3 mt-lg-0">

                    <a href="#contact" class="btn btn-secondary">Get Quote</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="tracker-container">
        <?php if ($shipment): ?>
            <div class="tracker-box">
                <div class="tracker-header">
                    <div class="tracker-title">
                        <h2>Shipment Tracking</h2>
                        <div
                            class="status-badge <?= $status === 'Delivered' ? 'delivered' : ($status === 'Stopped' ? 'problem' : 'in-transit') ?>">
                            <?= $status === 'Delivered' ? 'Delivered' : ($status === 'Stopped' ? 'Issue Detected' : 'In Transit') ?>
                        </div>
                    </div>
                    <div class="tracker-id">Tracking ID: <?= htmlspecialchars($shipment['billid']) ?></div>
                </div>

                <div class="tracker-body">
                    <div class="info-grid">
                        <div class="info-block">
                            <h3>Shipment Details</h3>
                            <div class="info-item">
                                <span class="info-label">Weight</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['charged_weight']) ?> kg</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Parcel Type</span>
                                <span class="info-value">Standard</span>
                            </div>
                        </div>

                        <div class="info-block">
                            <h3>Origin</h3>
                            <div class="info-item">
                                <span class="info-label">Location</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['consignor_district']) ?></span>
                            </div>
                        </div>

                        <div class="info-block">
                            <h3>Destination</h3>
                            <div class="info-item">
                                <span class="info-label">Location</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['consignee_district']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Recipient</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['consignee_name']) ?></span>
                            </div>
                        </div>
                    </div>

                    <h3>Tracking History</h3>
                    <div class="tracking-progress">
                        <div class="progress-line"></div>
                        <div class="progress-line-fill" id="progressFill"></div>

                        <?php foreach ($steps as $s): ?>
                            <div class="step <?= $s['status'] ?>">
                                <div class="step-label"><?= $s['label'] ?></div>
                                <?php if (strpos($s['label'], 'Dispatched') !== false): ?>
                                    <?php if (stripos($s['label'], $district_consignor) === false): ?>
                                        <div class="step-location">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($district_consignor) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif (strpos($s['label'], 'Received') !== false): ?>
                                    <?php if (stripos($s['label'], $district_consignee) === false): ?>
                                        <div class="step-location">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($district_consignee) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="action-buttons">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back </a>
                        <?php if ($status === 'Delivered' && !empty($shipment['proof_image'])): ?>
                            <a href="BILLIMAGE/<?= htmlspecialchars($shipment['proof_image']) ?>" class="btn btn-success"
                                download>
                                <i class="fas fa-download"></i> Download Proof Image
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="tracker-box">
                <div class="tracker-header">
                    <h3>Recipient Information</h3>
                </div>
                <div class="tracker-body">
                    <div class="info-grid">
                        <div class="info-block">
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['consignee_name']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['consignee_phone']) ?></span>
                            </div>
                        </div>
                        <div class="info-block">
                            <div class="info-item">
                                <span class="info-label">Delivery Address</span>
                                <span class="info-value"><?= htmlspecialchars($shipment['consignee_address']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="tracker-box not-found">
                <div class="not-found-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h2>Shipment Not Found</h2>
                <p>We couldn't find any shipment with the provided tracking ID. Please check the number and try again.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Tracking
                </a>
            </div>
        <?php endif; ?>
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
                        <a href="https://facebook.com" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>

                        <!-- Instagram -->
                        <a href="https://instagram.com" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>

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
                        <li><a href="index.php#home">Home</a></li>
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#services">Services</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-5 mb-md-0">
                    <h4>Our Services</h4>
                    <ul class="footer-links list-unstyled mt-4">
                        <li><a href="index.php#services">Parcel Services</a></li>
                        <li><a href="index.php#services">Local Services</a></li>
                        <li><a href="index.php#services">Full Truck Load</a></li>
                        <li><a href="index.php#services">Point to Point</a></li>
                        <li><a href="index.php#services">Part Delivery</a></li>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Animate progress line
            const progressFill = document.getElementById('progressFill');
            const steps = document.querySelectorAll('.step');
            const currentStep = document.querySelector('.step.current');

            if (progressFill && currentStep) {
                // Calculate height based on current step position
                const currentStepRect = currentStep.getBoundingClientRect();
                const progressContainerRect = progressFill.parentElement.getBoundingClientRect();
                const height = currentStepRect.top - progressContainerRect.top + (currentStepRect.height / 2);

                // Set the final height with animation
                setTimeout(() => {
                    progressFill.style.height = height + 'px';
                }, 300);

                // Animate steps in sequence
                steps.forEach((step, index) => {
                    setTimeout(() => {
                        step.classList.add('animate');
                    }, index * 200);
                });
            }
        });
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