# NorenSpring AWS production deployment

Production runs on the AWS EC2 instance managed by BT Panel. The site uses BT
Panel's native Nginx, PHP 8.3 and MySQL/MariaDB services; Docker is not part of
the production request path.

## Managed resources

- BT Panel site: `norenspring.com`
- Site root: `/www/wwwroot/norenspring.com`
- Canonical URL: `https://www.norenspring.com`
- Redirect host: `norenspring.com`
- CloudFront origin hostname: `origin.norenspring.com`
- Nginx vhost: `/www/server/panel/vhost/nginx/norenspring.com.conf`
- WordPress database: managed and visible in BT Panel
- Production backups: managed as BT Panel scheduled tasks and copied to the
  private AWS backup bucket

## AWS storage and CDN

- Private bucket: `norenspring-prod-storage-20260825-7e4c9a`
- CloudFront distribution: `E3KAOKVHE37PM3`
- CloudFront domain: `d1i3aekcxk6dsb.cloudfront.net`
- Public CDN domain: `cdn.norenspring.com`
- Whole-site CloudFront distribution: `E3RJJNEV93MH8L`
- Whole-site CloudFront domain: `d1toybyvcdpqap.cloudfront.net`
- EC2 instance role: `NorenSpringEC2RoleNorenSpringStorageAccess`

The bucket has ACLs disabled, all public access blocked, versioning enabled and
SSE-S3 default encryption. CloudFront is restricted to the bucket's `public/`
origin path. Inquiry attachments use `private/inquiries/`; backups use
`backups/`. Neither private prefix is exposed through CloudFront.

The public hosts `norenspring.com` and `www.norenspring.com` are DNS-only
CNAME records pointing to the whole-site CloudFront distribution. That
distribution connects to `origin.norenspring.com` over HTTPS, redirects HTTP
viewers to HTTPS, forwards all WordPress request methods and uses the managed
`CachingDisabled` policy. It therefore acts as the public AWS reverse proxy
without caching dynamic WordPress HTML. The separate `cdn.norenspring.com`
distribution continues to serve immutable theme assets from the bucket.

The origin hostname remains a DNS-only A record to the EC2 address so
CloudFront can reach it directly. The Nginx certificate must cover
`norenspring.com`, `www.norenspring.com` and `origin.norenspring.com`. Install
`deploy/springapex-certbot-deploy` as the root-owned executable
`/etc/letsencrypt/renewal-hooks/deploy/springapex-certbot-deploy`; successful
Certbot renewals then copy the renewed certificate into BT Panel's certificate
directory, validate Nginx and reload it. Also schedule the same executable as a
daily BT Panel Shell task after the normal Certbot renewal task. Failed
certificate deployments retain a pending marker and are retried by that task;
unchanged certificate pairs without a pending marker exit without reloading
Nginx. Before enabling the vhost, create a root-owned `releases/` directory,
copy the current certificate pair into a version directory, and atomically
point `current` at that directory. The top-level BT Panel `fullchain.pem` and
`privkey.pem` entries may be relative symlinks to `current/` for panel
compatibility. Renewals write and verify a complete new version directory,
then atomically replace only the `current` symlink, so an interruption cannot
leave Nginx with a mismatched certificate and private key.

CloudFront sends the viewer address in `CloudFront-Viewer-Address`, while PHP
otherwise sees the CloudFront edge address as `REMOTE_ADDR`. Install
`deploy/springapex-cloudfront-real-ip-update` as the root-owned executable
`/usr/local/sbin/springapex-cloudfront-real-ip-update`, run it once before
activating the repository Nginx vhost, then schedule it in BT Panel once daily.
The script builds Nginx trust directives only from AWS's
`CLOUDFRONT_ORIGIN_FACING` ranges, rejects unexpectedly empty responses,
validates Nginx and reloads it only when the range file changes. This keeps the
contact-form IP rate limit isolated per visitor without trusting a forged
viewer header from a direct origin request.

Production `wp-config.php` must define:

```php
define('SPRINGAPEX_S3_BUCKET', 'norenspring-prod-storage-20260825-7e4c9a');
define('SPRINGAPEX_S3_REGION', 'us-east-1');
define('SPRINGAPEX_S3_PRIVATE_PREFIX', 'private/inquiries');
define('SPRINGAPEX_CDN_URL', 'https://cdn.norenspring.com');
define('DISALLOW_FILE_EDIT', true);
```

`DISALLOW_FILE_EDIT` disables the appearance/plugin file editors in wp-admin:
all code reaches production through the GitHub pipeline, so the editor only
serves as a webshell shortcut for a compromised admin session. Keep it defined
in the production `wp-config.php` (that file is outside the deployment
pipeline and must be edited on the host).

