# SpringApex production deployment

Production is isolated under `/srv/springapex` and uses the Compose project name `springapex`.
The WordPress HTTP container listens only on `127.0.0.1:38100`; public traffic enters through the dedicated `web.apex-springs.com` Nginx server block.

## Managed paths

- `/srv/springapex/compose.yml` and `/srv/springapex/.env`: root-owned runtime configuration.
- `/srv/springapex/data/mariadb`: production database files.
- `/srv/springapex/wordpress`: WordPress core, uploads and runtime data.
- `/srv/springapex/wordpress/wp-content/themes/springapex`: deployed from GitHub.
- `/srv/springapex/wordpress/wp-content/plugins/webp-converter-for-media`: deployed from GitHub.

The GitHub key is forced through `/usr/local/bin/springapex-deploy-command`. It can only run write-only `rrsync` inside this site's `wp-content` directory or the site health check; it cannot run an interactive shell.

## Initial and recovery operations

Run all commands from `/srv/springapex` and target the explicit Compose file:

```bash
docker compose --env-file .env -f compose.yml ps
docker compose --env-file .env -f compose.yml logs --tail=100 wordpress
docker compose --env-file .env -f compose.yml --profile tools run --rm cli core version
docker compose --env-file .env -f compose.yml --profile tools run --rm cli option get home
```

Before any database URL change:

```bash
docker compose --env-file .env -f compose.yml exec -T database \
  sh -c 'mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' \
  > backups/before-url-change.sql
```

Then run `wp search-replace` first with `--dry-run`, followed by the real command only after reviewing the count.

## DNS and TLS

Point the `A` record for `web.apex-springs.com` to `95.169.2.68`. Use `nginx-web.apex-springs.com.conf` for the initial HTTP/ACME stage. After public DNS resolves, issue the certificate with Certbot webroot mode and install `nginx-web.apex-springs.com.tls.conf` as the active server block. Update `SPRINGAPEX_SITE_URL` in the root-owned `.env` from `http://web.apex-springs.com` to `https://web.apex-springs.com`, recreate only the `wordpress` service, and run a backed-up WordPress URL replacement from HTTP to HTTPS.
