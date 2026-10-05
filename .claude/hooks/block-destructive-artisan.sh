#!/usr/bin/env bash
# Memblokir perintah artisan yang menghapus seluruh database.

input=$(cat)
cmd=$(echo "$input" | jq -r '.tool_input.command // empty')

# Cocok untuk: php artisan migrate:fresh, ... --force, ... --seed,
# ./vendor/bin/sail artisan migrate:fresh, dll.
if echo "$cmd" | grep -Eq 'artisan[[:space:]]+(migrate:fresh|migrate:refresh|migrate:reset|db:wipe)'; then
  echo "DIBLOKIR: migrate:fresh menghapus semua data." >&2
  exit 2
fi

exit 0
