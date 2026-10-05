#!/usr/bin/env bash
#
# Bootstrap a linked git worktree from the main checkout so a new Claude Code
# session does not have to run `composer install` / `npm install` / .env setup.
#
# Called by the SessionStart and PostToolUse(EnterWorktree) hooks in
# .claude/settings.json, with the hook input JSON on stdin. Can also be run by
# hand from inside a worktree: `bash .claude/hooks/worktree-bootstrap.sh`.
#
# vendor/ and node_modules/ are copied (never symlinked: Composer's autoloader
# resolves paths from __DIR__, so a symlinked vendor/ would load App\ classes
# from the main checkout instead of the worktree), and only when the lock files
# are identical. A branch whose locks differ gets a real install instead.

set -uo pipefail

input=''
if [ ! -t 0 ]; then
    input=$(cat)
fi

field() {
    [ -n "$input" ] || return 0
    printf '%s' "$input" | jq -r "$1 // empty" 2>/dev/null || true
}

event=$(field '.hook_event_name')
target=$(field '.tool_response.worktreePath')
target=${target:-$(field '.tool_response.path')}
target=${target:-$(field '.cwd')}
target=${target:-$PWD}

cd "$target" 2>/dev/null || exit 0
worktree=$(git rev-parse --show-toplevel 2>/dev/null) || exit 0
common=$(cd "$(git rev-parse --git-common-dir)" 2>/dev/null && pwd -P) || exit 0
main=$(dirname "$common")
worktree=$(cd "$worktree" && pwd -P)

# Not a linked worktree (main checkout, or a submodule): nothing to do.
if [ "$worktree" = "$main" ] || [ -n "$(git rev-parse --show-superproject-working-tree 2>/dev/null)" ]; then
    exit 0
fi

cd "$worktree" || exit 0
log=()

sameFile() {
    if [ ! -e "$main/$1" ] && [ ! -e "$worktree/$1" ]; then
        return 0
    fi
    cmp -s "$main/$1" "$worktree/$1"
}

# Copy into a temp name first so an interrupted copy never looks finished.
copyDir() {
    local tmp="$1.bootstrap-$$"
    rm -rf "$tmp"
    cp -a "$main/$1" "$tmp" && mv "$tmp" "$1"
}

if [ ! -f .env ] && [ -f "$main/.env" ]; then
    cp "$main/.env" .env && log+=(".env disalin dari checkout utama")
fi

if [ ! -d vendor ]; then
    if [ -d "$main/vendor" ] && sameFile composer.lock && sameFile patches.lock.json; then
        copyDir vendor && log+=("vendor/ disalin (composer.lock + patches.lock.json identik)")
        for cached in packages.php services.php; do
            [ -f "$main/bootstrap/cache/$cached" ] && cp "$main/bootstrap/cache/$cached" "bootstrap/cache/$cached"
        done
    else
        composer install --no-interaction --no-progress >&2 \
            && log+=("composer install dijalankan (lock berbeda dari checkout utama)") \
            || log+=("GAGAL: composer install — periksa manual")
    fi
fi

if [ ! -d node_modules ]; then
    if [ -d "$main/node_modules" ] && sameFile package-lock.json; then
        copyDir node_modules && log+=("node_modules/ disalin (package-lock.json identik)")
    else
        npm ci --no-audit --no-fund >&2 \
            && log+=("npm ci dijalankan (package-lock.json berbeda dari checkout utama)") \
            || log+=("GAGAL: npm ci — periksa manual")
    fi
fi

if [ ! -d public/build ] && [ -d "$main/public/build" ]; then
    copyDir public/build && log+=("public/build/ disalin (jalankan npm run build bila resources/ berubah)")
fi

if [ ! -e public/storage ] && [ -d vendor ]; then
    php artisan storage:link --no-interaction >/dev/null 2>&1 && log+=("storage:link dibuat")
fi

# A cached config makes the test suite ignore phpunit.xml's sqlite override
# and migrate the dev database instead (see CLAUDE.md, "Running Tests").
rm -f bootstrap/cache/config.php

if [ ${#log[@]} -eq 0 ]; then
    message="Worktree $worktree sudah siap (vendor, node_modules, .env tersedia). Jangan jalankan composer install / npm install."
else
    message="Worktree $worktree di-bootstrap otomatis: $(IFS=';'; echo "${log[*]}"). Jangan jalankan composer install / npm install lagi."
fi

if [ "$event" = "PostToolUse" ]; then
    jq -n --arg ctx "$message" '{hookSpecificOutput: {hookEventName: "PostToolUse", additionalContext: $ctx}}'
else
    echo "$message"
fi

exit 0
