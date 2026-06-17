<?php
declare(strict_types=1);

session_start();

$smtpConfig = __DIR__ . '/smtp-config.php';
$smtpConfigExample = __DIR__ . '/smtp-config.example.php';
require_once is_file($smtpConfig) ? $smtpConfig : $smtpConfigExample;

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (isset($_GET['csrf'])) {
    json_response(['success' => true, 'csrf_token' => csrf_token()]);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['success' => false, 'message' => 'Invalid submission method.'], 405);
    }
}

function enforce_rate_limit(string $bucket, int $seconds = 45): void
{
    $key = 'rate_' . $bucket;
    $now = time();
    if (!empty($_SESSION[$key]) && ($now - (int) $_SESSION[$key]) < $seconds) {
        json_response(['success' => false, 'message' => 'Please wait a moment before sending another message.'], 429);
    }
    $_SESSION[$key] = $now;
}

function verify_security_fields(): void
{
    if (!empty($_POST['website'] ?? '')) {
        json_response(['success' => false, 'message' => 'Unable to process this submission.'], 400);
    }
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        json_response(['success' => false, 'message' => 'Please refresh the page and try again.'], 403);
    }
}

function clean_text(string $value, int $max = 500): string
{
    $value = trim(strip_tags($value));
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    return substr($value, 0, $max);
}

function clean_email(string $value): string
{
    return filter_var(trim($value), FILTER_SANITIZE_EMAIL) ?: '';
}

function required_value(string $key, string $label, int $max = 500): string
{
    $value = clean_text((string) ($_POST[$key] ?? ''), $max);
    if ($value === '') {
        json_response(['success' => false, 'message' => $label . ' is required.'], 422);
    }
    return $value;
}

function required_email(string $key = 'email'): string
{
    $email = clean_email((string) ($_POST[$key] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'A valid email address is required.'], 422);
    }
    return $email;
}

function private_storage_dir(): string
{
    $dir = defined('PRIVATE_STORAGE_DIR') ? PRIVATE_STORAGE_DIR : dirname(__DIR__, 2) . '/private';
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create private storage.');
    }
    return $dir;
}

function next_rfi_reference(): string
{
    $path = private_storage_dir() . '/rfi-counter.txt';
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Unable to open RFI counter.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Unable to lock RFI counter.');
        }

        rewind($handle);
        $current = trim(stream_get_contents($handle) ?: '');
        $next = ctype_digit($current) ? ((int) $current + 1) : 1;

        if ($next > 46655) {
            throw new RuntimeException('RFI counter has reached the three-character limit.');
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, (string) $next);
        fflush($handle);
        flock($handle, LOCK_UN);

        return strtoupper(str_pad(base_convert((string) $next, 10, 36), 3, '0', STR_PAD_LEFT));
    } finally {
        fclose($handle);
    }
}

function html_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function send_clinic_email(string $to, string $subject, string $html, string $replyToEmail = '', string $replyToName = ''): bool
{
    $phpmailerBase = __DIR__ . '/phpmailer/src';
    if (is_file($phpmailerBase . '/PHPMailer.php')) {
        require_once $phpmailerBase . '/Exception.php';
        require_once $phpmailerBase . '/PHPMailer.php';
        require_once $phpmailerBase . '/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->Port = SMTP_PORT;
            if (SMTP_SECURE !== '') {
                $mail->SMTPSecure = SMTP_SECURE;
            }
            $mail->setFrom(SMTP_USERNAME, CLINIC_NAME);
            $mail->addAddress($to);
            if ($replyToEmail !== '') {
                $mail->addReplyTo($replyToEmail, $replyToName ?: $replyToEmail);
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));
            return $mail->send();
        } catch (Throwable $exception) {
            error_log('PHPMailer error: ' . $exception->getMessage());
            return false;
        }
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . CLINIC_NAME . ' <' . CLINIC_EMAIL . '>'
    ];
    if ($replyToEmail !== '') {
        $headers[] = 'Reply-To: ' . $replyToEmail;
    }
    return mail($to, $subject, $html, implode("\r\n", $headers));
}

function message_row(string $label, string $value): string
{
    return '<p><strong>' . html_escape($label) . ':</strong> ' . nl2br(html_escape($value)) . '</p>';
}
