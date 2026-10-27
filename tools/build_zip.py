#!/usr/bin/env python3
"""Create an installable, deterministic WordPress plugin ZIP from reviewed source.

This creates an *internal QA artifact*, not a public release authorization.
Public distribution remains guarded by the separate T01-T24 acceptance gate.
"""
import argparse
import hashlib
import re
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "hashe-fa7-elementor"
NAME = "hashe-fa7-elementor"
REQUIRED = (
    "hashe-fa7-elementor.php",
    "readme.txt",
    "includes/kit-bridge.php",
    "assets/js/editor-callback-guard.js",
    "assets/css/fontawesome.min.css",
    "assets/css/solid.min.css",
    "assets/css/regular.min.css",
    "assets/css/brands.min.css",
    "assets/webfonts/fa-solid-900.woff2",
    "assets/webfonts/fa-regular-400.woff2",
    "assets/webfonts/fa-brands-400.woff2",
    "FONT-AWESOME-LICENSE.txt",
)
ALLOWED_SUFFIXES = {".php", ".js", ".css", ".woff2", ".json", ".txt", ".md"}
HEADER_RE = re.compile(r"^\s*\*\s*Version:\s*(\S+)", re.MULTILINE)
TAG_RE = re.compile(r"^Stable tag:\s*(\S+)", re.MULTILINE)


def build(output):
    if not PLUGIN.is_dir():
        raise ValueError("Required WordPress plugin directory missing")
    for rel in REQUIRED:
        if not (PLUGIN / rel).is_file():
            raise ValueError("Missing required plugin asset: " + rel)
    entry = (PLUGIN / "hashe-fa7-elementor.php").read_text(encoding="utf-8")
    readme = (PLUGIN / "readme.txt").read_text(encoding="utf-8")
    header = HEADER_RE.search(entry)
    tag = TAG_RE.search(readme)
    if not header or not tag or header.group(1) != tag.group(1):
        raise ValueError("Plugin header and readme stable tag disagree")
    version = header.group(1)
    if not re.fullmatch(r"[0-9]+\.[0-9]+\.[0-9]+(?:-[a-zA-Z0-9.-]+)?", version):
        raise ValueError("Unexpected WordPress plugin version")

    names = []
    for item in PLUGIN.rglob("*"):
        if not item.is_file():
            continue
        rel = item.relative_to(PLUGIN)
        if any(part.startswith(".") for part in rel.parts):
            raise ValueError("Hidden files are not allowed in installable ZIP")
        if item.suffix.lower() not in ALLOWED_SUFFIXES:
            raise ValueError("Unexpected asset extension: " + str(rel))
        if "pro" in item.name.lower() and item.suffix.lower() == ".woff2":
            raise ValueError("Paid Pro webfont files must never be packaged")
        names.append(item)
    names.sort(key=lambda p: p.relative_to(PLUGIN).as_posix())
    output.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(output, mode="w", compression=zipfile.ZIP_DEFLATED,
                         compresslevel=9) as archive:
        for file in names:
            arcname = NAME + "/" + file.relative_to(PLUGIN).as_posix()
            info = zipfile.ZipInfo(arcname, date_time=(1980, 1, 1, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o644 << 16
            archive.writestr(info, file.read_bytes(), compress_type=zipfile.ZIP_DEFLATED)
    with zipfile.ZipFile(output) as archive:
        bad = archive.testzip()
        if bad is not None:
            raise ValueError("ZIP integrity check failed: " + bad)
        entries = set(archive.namelist())
        for rel in REQUIRED:
            if NAME + "/" + rel not in entries:
                raise ValueError("Packaged ZIP omitted " + rel)
        packaged_header = archive.read(NAME + "/hashe-fa7-elementor.php").decode()
        if HEADER_RE.search(packaged_header).group(1) != version:
            raise ValueError("ZIP plugin version differs")
    digest = hashlib.sha256(output.read_bytes()).hexdigest()
    return {"version": version, "sha256": digest, "files": len(names), "bytes": output.stat().st_size}


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("output", type=Path)
    options = parser.parse_args()
    result = build(options.output)
    print("QA ZIP:", options.output, "version:", result["version"],
          "files:", result["files"], "sha256:", result["sha256"])
    print("UNRELEASED: package validation does not authorize distribution or installation")


if __name__ == "__main__":
    sys.exit(main())
