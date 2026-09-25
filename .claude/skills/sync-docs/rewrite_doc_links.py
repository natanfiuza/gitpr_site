#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""Reescreve os links cruzados dos .md de public/content/docs para as URLs do site.

Os arquivos de public/content/docs/ sao copia fiel de <gitpr>/docs/ e trazem links
relativos entre arquivos .md (ex.: [texto](commit-message-ia.md)). No site eles viram
href relativo e dao 404, porque as paginas sao servidas pela rota catch-all
`/{page}?lang={code}` (ver app/Http/Controllers/DocsController.php).

Este script converte o DESTINO de cada link -- o TEXTO VISIVEL nunca e tocado -- para
a forma canonica do site. E idempotente: rodar duas vezes produz o mesmo byte.

Uso:
    python rewrite_doc_links.py                          # aplica no root padrao
    python rewrite_doc_links.py --check                  # dry-run; exit 1 se pendente
    python rewrite_doc_links.py --check --source <gitpr> # compara fonte x site
    python rewrite_doc_links.py --self-test

Invocar SEMPRE como `python` -- `python3` neste ambiente e o stub da Microsoft Store.
"""

from __future__ import annotations

import argparse
import re
import sys
import unicodedata
from pathlib import Path

# -- Constantes ---------------------------------------------------------------

# Forma ja usada por 286 links do proprio corpus (verificado no repo de origem).
REPO_URL_DOCS = "https://github.com/gitpr-cli/gitpr.git/blob/main/docs/"

# Sufixo do arquivo -> codigo de idioma do site. Cobre os nomes de ORIGEM (.es_es /
# .fr_fr) e os do SITE (.es / .fr), para servir aos dois lados da comparacao.
LANG_FROM_SUFFIX = {
    "en": "en", "pt_br": "pt_br", "pt_pt": "pt_pt",
    "es": "es", "fr": "fr",
    "es_es": "es", "fr_fr": "fr",
}

# Sufixos reconhecidos ao extrair o topico de um nome de arquivo.
_TOPIC_SUFFIX = re.compile(r"\.(?:pt_br|pt_pt|es_es|fr_fr|en|es|fr)$")

# Destinos que NUNCA sao tocados: URL ja final, scheme absoluto, ou ancora local.
# E isto -- e nao "termina em .md" -- que garante a idempotencia: as URLs do GitHub
# emitidas por este script TAMBEM terminam em .md.
TERMINAL_PREFIXES = ("http://", "https://", "//", "mailto:", "/", "#")

# Link inline completo. `(?<!!)` exclui imagens; `title` e opcional e preservado.
# Sem titulo no corpus hoje -- o grupo existe para falhar de forma explicita.
INLINE_LINK = re.compile(
    r"(?<!!)\[(?P<text>[^\]]*)\]\((?P<dest>[^)\s]*)"
    r"(?P<title>\s+[\"'][^\"']*[\"'])?\s*\)"
)

# Abertura/fechamento de bloco de codigo cercado. `info` so pode ser vazio no
# FECHAMENTO -- um fence de abertura com info string nao fecha nada.
_FENCE = re.compile(r"^\s{0,3}(?P<marker>`{3,}|~{3,})(?P<info>.*)$")


# -- Slugify: espelho EXATO de MarkdownViewer.vue (pass do DOM em h2/h3) -------
# A classe de espaco do JS (`\s`) difere da do Python em U+FEFF vs U+001C-001F e
# U+0085. Reproduzida literalmente para nao depender da versao do Unicode.

_JS_WS = r"\t\n\v\f\r    -     　﻿"
_SLUG_DROP = re.compile(r"[^a-z0-9" + _JS_WS + r"-]")
_SLUG_SPACES = re.compile(r"[" + _JS_WS + r"]+")
_SLUG_DASHES = re.compile(r"-+")


def slugify(text: str) -> str:
    """Mesmo algoritmo do MarkdownViewer.vue: lowercase -> NFD -> remove
    U+0300-U+036F -> remove [^a-z0-9\\s-] -> trim -> espacos->'-' -> '-+'->'-'.

    Assim `#Invocacao Direta` e `#invocacao-direta` chegam no mesmo id.
    """
    s = text.lower()
    s = unicodedata.normalize("NFD", s)
    s = "".join(ch for ch in s if not ("̀" <= ch <= "ͯ"))
    s = _SLUG_DROP.sub("", s).strip()
    s = _SLUG_SPACES.sub("-", s)
    return _SLUG_DASHES.sub("-", s)


def lang_from_filename(name: str) -> str:
    """`foo.md`->en, `foo.es.md`->es, `foo.es_es.md`->es, `foo.pt_br.md`->pt_br.

    O sufixo de idioma e o ULTIMO campo separado por ponto, nunca uma substring:
    `.md` e sempre o ultimo, o idioma vem no meio.
    """
    stem = name[:-3] if name.lower().endswith(".md") else name
    parts = stem.split(".")
    if len(parts) > 1:
        return LANG_FROM_SUFFIX.get(parts[-1].lower(), "en")
    return "en"


def _site_url(topic: str, lang: str, fragment: str = "") -> str:
    """`/docs/{topic}` com `?lang=` omitido no ingles (decisao do site)."""
    url = f"/docs/{topic}" if lang == "en" else f"/docs/{topic}?lang={lang}"
    return f"{url}#{fragment}" if fragment else url


def rewrite_destination(dest: str, lang: str):
    """Destino -> URL do site, ou None se nao houver nada a fazer.

    None significa "devolva o trecho original byte a byte" -- e o caminho de
    todas as formas nao reconhecidas.
    """
    if dest.startswith(TERMINAL_PREFIXES):
        return None

    head, _, raw_fragment = dest.partition("#")
    path = head[2:] if head.startswith("./") else head

    if not path.lower().endswith(".md"):
        return None  # .py, .json, mailto:, {placeholder}, ...

    # ../README.md -> topico `readme` (vive em public/content/docs/readme.*).
    # Antes do strip de `docs/` e do teste de subdiretorio: `../README.md` contem '/'.
    if re.search(r"(?:^|/)readme\.md$", path, re.I):
        return _site_url("readme", lang, slugify(raw_fragment))

    # `docs/foo.md` -- forma usada SO nos readme.* (14 ocorrencias).
    # ATENCAO: 'docs/' tem 5 caracteres, nao 4.
    if path.startswith("docs/"):
        path = path[len("docs/"):]

    # Subdiretorio: nao existe topico no site (plans/, tutorial/) -> repo de origem.
    # O caminho vai VERBATIM, incluindo .es_es/.fr_fr: a URL aponta para o repositorio
    # de origem, onde os arquivos tem esse nome (verificado). Normalizar aqui quebraria
    # os 6 links de tutorial/install-from-source.*. O fragmento tambem vai verbatim: o
    # slug do GitHub preserva acentos, ao contrario do slugify do site.
    if "/" in path:
        url = REPO_URL_DOCS + path
        return f"{url}#{raw_fragment}" if raw_fragment else url

    # Topico do site. O sufixo do alvo e DESCARTADO: o idioma do destino e o do
    # arquivo que contem o link. O DocsController ja cai no ingles quando falta
    # a variante, entao nao ha checagem de existencia.
    topic = _TOPIC_SUFFIX.sub("", path[:-3])
    if not topic:
        return None  # topico vazio (ex.: destino `.md`) -- nunca emitir `/docs/`

    return _site_url(topic, lang, slugify(raw_fragment))


def _split_fences(text: str):
    """[(trecho, dentro_de_bloco_cercado?)] -- preserva os bytes na remontagem.

    markdown-it nunca parseia links dentro de um fence, entao o script tambem nao
    pode: reescrever ali mudaria codigo que deve aparecer literal na tela.
    """
    segments, buf = [], []
    inside, marker = False, None

    for line in text.splitlines(keepends=True):
        match = _FENCE.match(line)
        opening = bool(match) and not inside

        if inside and match and match.group("marker")[0] == marker[0] \
                and len(match.group("marker")) >= len(marker) \
                and not match.group("info").strip():
            buf.append(line)
            segments.append(("".join(buf), True))
            buf, inside, marker = [], False, None
            continue

        if opening:
            if buf:
                segments.append(("".join(buf), False))
                buf = []
            marker, inside = match.group("marker"), True

        buf.append(line)

    if buf:
        segments.append(("".join(buf), inside))
    return segments


def rewrite_text(text: str, lang: str) -> str:
    """Reescreve todos os destinos de link de um documento, em uma unica passada."""

    def _replace(match: re.Match) -> str:
        new_dest = rewrite_destination(match.group("dest"), lang)
        if new_dest is None:
            return match.group(0)
        return f"[{match.group('text')}]({new_dest}{match.group('title') or ''})"

    return "".join(
        segment if inside else INLINE_LINK.sub(_replace, segment)
        for segment, inside in _split_fences(text)
    )


# -- I/O: bytes, nunca texto --------------------------------------------------
# 31 arquivos tem CRLF e 7 nao terminam em newline. Ler/escrever em binario preserva
# os dois; `open(..., 'w')` reescreveria o arquivo inteiro so por causa da
# normalizacao de newline do Python no Windows. E o repo tem `.gitattributes` com
# `eol=lf`, entao o git ESCONDE essa diferenca -- um teste de idempotencia via
# `git diff` passaria com o conteudo corrompido.

def _read(path: Path) -> bytes:
    return path.read_bytes()


def _plan(root: Path):
    """[(path, bytes_atuais, bytes_novos)] apenas para o que muda."""
    changes = []
    for path in sorted(root.glob("*.md")):
        old = _read(path)
        new = rewrite_text(old.decode("utf-8"), lang_from_filename(path.name)).encode("utf-8")
        if new != old:
            changes.append((path, old, new))
    return changes


def _diff_dests(old: bytes, new: bytes):
    """Pares (antes, depois) dos destinos alterados, para o relatorio."""
    pairs = []
    old_text, new_text = old.decode("utf-8"), new.decode("utf-8")
    for before, after in zip(INLINE_LINK.finditer(old_text), INLINE_LINK.finditer(new_text)):
        if before.group("dest") != after.group("dest"):
            pairs.append((before.group("dest"), after.group("dest")))
    return pairs


# -- Modo --source: comparacao DEPOIS da transformacao ------------------------
# Substitui o `diff -rq` cru: um diff cru acusa diferenca em TODO arquivo com link
# cruzado, e nem sequer casa os nomes (.es_es.md na fonte vs .es.md no site).

def _to_site_name(name: str) -> str:
    return re.sub(r"\.(es_es|fr_fr)\.md$",
                  lambda m: ".es.md" if m.group(1) == "es_es" else ".fr.md",
                  name, flags=re.I)


def _source_pairs(source_root: Path):
    """(arquivo_fonte, nome_esperado_no_site). README* da raiz -> readme.*"""
    pairs = []
    docs_dir = source_root / "docs"
    if docs_dir.is_dir():
        for p in sorted(docs_dir.glob("*.md")):
            pairs.append((p, _to_site_name(p.name)))
    for p in sorted(source_root.glob("README*.md")):
        pairs.append((p, _to_site_name("readme" + p.name[len("README"):])))
    return pairs


def _first_diff(a: str, b: str) -> str:
    for i, (ca, cb) in enumerate(zip(a, b)):
        if ca != cb:
            return (f"1a divergencia no byte {i}: "
                    f"site={a[max(0, i - 30):i + 30]!r} fonte={b[max(0, i - 30):i + 30]!r}")
    return f"tamanhos diferentes (site={len(a)}, fonte={len(b)})"


def compare_source(root: Path, source_root: Path):
    """Problemas de conteudo entre fonte e site, ignorando a reescrita de links.

    Aplica a MESMA transformacao dos dois lados antes de comparar, entao funciona
    tanto antes quanto depois de rodar a reescrita -- e reporta apenas divergencia
    real de conteudo.
    """
    problems, checked = [], 0
    for src, site_name in _source_pairs(source_root):
        site_path = root / site_name
        if not site_path.exists():
            problems.append((site_name, "ausente no site"))
            continue
        lang = lang_from_filename(site_name)
        expected = rewrite_text(_read(site_path).decode("utf-8"), lang)
        got = rewrite_text(_read(src).decode("utf-8"), lang)
        checked += 1
        if expected != got:
            problems.append((site_name, _first_diff(expected, got)))
    return checked, problems


# -- Auto-teste ---------------------------------------------------------------

_SLUG_CASES = [
    ("Invocação Direta via CLI", "invocacao-direta-via-cli"),
    ("Invocación Directa por CLI", "invocacion-directa-por-cli"),
    ("Invocation Directe par CLI", "invocation-directe-par-cli"),
    ("Direct CLI Invocation", "direct-cli-invocation"),
    ("Point d'Entrée Alternatif (`gitpr --mcp`)", "point-dentree-alternatif-gitpr-mcp"),
    ("Configuration des Éditeurs", "configuration-des-editeurs"),
    ("Prérequis", "prerequis"),
    ("Dépannage", "depannage"),
    ("Solução de Problemas", "solucao-de-problemas"),
    ("  spaced  out  ", "spaced-out"),
    ("a---b", "a-b"),
    ("Éléments", "elements"),
]

_REWRITE_CASES = [
    ("[a](foo.md)", "pt_br", "[a](/docs/foo?lang=pt_br)"),
    ("[a](foo.md)", "en", "[a](/docs/foo)"),
    ("[a](foo.es_es.md)", "en", "[a](/docs/foo)"),
    ("[a](foo.fr_fr.md)", "fr", "[a](/docs/foo?lang=fr)"),
    ("[a](../README.md)", "en", "[a](/docs/readme)"),
    ("[a](../README.md)", "es", "[a](/docs/readme?lang=es)"),
    ("[a](docs/map-reduce-diff.pt_br.md)", "pt_br", "[a](/docs/map-reduce-diff?lang=pt_br)"),
    ("[a](mcp-integration.md#invocação-direta-via-cli)", "pt_br",
     "[a](/docs/mcp-integration?lang=pt_br#invocacao-direta-via-cli)"),
    ("[a](foo.md#Anchor)", "en", "[a](/docs/foo#anchor)"),
    # orfaos: sufixo de ORIGEM preservado, caminho verbatim
    ("[a](tutorial/install-from-source.es_es.md)", "es",
     "[a](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/tutorial/install-from-source.es_es.md)"),
    ("[a](plans/x.md)", "en",
     "[a](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/x.md)"),
    # intocados
    ("[a](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/x.md)", "en",
     "[a](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/x.md)"),
    ("[a](/docs/foo?lang=fr)", "en", "[a](/docs/foo?lang=fr)"),
    ("[a](#local)", "en", "[a](#local)"),
    ("[a](mailto:x@y.z)", "en", "[a](mailto:x@y.z)"),
    ("[a](../src/core.py)", "en", "[a](../src/core.py)"),
    ("[a](foo.txt)", "en", "[a](foo.txt)"),
    # texto visivel preservado (o caso dos 184 links)
    ("* [pull-request-publication.md](pull-request-publication.md)",
     "pt_br", "* [pull-request-publication.md](/docs/pull-request-publication?lang=pt_br)"),
    ("[documentação principal (README.md)](../README.md)", "pt_br",
     "[documentação principal (README.md)](/docs/readme?lang=pt_br)"),
    ("[a](foo.md \"T\")", "en", "[a](/docs/foo \"T\")"),
]

# Fence: o link cercado tem de ficar intacto; o de fora tem de ser reescrito.
_FENCE_CASES = [
    ("```\n[a](foo.md)\n```\n[b](bar.md)\n", "en",
     "```\n[a](foo.md)\n```\n[b](/docs/bar)\n"),
    ("~~~markdown\n[a](foo.md)\n~~~\n[b](bar.md)\n", "en",
     "~~~markdown\n[a](foo.md)\n~~~\n[b](/docs/bar)\n"),
    ("```\n[a](foo.md)\n", "en", "```\n[a](foo.md)\n"),  # fence nao fechado
    ("[a](foo.md)\n```\n[b](bar.md)\n```\n", "en",
     "[a](/docs/foo)\n```\n[b](bar.md)\n```\n"),
]


def self_test() -> int:
    failures = 0

    def check(label, got, want):
        nonlocal failures
        if got != want:
            failures += 1
            print(f"FAIL {label}: {got!r}, esperado {want!r}")

    for text, want in _SLUG_CASES:
        check(f"slugify({text!r})", slugify(text), want)

    for src, lang, want in _REWRITE_CASES + _FENCE_CASES:
        got = rewrite_text(src, lang)
        check(f"rewrite({src!r}, {lang})", got, want)
        # toda saida ja reescrita tem de ser no-op na 2a passada
        check(f"idempotencia({got!r})", rewrite_text(got, lang), got)

    total = len(_SLUG_CASES) + len(_REWRITE_CASES) + len(_FENCE_CASES)
    print(f"self-test: {total - failures}/{total} ok")
    return 1 if failures else 0


# -- CLI ----------------------------------------------------------------------

def _default_root() -> Path:
    # .claude/skills/sync-docs/rewrite_doc_links.py -> raiz do repo do site
    return Path(__file__).resolve().parents[3] / "public" / "content" / "docs"


def main(argv=None) -> int:
    ap = argparse.ArgumentParser(
        description="Reescreve os links cruzados de public/content/docs para as URLs do site.")
    ap.add_argument("--root", type=Path, default=None,
                    help="diretorio dos .md do site (padrao: <repo>/public/content/docs)")
    ap.add_argument("--check", action="store_true",
                    help="dry-run; exit 1 se houver link por reescrever")
    ap.add_argument("--source", type=Path, default=None,
                    help="raiz do repo gitpr; compara fonte x site DEPOIS da transformacao")
    ap.add_argument("--self-test", action="store_true")
    ap.add_argument("--quiet", action="store_true")
    args = ap.parse_args(argv)

    if args.self_test:
        return self_test()

    root = (args.root or _default_root()).resolve()
    if not root.is_dir():
        print(f"erro: --root nao e um diretorio: {root}", file=sys.stderr)
        return 2

    scoped = sorted(root.glob("*.md"))
    changes = _plan(root)

    if not args.quiet:
        print(f"root: {root}")
        print(f"arquivos varridos: {len(scoped)}")
        print(f"arquivos desatualizados: {len(changes)}")

    rewritten = 0
    for path, old, new in changes:
        pairs = _diff_dests(old, new)
        rewritten += len(pairs)
        if args.quiet:
            continue
        print(f"\n  {path.name}  ({len(pairs)})")
        for before, after in pairs:
            print(f"      {before}")
            print(f"    -> {after}")

    if changes:
        if args.check:
            print(f"\n--check: {len(changes)} arquivo(s) desatualizado(s)", file=sys.stderr)
            return 1
        for path, _old, new in changes:
            path.write_bytes(new)  # binario: preserva CRLF e ausencia de newline final
        print(f"\n{len(changes)} arquivo(s) reescrito(s), {rewritten} link(s)")
    elif not args.quiet:
        print("\nnada a reescrever.")

    if args.source:
        source_root = args.source.resolve()
        if not source_root.is_dir():
            print(f"erro: --source nao e um diretorio: {source_root}", file=sys.stderr)
            return 2
        checked, problems = compare_source(root, source_root)
        print(f"\n--source: {checked} arquivo(s) comparado(s) "
              f"(fonte x site, depois da transformacao)")
        for name, why in problems:
            print(f"  DIVERGE  {name}: {why}")
        if problems:
            return 1

    return 0


if __name__ == "__main__":
    sys.exit(main())
