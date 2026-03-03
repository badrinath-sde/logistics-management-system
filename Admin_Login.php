<?php
session_start();

$host = 'localhost';
$dbname = 'logistics_db';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $admin_id = trim($_POST['Adminid']);
    $password = trim($_POST['User_Password']);

    if (!empty($admin_id) && !empty($password)) {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_STRING);

        $stmt = $conn->prepare("SELECT admin_id, password, location FROM admin WHERE admin_id = ?");
        $stmt->bind_param("s", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if ($password === $row['password']) {

                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['location'] = $row['location'];

                header("Location: Admin_Dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin ID not found.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="icon" type="image/png" href="Asset/Logo.png">
    <title>SV Logistics</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap");

        body {
            font-family: "Poppins", sans-serif;
            background: #ececec;
        }

        .box-area {
            width: 930px;
        }

        .right-box {
            padding: 40px 30px 40px 40px;
        }

        ::placeholder {
            font-size: 16px;
        }

        .rounded-4 {
            border-radius: 20px;
        }

        .rounded-5 {
            border-radius: 30px;
        }

        @media only screen and (max-width: 768px) {
            .box-area {
                margin: 0 10px;
            }

            .left-box {
                height: auto !important;
                overflow: hidden;
                padding: 20px !important;
            }

            .right-box {
                padding: 20px;
                margin-top: 20px !important;
            }
        }

        .form-control:focus {
            box-shadow: none !important;
        }

        i {
            color: #898686;
        }
    </style>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row border rounded-5 p-3 bg-white shadow box-area">
            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box"
                style="background: #103cbe">

                <p class="text-white fs-2"
                    style="font-family: 'Courier New', Courier, monospace; font-weight: 600;font-size:30px;">
                    Logistics Admin Panel</p>
                <small class="text-white text-wrap text-center"
                    style="width: 17rem; font-family: 'Courier New', Courier, monospace;font-size:20px;">Manage routes,
                    waybills &
                    operations seamlessly.</small>
            </div>

            <div class="col-md-6 right-box">
                <?php if (isset($error)) {
                    echo "<div class='alert alert-danger'>$error</div>";
                } ?>
                <form method="post">
                    <div class="row align-items-center">
                        <div class="header-text mb-4">
                            <h2>Hello, Again</h2>
                            <p>Your logistics dashboard starts here.</p>
                        </div>

                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-lg bg-light fs-6" placeholder="Admin ID"
                                name="Adminid" id="email" required />
                        </div>
                        <div class="input-group mb-1">
                            <input type="password" class="form-control form-control-lg bg-light fs-6"
                                name="User_Password" placeholder="Password" id="password" required />
                        </div>
                        <div class="input-group mb-3">
                            <input class="btn btn-lg btn-primary w-100 fs-6" type="submit" name="login" value="Login" />
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>