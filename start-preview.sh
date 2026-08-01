#!/usr/bin/env bash
set -euo pipefail
THEME_DIR="$(cd "$(dirname "$0")" && pwd)"
REQUESTED_PORT="${1:-8877}"
cd "$THEME_DIR"

if ! command -v php >/dev/null 2>&1; then
  echo "未找到 PHP，请先安装 PHP 8.0 或更高版本。"
  exit 1
fi

if ! php -r 'exit(version_compare(PHP_VERSION, "8.0.0", ">=") ? 0 : 1);'; then
  echo "当前 PHP 版本低于 8.0，无法运行此主题预览。"
  exit 1
fi

if [[ ! "$REQUESTED_PORT" =~ ^[0-9]+$ ]] || (( REQUESTED_PORT < 1 || REQUESTED_PORT > 65535 )); then
  echo "端口必须是 1 到 65535 之间的整数。"
  exit 1
fi

port_is_in_use() {
  local candidate="$1"
  if command -v lsof >/dev/null 2>&1; then
    lsof -nP -iTCP:"$candidate" -sTCP:LISTEN >/dev/null 2>&1
    return
  fi

  php -r '
    $socket = @stream_socket_client("tcp://127.0.0.1:" . $argv[1], $errorCode, $errorMessage, 0.2);
    if (is_resource($socket)) {
        fclose($socket);
        exit(0);
    }
    exit(1);
  ' "$candidate"
}

PORT="$REQUESTED_PORT"
while port_is_in_use "$PORT"; do
  if (( PORT >= 65535 )); then
    echo "端口 $REQUESTED_PORT 已被占用，且没有可用的后续端口。"
    exit 1
  fi
  PORT=$((PORT + 1))
done

if [[ "$PORT" != "$REQUESTED_PORT" ]]; then
  echo "端口 ${REQUESTED_PORT} 已被占用，改用 ${PORT}。"
fi

echo ""
echo "SpringApex 预览已启动"
echo "请在浏览器打开："
echo "  http://127.0.0.1:${PORT}/preview/index.php"
echo ""
echo "其他页面："
echo "  Products  http://127.0.0.1:${PORT}/preview/index.php?sa_page=products"
echo "  Product   http://127.0.0.1:${PORT}/preview/index.php?sa_page=product&product=compression-springs"
echo "  Solutions http://127.0.0.1:${PORT}/preview/index.php?sa_page=solutions"
echo "  About     http://127.0.0.1:${PORT}/preview/index.php?sa_page=about"
echo "  Contact   http://127.0.0.1:${PORT}/preview/index.php?sa_page=contact"
echo "  Search    http://127.0.0.1:${PORT}/preview/index.php?sa_page=search&s=compression"
echo ""
echo "按 Ctrl+C 停止"
echo ""

# 默认不唤起 GUI；显式设置环境变量后才自动打开浏览器。
if [[ "${SPRINGAPEX_PREVIEW_OPEN:-0}" == "1" ]] && command -v open >/dev/null 2>&1; then
  (sleep 0.8 && open "http://127.0.0.1:${PORT}/preview/index.php") &
fi

exec php -S "127.0.0.1:${PORT}" -t "$THEME_DIR"
