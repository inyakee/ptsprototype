<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

require_post();
enforce_rate_limit('contact');
verify_security_fields();

$firstName = required_value('first_name', 'First Name', 80);
$lastName = required_value('last_name', 'Last Name', 80);
$name = trim($firstName . ' ' . $lastName);
$email = required_email();
$phone = clean_text((string) ($_POST['phone'] ?? ''), 60);
$subject = required_value('subject', 'Subject', 160);
$message = required_value('message', 'Message', 2000);

try {
    $rfiCode = next_rfi_reference();
} catch (Throwable $exception) {
    error_log('RFI reference error: ' . $exception->getMessage());
    json_response(['success' => false, 'message' => 'The message could not be prepared. Please call the clinic.'], 500);
}
$emailSubject = 'RFI - ' . $rfiCode;

$body = '<h2>New Contact Form Message</h2>'
    . message_row('Reference', $emailSubject)
    . message_row('First Name', $firstName)
    . message_row('Last Name', $lastName)
    . message_row('Email', $email)
    . message_row('Phone', $phone)
    . message_row('Subject', $subject)
    . message_row('Message', $message);

$sent = send_clinic_email(CLINIC_EMAIL, $emailSubject, $body, $email, $name);

if (!$sent) {
    json_response(['success' => false, 'message' => 'The message could not be sent. Please call the clinic.'], 500);
}

json_response(['success' => true, 'message' => 'Thank you. Your message has been sent to the PTS team.']);
