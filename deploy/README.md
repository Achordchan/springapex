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

生产默认禁止在线修改程序文件。只在安装受信任的 WordPress 语言包时，给当次 CLI 容器传入一次性开关：

```bash
docker compose --env-file .env -f compose.yml --profile tools run --rm \
  -e SPRINGAPEX_ALLOW_FILE_MODS=1 cli language core install zh_CN --activate
```

结束后不在 `.env` 中保留该开关，Web 容器仍会定义 `DISALLOW_FILE_MODS=true`。

## 临时调试保护

线上调试阶段启用三层保护：

1. Nginx HTTPS server 使用 `auth_basic` 和 `/etc/nginx/.htpasswd-springapex`。
2. HTTP/HTTPS 响应都返回 `X-Robots-Tag: noindex, nofollow, noarchive`。
3. WordPress `blog_public` 选项设为 `0`。

`/.well-known/acme-challenge/` 仍保持无鉴权，保证 Certbot 自动续期。GitHub Actions 的健康检查通过 `springapex-deploy-command` 直接访问 `127.0.0.1:38100`，不依赖共享预览密码。

正式开放前需同时完成：移除 Nginx `auth_basic`、移除 `X-Robots-Tag`、将 `blog_public` 恢复为 `1`，然后执行 Nginx 语法检查和公网验收。

Before any database URL change:

```bash
docker compose --env-file .env -f compose.yml exec -T database \
  sh -c 'mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' \
  > backups/before-url-change.sql
```

Then run `wp search-replace` first with `--dry-run`, followed by the real command only after reviewing the count.

## DNS and TLS

Point the `A` record for `web.apex-springs.com` to `95.169.2.68`. Use `nginx-web.apex-springs.com.conf` for the initial HTTP/ACME stage. After public DNS resolves, issue the certificate with Certbot webroot mode and install `nginx-web.apex-springs.com.tls.conf` as the active server block. Update `SPRINGAPEX_SITE_URL` in the root-owned `.env` from `http://web.apex-springs.com` to `https://web.apex-springs.com`, recreate only the `wordpress` service, and run a backed-up WordPress URL replacement from HTTP to HTTPS.
