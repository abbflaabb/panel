# Domain & DNS Manager

The panel now includes an admin-only Domain & DNS Manager under **Admin Panel → Domains & DNS**. It stores managed domains and local DNS record metadata, and synchronizes record creation and deletion with Cloudflare when a token is configured.

## Cloudflare setup

Create a Cloudflare API Token with the minimum permissions **Zone:Read** and **DNS:Edit**, restricted to the zones that the panel should manage. Add the token to the runtime `.env` file; never commit it:

```env
CLOUDFLARE_API_TOKEN=your-scoped-token
```

Then clear the Laravel configuration cache:

```bash
php artisan optimize:clear
php artisan migrate
```

## Usage

Open **Admin Panel → Domains & DNS**, add a root domain such as `example.com`, and optionally provide its Cloudflare Zone ID. If the token is configured, the panel will attempt to discover the Zone ID automatically. Add DNS records by selecting a supported type: `A`, `AAAA`, `CNAME`, `TXT`, `MX`, or `SRV`.

The manager normalizes record names to the selected domain, validates domain and record input, uses HTTPS for Cloudflare API requests, and stores only the Cloudflare record ID locally. Deleting a managed record attempts to delete the Cloudflare record first and removes the local record only after the remote deletion succeeds or the remote record is already missing.

If Cloudflare is not configured, domains and local record metadata can still be created for development, but no live DNS changes are sent.
