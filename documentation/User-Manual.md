# User Manual

## Editing Page Content

Open the relevant `.html` file and edit the visible text inside headings, paragraphs, lists, and cards.

## Updating Contact Details

Update contact details in:

- Page footer sections
- `contact.html`
- `backend/smtp-config.php`
- Home page structured data

## Updating Forms

Contact form fields post to `backend/contact.php`.

The form uses First Name and Last Name fields. Email subjects are generated automatically as `RFI - XXX`, using the private counter folder configured in `backend/smtp-config.php`.

If fields are added to HTML, also add validation and email rows in the PHP handler.

## Updating Images

Add optimized images to `assets/images`. Use descriptive `alt` text and `loading="lazy"` for non-hero images.
