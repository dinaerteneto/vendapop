# VendaPop — Instruções para Claude Code

## Regras de Git

### Merge para `main` exige tagging e release notes

Sempre que houver merge (ou commit direto) na branch `main`:

1. **Determinar a próxima versão** — ler o `RELEASE_NOTES.md` e incrementar o patch (`v1.14.X → v1.14.X+1`). Para features novas, incrementar minor (`v1.14.X → v1.15.0`).

2. **Atualizar `RELEASE_NOTES.md`** — adicionar seção no topo com:
   - Versão e título descritivo
   - Data e branch
   - Lista de correções/features agrupadas por contexto
   - Tabela de arquivos alterados
   - Git log dos commits incluídos

3. **Commitar as release notes**:
   ```
   chore(release): update release notes for vX.Y.Z
   ```

4. **Criar tag git anotada**:
   ```bash
   git tag -a vX.Y.Z -m "vX.Y.Z — Descrição breve"
   ```

Não fazer push/tag sem ter atualizado o `RELEASE_NOTES.md` primeiro.
