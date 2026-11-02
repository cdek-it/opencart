#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

CHANGED_FILES="$("$SCRIPT_DIR/diff-files.sh" 'upload/*.php' | grep -v '/vendor/' || true)"

if [ -z "$CHANGED_FILES" ]; then
    echo "Нет изменённых PHP-файлов в upload/ — phpcs пропущен"
    exit 0
fi

echo "Изменённые PHP-файлы в upload/:"
echo "$CHANGED_FILES"
echo

# shellcheck disable=SC2086
vendor/bin/phpcs --standard="$SCRIPT_DIR/../phpcs.xml" $CHANGED_FILES
