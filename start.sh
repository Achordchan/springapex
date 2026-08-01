#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$PROJECT_DIR"
REQUESTED_PORT="${1:-${PORT:-8877}}"

if (( $# > 1 )); then
  echo "用法：./start.sh [端口号]"
  exit 1
fi

if [[ ! -f "$THEME_DIR/start-preview.sh" ]]; then
  echo "未找到可执行的预览脚本：$THEME_DIR/start-preview.sh"
  exit 1
fi

if [[ ! -x "$THEME_DIR/start-preview.sh" ]]; then
  echo "预览脚本不可执行：chmod +x start-preview.sh"
  exit 1
fi

exec "$THEME_DIR/start-preview.sh" "$REQUESTED_PORT"
