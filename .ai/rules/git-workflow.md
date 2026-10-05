# Git Workflow

- Use Conventional Commits with the format `<type>(<scope>): <description>`.
- Write the description and optional body in Indonesian using imperative present tense.
- Choose a scope from the closest application domain or a repository-level scope such as `docs`, `deps`, `ci`, `config`, or `tests`.
- Keep each commit atomic and selectively stage only the files that belong to that logical change.
- Use normal `git commit` commands and never bypass hooks with `--no-verify`.
- Every commit must end with exactly this canonical trailer:

  ```text
  Co-Authored-By: SupportAgent <support@mitracoretech.id>
  ```

- `.githooks/commit-msg` is the tracked source of truth. The active `.git/hooks/commit-msg` hook removes tool-specific attribution trailers and appends the canonical trailer as the final non-empty line.
- Before the first commit in a checkout, run `bash .githooks/install` and verify the tracked and active hooks match.
- After each commit, run `git log -1 --format=full` and confirm the final message contains exactly one canonical trailer.
- Prefer `git pull --ff-only` and `git merge --ff-only`. Create an explicit merge commit only after user approval, using a repository-compliant message and normal hook execution.

## Branch Flow: Worktree → `develop` → `main`

`develop` is the main development branch; `main` is the release branch. The
integration flow has two layers:

1. **Worktree → `develop`.** Every worktree (including one created
   automatically via `EnterWorktree`, named `worktree-bridge-cse_*`, or
   created manually via `git worktree add`) works on its own new branch,
   never directly on `develop`. That branch gets merged into `develop` once
   its tests are green — via a local merge (`git merge --ff-only`, or a
   merge commit after user approval per the rules above) or via a GitHub
   Pull Request, depending on the choice made in the
   `finishing-a-development-branch` skill. `develop` itself must never be
   deleted or treated as "done" by any single worktree.
2. **`develop` → `main` via Pull Request.** `main` only receives changes via
   a Pull Request from `develop` (see history: every advance of `main` is a
   "Merge pull request ... from awankumay/develop"), **never** via a direct
   `git push`/`git merge` to `main`. Open that PR when the user asks for it,
   not as an automatic step right after a worktree gets merged into
   `develop` — `develop` can hold several worktrees' worth of work before
   they are collected into one release to `main`.

`worktree.baseRef: "head"` in `.claude/settings.json` makes a new worktree
branch off the current local HEAD (normally `develop`), not off GitHub's
default branch (`main`) — do not change it to `"fresh"` without a strong
reason, since that would make new worktrees branch off `main`, which lags
far behind `develop`.