## Media conversion

Converter for Media uses the BT Panel PHP 8.3 runtime to generate WebP and
AVIF files under `wp-content/uploads-webpc/`. That runtime must load the
Imagick extension; checking only the operating system PHP is insufficient.
Production currently uses PECL Imagick 3.8.1 compiled with
`/www/server/php/83/bin/phpize` and
`/www/server/php/83/bin/php-config`, backed by ImageMagick 6 with WebP and AVIF
delegates. Both `/www/server/php/83/etc/php.ini` and `php-cli.ini` load
`imagick.so`.

The repository Nginx vhost also performs content negotiation for public images
under `wp-content/`: it prefers a generated AVIF file when the browser accepts
AVIF, falls back to WebP, and finally serves the original file. The private
inquiry upload location remains protected by its higher-priority `^~` rule.
After changing PHP or Nginx, verify all three layers:

```bash
/www/server/php/83/bin/php -r 'echo phpversion("imagick"), PHP_EOL;'
/www/server/php/83/sbin/php-fpm -i | grep 'imagick module version'
curl -I -H 'Accept: image/webp' \
  https://www.norenspring.com/wp-content/uploads/webp-converter-for-media-test.png
```

The last response must retain the original URL while returning
`Content-Type: image/webp` and `Vary: Accept`.

AWS credentials must not be stored in WordPress, BT Panel or GitHub. The theme
uses IMDSv2 to obtain short-lived credentials from the attached EC2 role.
The production puller calls the local `springapex-cdn-prepare <version>`
command, stages only the new `assets/` directory, then invokes
`springapex-cdn-sync <version>` before changing the live theme. The sync
command publishes
`public/theme/<version>/assets/` and removes the staging directory. Only after
that succeeds does rsync activate the new theme version, so a failed CDN
publish cannot leave production pointing at missing assets. The command rejects
every symbolic link and tells AWS CLI not to follow links; PHP source files and
server-local files are never published. Every object in this versioned prefix
is uploaded with `Cache-Control: public,max-age=31536000,immutable`; the deploy
command reads the metadata back from S3 and fails before theme activation if it
is missing. Because the version directory is never overwritten with different
content, no CloudFront invalidation is required for a normal release.

PR and `main` workflows run `verify-asset-version-bump.sh`: any change under
the theme's `assets/` directory must also advance `SPRINGAPEX_VERSION`, so an
immutable CloudFront prefix is never overwritten with different content.

BT Panel keeps seven daily local copies of the site and database. Install
`deploy/springapex-backup-to-s3` as
`/usr/local/sbin/springapex-backup-to-s3` with mode `0755`, then create a BT
Panel Shell scheduled task at 03:00 daily that runs that command after the
02:00 site backup and 02:30 database backup. The script uses the EC2 instance
role, validates both gzip archives, uploads them under `backups/site/` and
`backups/database/`, and verifies the S3 object size and SSE-S3 encryption.
The bucket lifecycle rule `NorenSpring backups 7-day retention` applies only
to the `backups/` prefix: current versions expire after 7 days, noncurrent
versions are permanently deleted after 1 day, and incomplete multipart uploads
are removed after 1 day. The rule does not affect CDN assets or private inquiry
attachments.

The public website no longer uses preview Basic Auth, `X-Robots-Tag: noindex`
or WordPress `blog_public=0`.

## GitHub deployment boundary

Pushes to `main` deploy only these repository-managed directories:

- `wp-content/themes/springapex`
- `wp-content/plugins/webp-converter-for-media`
- `wp-content/plugins/wp-mail-smtp`

GitHub-hosted runners perform verification only. After a successful `main`
run, the workflow creates an HMAC-authenticated tag bound to the verified SHA,
GitHub run ID and run attempt, then advances the fast-forward-only
`production-ready` branch.
BT Panel runs `/usr/local/sbin/springapex-pull-production` every minute as the
unprivileged `springapex-deploy` user. The EC2 host uses an encrypted-at-rest,
read-only GitHub deploy key to fetch that branch over outbound SSH, requires
the marker to remain in the latest `main` history, verifies the newest HMAC
attestation generation, rejects symlinks, lints PHP, publishes CDN assets and
activates only the three managed code directories. Extracted release trees are
removed after success, and stale candidate directories are cleared while the
deployment lock is held. No GitHub Action or repository script executes on the
production host, and public SSH remains restricted to the management IP.

