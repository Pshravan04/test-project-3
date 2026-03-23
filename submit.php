<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $mobile = htmlspecialchars($_POST['mobile'] ?? '');
    $form_name = htmlspecialchars($_POST['form_name'] ?? 'General Enquiry');

    // Optional Logging
    file_put_contents("debug-log.txt", date('Y-m-d H:i:s') . " - Name: $name | Email: $email | Phone: $mobile | Form: $form_name\n", FILE_APPEND);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '{{SMTP_USER}}';
        $mail->Password = '{{SMTP_PASS}}';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('{{SMTP_USER}}', 'Website Lead');

        $mail->addAddress('');

        $mail->isHTML(true);
        $mail->Subject = "New Lead: $form_name - test project 3";
        $mail->Body = "
            <h2>New Lead Submission</h2>
            <p><strong>Form Name:</strong> {$form_name}</p>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Phone:</strong> {$mobile}</p>
            <p><strong>Source URL:</strong> " . ($_POST['currentUrl'] ?? 'Not specified') . "</p>
        ";

        if ($mail->send()) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'Email sent successfully']);
                exit();
            } else {
                header("Location: thankyou.html");
                exit();
            }
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
                exit();
            } else {
                echo "Mailer Error: " . $mail->ErrorInfo;
            }
        }

    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
            exit();
        } else {
            echo "Error Sending Email: {$mail->ErrorInfo}";
        }
    }

} else {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
        exit();
    } else {
        echo "⚠️ Invalid Request: Must be POST.";
    }
}