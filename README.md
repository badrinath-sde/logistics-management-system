Logistics Management System

Core PHP | MySQL | PDF Generation | Production Deployment


🔗 Live Demo: [View Website](https://svlogisticscovai.com)

Overview

    A web-based logistics management system built using Core PHP and MySQL to manage shipment tracking, automated invoice generation, and operational workflows.

    The system handles end-to-end logistics processes including shipment creation, billing, status updates, and proof-of-delivery management.


Key Features

    Shipment creation and management  
    Automatic invoice number generation  
    PDF invoice generation using FPDF, DOMPDF, and FPDI  
    Manual billing configuration based on business requirements  
    FTL (Full Truck Load) priority billing option  
    Shipment status tracking workflow  
    Proof-of-delivery image upload  
    Monthly bill export handling  
    Production deployment on shared hosting (Hostinger)


System Architecture

Built using traditional Core PHP structure with file-based routing.

Integrated third-party libraries:

FPDF  
DOMPDF  
FPDI  
PHPMailer  

Database: MySQL with relational structure connecting shipments and invoices using foreign key relationships.


Security Implementation

    Password hashing for authentication  
    Input validation and secure query handling  
    SMTP-based email notification system (credentials excluded from repository)  
    Production-level debugging and feature enhancement after deployment  


Project Structure (Simplified)

    Asset/  
    BILLIMAGE/ (ignored - generated files)  
    Bills/ (ignored - generated invoices)  
    Monthly_bills/ (ignored - exports)  
    dompdf/  
    fpdf/  
    fpdi/  
    PHPMailer/  
    Multiple PHP modules for admin and employee operations  


Setup Instructions

Setup Instructions

1. Clone the repository
2. Open the PHP files and update the database connection details inside the pages where the connection is defined
3. Update SMTP email credentials inside the mail configuration section
4. Import the required MySQL database schema
5. Run the project using a local server such as XAMPP or WAMP, or deploy to hosting


Deployment

Deployed independently using shared hosting with database configuration, cPanel setup, file permission handling, live production debugging, and real-time feature enhancement based on client requirements.


Learning Outcome

This project strengthened practical skills in backend workflow design, invoice generation systems, relational database structuring, production deployment management, and handling real-time feature updates in live systems.
