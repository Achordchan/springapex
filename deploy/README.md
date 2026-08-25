# NorenSpring AWS production deployment

Production runs on the AWS EC2 instance managed by BT Panel. The site uses BT
Panel's native Nginx, PHP 8.3 and MySQL/MariaDB services; Docker is not part of
the production request path.

## Managed resources

- BT Panel site: `norenspring.com`
- Site root: `/www/wwwroot/norenspring.com`
- Canonical URL: `https://norenspring.com`
- Redirect host: `www.norenspring.com`
- Nginx vhost: `/www/server/panel/vhost/nginx/norenspring.com.conf`
- WordPress database: managed and visible in BT Panel
- Production backups: managed as BT Panel scheduled tasks and copied to the
  private AWS backup bucket

## AWS storage and CDN

- Private bucket: `norenspring-prod-storage-20260825-7e4c9a`
- CloudFront distribution: `E3KAOKVHE37PM3`
- CloudFront domain: `d1i3aekcxk6dsb.cloudfront.net`
- Public CDN domain: `cdn.norenspring.com`
- EC2 instance role: `NorenSpringEC2RoleNorenSpringStorageAccess`

The bucket has ACLs disabled, all public access blocked, versioning enabled and
SSE-S3 default encryption. CloudFront is restricted to the bucket's `public/`
origin path. Inquiry attachments use `private/inquiries/`; backups use
`backups/`. Neither private prefix is exposed through CloudFront.

Production `wp-config.php` must define:

```php
define('SPRINGAPEX_S3_BUCKET', 'norenspring-prod-storage-20260825-7e4c9a');
define('SPRINGAPEX_S3_REGION', 'us-east-1');
define('SPRINGAPEX_S3_PRIVATE_PREFIX', 'private/inquiries');
define('SPRINGAPEX_CDN_URL', 'https://cdn.norenspring.com');
```

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
server-local files are never published.

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
curl -fsS --resolve norenspring.com:443:127.0.0.1 \
  https://norenspring.com/ >/dev/null
curl -fsSI https://norenspring.com/
curl -fsSI https://www.norenspring.com/
curl -sS -o /dev/null -w '%{http_code}\n' \
  https://norenspring.com/wp-content/uploads/springapex-private/probe.txt
```

Verify in BT Panel as well:

- Nginx, PHP and database services are running.
- PHP upload limits are 10 MB per file and 12 MB per request.
- The private attachment URL returns 403 or 404.
- WordPress `home` and `siteurl` are `https://norenspring.com`.
- WordPress `blog_public` is `1`.
- Recent Nginx, PHP and WordPress logs contain no fatal errors.
- Let's Encrypt renewal covers all production hostnames.
- Daily site and database backup tasks are enabled and have a successful run.

## Rollback

The migration source backup contains a logical database dump, the complete
WordPress tree, runtime configuration and checksums. Keep the old VPS stopped
but intact until the AWS deployment, GitHub workflow, inquiry upload/download,
email delivery and scheduled backups have all passed production acceptance.
