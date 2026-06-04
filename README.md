# Physical Therapy Services Website Redesign

Production-ready static HTML/CSS/JavaScript redesign with PHP form processing for Physical Therapy Services in Eugene, Oregon.

## Pages

- `index.html`
- `about.html`
- `services.html`
- `massage-therapy.html`
- `team.html`
- `new-patients.html`
- `contact.html`

## Backend

- `backend/contact.php` handles general inquiries.
- `backend/config.php` contains validation, CSRF, rate limiting, sanitization, and mail helpers.
- `backend/smtp-config.php` stores SMTP settings. Replace placeholders before production.
- `PRIVATE_STORAGE_DIR` in `backend/smtp-config.php` points to a private folder outside the public site for the RFI counter.

## Local Preview

Run from this folder:

```bash
php -S localhost:8080
```

Then open `http://localhost:8080`.

## Production Checklist

1. Upload files to the hosting public web root.
2. Install PHPMailer into `backend/phpmailer/src`.
3. Update `backend/smtp-config.php` with real SMTP credentials.
4. Enable SSL.
5. Create the private counter folder referenced by `PRIVATE_STORAGE_DIR` outside `public_html`, writable by PHP.
6. Test the contact form from the production domain.
7. Replace remote staff photo URLs with local optimized copies if the clinic wants to host all assets directly.
