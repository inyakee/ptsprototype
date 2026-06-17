<?php
declare(strict_types=1);

// Copy this file to smtp-config.php on the server and replace these values.
// Keep smtp-config.php out of Git when it contains real credentials.
const SMTP_HOST = 'smtp.example.com';
const SMTP_USERNAME = 'clinic@example.com';
const SMTP_PASSWORD = 'replace-with-secure-password';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls'; // tls, ssl, or empty string.

const CLINIC_EMAIL = 'info@ptsclinic.com';
const CLINIC_NAME = 'Physical Therapy Services';
const CLINIC_PHONE = '(541) 345-7532';
const CLINIC_ADDRESS = '1310 Coburg Rd #5, Eugene, OR 97401';

// Keep generated RFI counters outside the public website folder.
const PRIVATE_STORAGE_DIR = __DIR__ . '/../../private';

