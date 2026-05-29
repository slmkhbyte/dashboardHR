#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

usage() {
    cat <<'EOF'
Usage:
  bash dev.sh up
  bash dev.sh up-build
  bash dev.sh down
  bash dev.sh reset
  bash dev.sh test
  bash dev.sh logs
  bash dev.sh artisan <command...>
  bash dev.sh composer <command...>
  bash dev.sh npm <command...>

Examples:
  bash dev.sh up
  bash dev.sh artisan migrate:status
  bash dev.sh composer install
  bash dev.sh npm run build
EOF
}

require_args() {
    if [[ $# -eq 0 ]]; then
        echo "Missing arguments."
        echo
        usage
        exit 1
    fi
}

command_name="${1:-}"

case "$command_name" in
    up)
        docker compose up
        ;;
    up-build)
        docker compose up --build
        ;;
    down)
        docker compose down
        ;;
    reset)
        docker compose run --rm app php artisan migrate:fresh --seed
        ;;
    test)
        docker compose run --rm app php artisan test
        ;;
    logs)
        docker compose logs -f
        ;;
    artisan)
        shift
        require_args "$@"
        docker compose run --rm app php artisan "$@"
        ;;
    composer)
        shift
        require_args "$@"
        docker compose run --rm app composer "$@"
        ;;
    npm)
        shift
        require_args "$@"
        docker compose run --rm vite npm "$@"
        ;;
    ""|-h|--help|help)
        usage
        ;;
    *)
        echo "Unknown command: $command_name"
        echo
        usage
        exit 1
        ;;
esac
