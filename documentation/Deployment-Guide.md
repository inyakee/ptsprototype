# Deployment Guide

## Shared Hosting

Compatible with InfinityFree, Hostinger, Namecheap Hosting, Bluehost, cPanel hosting, and similar PHP 8+ shared hosting.

## Upload

1. Upload all site files to `public_html` or the provider's web root.
2. Keep the folder structure intact.
3. Ensure PHP 8+ is selected in hosting settings.

## SSL

Enable the host's free SSL certificate or connect Cloudflare SSL. Force HTTPS after confirming the certificate works.

## SMTP

1. Download PHPMailer from `https://github.com/PHPMailer/PHPMailer`.
2. Copy `src/PHPMailer.php`, `src/SMTP.php`, and `src/Exception.php` into `backend/phpmailer/src`.
3. Edit `backend/smtp-config.php`.
4. Use host-provided SMTP, Gmail SMTP, Outlook SMTP, Zoho SMTP, or a custom domain mailbox.

## Private Counter Storage

The contact form email subject uses an RFI reference such as `RFI - 001`. The counter is stored in the folder configured by `PRIVATE_STORAGE_DIR` in `backend/smtp-config.php`.

Keep this folder outside `public_html` or your public web root. Make sure PHP can write to it.

## cPanel Notes

- Use File Manager or FTP to upload.
- Create an email mailbox if using domain SMTP.
- Confirm outbound SMTP ports are allowed by the host.

## InfinityFree Notes

- InfinityFree may restrict external SMTP. Use an allowed mail provider or upgrade hosting if SMTP delivery is blocked.

## Hostinger, Namecheap, Bluehost

- Use the hosting email account SMTP settings from the provider dashboard.
- Test the contact form after DNS and SSL are live.
