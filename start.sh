#!/usr/bin/env bash
# 启动本项目：真实 WordPress，含前台和后台。项目根目录就是完整站点，
# 主题位于 wp-content/themes/springapex/。
#
#   ./start.sh          默认 8899 端口
#   ./start.sh 9000     指定端口（需同时改数据库里的 siteurl / home）
set -euo pipefail

WP_DIR="$(cd "$(dirname "$0")" && pwd)"
PORT="${1:-8899}"

if ! command -v php >/dev/null 2>&1; then
  echo "未找到 PHP，请先安装 PHP 8.0 或更高版本。"
  exit 1
fi

if ! php -r 'exit(version_compare(PHP_VERSION, "8.0.0", ">=") ? 0 : 1);'; then
  echo "当前 PHP 版本低于 8.0，无法运行 WordPress。"
  exit 1
fi

if [[ ! -f "$WP_DIR/wp-config.php" || ! -f "$WP_DIR/wp-router.php" ]]; then
  echo "站点文件不完整：$WP_DIR 下缺少 wp-config.php 或 wp-router.php。"
  exit 1
fi

# WordPress 的站点地址写死在数据库里，换端口会导致跳转回原端口，所以这里不自动换端口。
if command -v lsof >/dev/null 2>&1 && lsof -nP -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; then
  echo "端口 ${PORT} 已被占用。可能网站已经在跑了，先打开看看："
  echo "  http://127.0.0.1:${PORT}/"
  echo "确认要换端口的话，需要同时改数据库里的 siteurl / home。"
  exit 1
fi

# 数据库没起来的话，页面会白屏，提前说清楚。
if command -v mysqladmin >/dev/null 2>&1 && ! mysqladmin ping --silent >/dev/null 2>&1; then
  echo "提示：MySQL 好像没有启动，网站会打不开。可以试试："
  echo "  brew services start mysql"
  echo ""
fi

echo ""
echo "NorenSpring WordPress 已启动"
echo ""
echo "  前台     http://127.0.0.1:${PORT}/"
echo "  后台     http://127.0.0.1:${PORT}/wp-admin/"
echo "  网站内容 http://127.0.0.1:${PORT}/wp-admin/admin.php?page=springapex-content"
echo ""
echo "  账号 achord   密码 woshichen123     （本地开发用，上线不要沿用）"
echo ""
echo "主题就在 wp-content/themes/springapex/，改完代码刷新页面即可生效。"
echo "按 Ctrl+C 停止"
echo ""

if [[ "${SPRINGAPEX_WP_OPEN:-0}" == "1" ]] && command -v open >/dev/null 2>&1; then
  (sleep 0.8 && open "http://127.0.0.1:${PORT}/") &
fi

# 多进程：后台里一个页面会同时请求多个 PHP 资源，单进程会互相阻塞。
export PHP_CLI_SERVER_WORKERS=10
exec php -S "127.0.0.1:${PORT}" -t "$WP_DIR" "$WP_DIR/wp-router.php"
