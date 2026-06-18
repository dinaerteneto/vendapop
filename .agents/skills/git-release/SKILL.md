# Git Release Workflow

Automates conventional commits, release notes, version tagging, branch merging, and production deployment.

## When to Use

- After completing a feature branch and wanting to merge to main/master
- When preparing a release with proper version tags
- When generating changelog/release notes from conventional commits
- When squashing or reorganizing commits before merge

## Workflow

### 1. Validate Conventional Commits

All commits on the feature branch must follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types:** `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`, `ci`, `build`, `revert`

**Valid scopes for this project:** `backend`, `frontend`, `e2e`, `docs`, `deploy`, `landing`, `auth`, `admin`

Run:
```bash
git log --oneline feature/onboarding-wizard ^main
```

If any commit doesn't follow the pattern, ask whether to:
- **A)** Rewrite commit messages with `rebase -i`
- **B)** Squash all into a single conventional commit
- **C)** Proceed as-is

### 2. Generate Release Notes

From the commit log, generate release notes grouped by type:

```markdown
# v{MAJOR}.{MINOR}.{PATCH} — {YYYY-MM-DD}

## Features
- feat(scope): description

## Fixes
- fix(scope): description

## Chores
- chore(scope): description
```

**Version bump rules** (SemVer):
- `feat` commits → bump MINOR
- `fix` commits → bump PATCH
- `feat!` or `BREAKING CHANGE` → bump MAJOR

Write release notes to `RELEASE_NOTES.md` (single file, newest version first).

### 3. Tagging

After merge, create an annotated tag:

```bash
git tag -a v{version} -m "v{version}: {short summary}"
```

Push tag:
```bash
git push origin v{version}
```

### 4. Merge Strategy

**Standard merge (preserves history):**
```bash
git checkout main
git merge --no-ff feature/onboarding-wizard -m "merge: feature/onboarding-wizard → main (v{version})"
```

**Squash merge (clean history):**
```bash
git checkout main
git merge --squash feature/onboarding-wizard
git commit -m "feat: onboarding wizard + image uploader (v{version})

{categorized summary from release notes}"
```

**Choice:** Ask the user which strategy. Default to `--no-ff` for feature branches with well-structured commits.

### 5. Post-Merge Checklist

- [ ] Commits follow conventional format
- [ ] Release notes appended to `RELEASE_NOTES.md` (newest first)
- [ ] Version tag created and pushed
- [ ] Merged to main/master
- [ ] Feature branch deleted (optional)
- [ ] Mentioned in changelog if applicable

## Commands Reference

```bash
# Check commits on feature branch
git log --oneline feature/onboarding-wizard ^main

# Get next version suggestion
git tag --sort=-v:refname | head -1

# Create tag
git tag -a v1.12.0 -m "v1.12.0: onboarding wizard + image uploader"

# Merge (no fast-forward)
git checkout main && git merge --no-ff feature/onboarding-wizard

# Delete local branch
git branch -d feature/onboarding-wizard

# Push
git push origin main --tags

# Deploy (remote)
./deploy/deploy.sh

# Deploy with specific tag
TAG=v1.12.0 ./deploy/deploy.sh
```

## Deploy Script

The `deploy/deploy.sh` script handles production deployment:

1. **Pré-requisitos:** Docker, Docker Compose, `.env.production`, rede externa `web`
2. **Git pull:** `git checkout main && git pull origin main` (ou `TAG` específico)
3. **Build:** `docker compose build` com `VITE_API_BASE_URL` e `VITE_RECAPTCHA_SITE_KEY`
4. **Migrations:** `php artisan migrate --force`
5. **Storage:** `php artisan storage:link` (se não existir)
6. **Cache:** `config:cache`, `route:cache`, `view:cache`
7. **Rolling update:** `docker compose up -d --remove-orphans`
8. **Healthcheck:** verifica containers running
9. **Cleanup:** `docker image prune -af --filter "until=72h"`

Uso no servidor:
```bash
cd /var/www/vendapop && ./deploy/deploy.sh
```

## Release Notes Template

Append to `RELEASE_NOTES.md` (newest version on top):

```markdown
## v{version} — {title}

**Data:** {YYYY-MM-DD} | **Branch:** `feature/{name}`

### Novidades

- **{Feature}** — {description}
  - Sub-item

### Database

| Migration | Tabela | Campos |
|-----------|--------|--------|

### Dependências

- {package} — {purpose}

### Git Log

```
{commits}
```
```