Because the deployment state includes the GitHub run ID and run attempt, a
successful manual `workflow_dispatch` creates a new authenticated generation
and intentionally redeploys the same commit to repair production drift. The
puller also requires every new release SHA to descend from the deployed SHA
and rejects older run generations for the same commit, preventing marker or
attestation replay from rolling production back.

Recovery setup installs the puller as a root-owned executable, but the task
itself runs without root privileges:

```bash
install -d -o springapex-deploy -g www -m 0700 /srv/springapex/releases
install -o root -g root -m 0755 \
  deploy/springapex-pull-production \
  /usr/local/sbin/springapex-pull-production
```

Before the first deployment on an empty host, root must explicitly initialize
`/srv/springapex/releases/bootstrap.sha` with the trusted, currently verified
`production-ready` SHA and then set ownership to `springapex-deploy:www` with
mode `0600`. The puller requires that SHA to equal both the current `main` tip
and marker, then removes `bootstrap.sha` after writing `deployed.sha`. If
`deployed.sha` is later lost, deployment fails closed until root performs a
new trusted bootstrap; historical attestations are never accepted implicitly.

The read-only GitHub deploy key is stored as
`/home/springapex-deploy/.ssh/github_readonly`; its known-hosts file contains
GitHub's published ED25519 host key. The matching HMAC secret is stored at
`/home/springapex-deploy/.config/release_hmac_key` and as the protected GitHub
secret `SPRINGAPEX_RELEASE_HMAC_KEY`. The BT Panel task must execute the puller
as `springapex-deploy`, never as `root` or `www`.

The root-owned `/usr/local/bin/springapex-deploy-command` is invoked locally by
the unprivileged puller. It exposes only CDN prepare/sync and the site health
check; it has no inbound SSH or rsync command path. The CDN commands reject
symlinked staging roots, version directories and asset directories after
realpath checks.

## Required server ownership

The deployment account owns only the managed code directories. WordPress,
uploads, `wp-config.php`, the database and BT Panel configuration remain owned
by the site runtime account.

```bash
install -d -o springapex-deploy -g root -m 0700 \
  /www/wwwroot/norenspring.com/wp-content/.springapex-cdn-stage

install -d -o springapex-deploy -g www -m 2750 \
  /www/wwwroot/norenspring.com/wp-content/themes/springapex \
  /www/wwwroot/norenspring.com/wp-content/plugins/webp-converter-for-media \
  /www/wwwroot/norenspring.com/wp-content/plugins/wp-mail-smtp
```

## Production checks

Run these after a release; a green GitHub Actions run alone is insufficient.

```bash
curl -fsS --resolve www.norenspring.com:443:127.0.0.1 \
  https://www.norenspring.com/ >/dev/null
curl -fsSI https://www.norenspring.com/
curl -fsSI https://norenspring.com/
curl -fsSI https://www.norenspring.com/wp-json/
curl -sS -o /dev/null -w '%{http_code}\n' \
  https://www.norenspring.com/wp-content/uploads/springapex-private/probe.txt
```

Verify in BT Panel as well:

- Nginx, PHP and database services are running.
- PHP upload limits are 10 MB per file and 12 MB per request.
- The private attachment URL returns 403 or 404.
- Public responses include `via`, `x-cache` and `x-amz-cf-pop` CloudFront
  headers, and the public DNS answers no longer expose the EC2 address.
- `norenspring.com` redirects to `https://www.norenspring.com` while retaining
  the original path and query string.
- A CloudFront request reaches PHP with the viewer IP in `REMOTE_ADDR`, so the
  inquiry IP rate limit is not shared by an entire CloudFront edge.
- WordPress `home` and `siteurl` are `https://www.norenspring.com`.
- WordPress `blog_public` is `1`.
- `/xmlrpc.php` returns 404 at the Nginx layer. A post with pingbacks
  allowed carries no `X-Pingback` header, and an XML-RPC `system.listMethods`
  call returns no methods (the theme empties the method table in
  `inc/hardening.php`), so either layer alone keeps the endpoint closed.
- `Simple History 5.31.0` is installed from the WordPress directory. It is
  maintained manually and is NOT part of the deployment pipeline (only the two
  managed plugin directories are rsynced): record updates in this file when its
  version changes. Audit logs live in its own database tables and survive
  deployments.
- Recent Nginx, PHP and WordPress logs contain no fatal errors.
- Let's Encrypt renewal covers all production hostnames.
- Daily site and database backup tasks are enabled and have a successful run.

## Rollback

The migration source backup contains a logical database dump, the complete
WordPress tree, runtime configuration and checksums. Keep the old VPS stopped
but intact until the AWS deployment, GitHub workflow, inquiry upload/download,
email delivery and scheduled backups have all passed production acceptance.
