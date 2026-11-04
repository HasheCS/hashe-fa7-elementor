#!/usr/bin/env python3
"""Build a deterministic, unpublished FA7 installable development ZIP."""
import argparse
import hashlib
import re
from pathlib import Path
from zipfile import ZipFile, ZipInfo, ZIP_DEFLATED

REPO = Path(__file__).resolve().parents[1]
PLUGIN = REPO / "hashe-fa7-elementor"
SLUG = "hashe-fa7-elementor"

def build(destination: Path) -> Path:
    header = (PLUGIN / (SLUG + ".php")).read_text(encoding="utf-8")
    readme = (PLUGIN / "readme.txt").read_text(encoding="utf-8")
    match = re.search(r"(?m)^ \* Version: (\d+\.\d+\.\d+)$", header)
    stable = re.search(r"(?m)^Stable tag: (\d+\.\d+\.\d+)$", readme)
    if not match or not stable or match.group(1) != stable.group(1):
        raise ValueError("Main header and readme version mismatch")
    version = match.group(1)
    if f" * Update URI: https://plugins.hashe.com/{SLUG}" not in header:
        raise ValueError("Expected independent-channel Update URI is missing")
    files = sorted(p for p in PLUGIN.rglob("*") if p.is_file())
    if len(files) < 18:
        raise ValueError("Incomplete plugin package")
    for file in files:
        if file.is_symlink():
            raise ValueError("Symlinks are not allowed in the installable package")
        relative = file.relative_to(PLUGIN)
        if ".." in relative.parts or any(c.startswith(".") for c in relative.parts):
            raise ValueError("Hidden or unsafe package path: " + str(relative))
    destination.mkdir(parents=True, exist_ok=True)
    target = destination / f"{SLUG}-v{version}-development.zip"
    if target.exists():
        raise FileExistsError("Candidate already exists: " + str(target))
    with ZipFile(target, "w", ZIP_DEFLATED, compresslevel=9) as archive:
        for file in files:
            name = (Path(SLUG) / file.relative_to(PLUGIN)).as_posix()
            entry = ZipInfo(name, date_time=(1980, 1, 1, 0, 0, 0))
            entry.create_system = 3
            entry.external_attr = 0o100644 << 16
            entry.compress_type = ZIP_DEFLATED
            archive.writestr(entry, file.read_bytes(), compress_type=ZIP_DEFLATED, compresslevel=9)
    with ZipFile(target) as archive:
        if archive.testzip() is not None or len(archive.namelist()) != len(files):
            raise ValueError("Candidate ZIP integrity failed")
        if archive.read(f"{SLUG}/{SLUG}.php") != (PLUGIN / (SLUG + ".php")).read_bytes():
            raise ValueError("Main plugin file changed during packaging")
    digest = hashlib.sha256(target.read_bytes()).hexdigest()
    (destination / "SHA256SUMS.txt").write_text(f"{digest}  {target.name}\n", encoding="utf-8")
    print(f"DEVELOPMENT_CANDIDATE_VERSION={version}")
    print(f"DEVELOPMENT_ZIP_FILE_COUNT={len(files)}")
    print(f"DEVELOPMENT_ZIP_SHA256={digest}")
    print("RELEASE_APPROVAL=NOT_GRANTED")
    return target

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", required=True, type=Path)
    args = parser.parse_args()
    build(args.output)
