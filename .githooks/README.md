# Git Hooks

`.githooks/` contains the tracked hook sources. Git executes hooks from its active hooks directory, which is not versioned, so install them once per clone:

```bash
bash .githooks/install
```

The installer resolves the active Git hooks directory, copies the tracked `commit-msg` hook, preserves executable permission, and verifies that the installed hook matches the source.

## commit-msg

The hook removes any existing `Co-Authored-By` trailer, regardless of casing or tool identity, and appends exactly one canonical trailer as the final non-empty line:

```text
Co-Authored-By: SupportAgent <support@mitracoretech.id>
```

Remote plugins and repository-local skills receive the same enforcement when they use normal `git commit` commands. Never bypass the hook with `--no-verify`.

To verify an existing checkout manually:

```bash
cmp .githooks/commit-msg "$(git rev-parse --git-path hooks)/commit-msg"
```
