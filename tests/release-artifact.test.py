#!/usr/bin/env python3
"""Read-only release metadata and portable WordPress ZIP packaging checks."""
from pathlib import Path
import re
import tempfile
import zipfile

repo = Path(__file__).resolve().parent.parent
plugin = repo / "hashe-fa7-elementor"
main = (plugin / "hashe-fa7-elementor.php").read_text(encoding="utf-8")
readme = (plugin / "readme.txt").read_text(encoding="utf-8")
vh = re.search(r"(?m)^ \* Version: ([0-9]+\.[0-9]+\.[0-9]+)$", main)
st = re.search(r"(?m)^Stable tag: ([0-9]+\.[0-9]+\.[0-9]+)$", readme)
assert vh and st and vh.group(1) == st.group(1), "Plugin and readme versions must agree"
version = vh.group(1)
assert re.search(r"(?m)^= " + re.escape(version) + r" =$", readme), "Missing release changelog"
assert main.startswith("<?php"), "WordPress main plugin file missing PHP entry"
assert "Update URI: https://plugins.hashe.com/hashe-fa7-elementor" in main
installation = readme.split("== Installation ==", 1)[1].split("== Privacy and optional reporting ==", 1)[0]
steps = re.findall(r"(?m)^([1-4])\. (.+)$", installation)
assert [int(n) for n, _ in steps] == [1, 2, 3, 4], "Incomplete numbered installation instructions"
assert "GitHub release" in steps[0][1] and "ZIP" in steps[0][1], "Missing correct ZIP download guidance"
assert "back up" not in installation.lower() and "backup" not in installation.lower(), "Installation must not suggest full-site backups"
github_readme = (repo / "README.md").read_text(encoding="utf-8")
github_installation = github_readme.split("## Installation", 1)[1].split("## Verification", 1)[0]
github_steps = re.findall(r"(?m)^([1-4])\. (.+)$", github_installation)
assert [int(n) for n, _ in github_steps] == [1, 2, 3, 4], "GitHub instructions must be simple and numbered"
assert "backup" not in github_installation.lower() and "back up" not in github_installation.lower(), "GitHub instructions must not suggest full-site backups"
assert "+'s automatic" not in readme, "Damaged old installation instruction"

files = sorted(p for p in plugin.rglob("*") if p.is_file())
assert len(files) >= 18, "Unexpectedly incomplete release tree"
needed = [
    "hashe-fa7-elementor.php", "readme.txt", "FONT-AWESOME-LICENSE.txt",
    "includes/custom-kit-icons.php", "includes/hashe-core/v1/update-client.php",
    "assets/css/hashe-fa7-free-scoped.css", "assets/webfonts/fa-solid-900.woff2"
]
for relative in needed:
    assert (plugin / relative).is_file(), "Missing release file: " + relative

with tempfile.TemporaryDirectory() as d:
    archive = Path(d) / ("hashe-fa7-elementor-v" + version + ".zip")
    with zipfile.ZipFile(archive, "w", compression=zipfile.ZIP_DEFLATED) as z:
        for f in files:
            path = "hashe-fa7-elementor/" + f.relative_to(plugin).as_posix()
            assert ".." not in Path(path).parts
            assert not any(part.startswith(".") for part in Path(path).parts)
            assert "tests" not in Path(path).parts
            z.write(f, arcname=path)
    with zipfile.ZipFile(archive) as z:
        entries = z.namelist()
        assert len(entries) == len(files) and z.testzip() is None
        assert all(x.startswith("hashe-fa7-elementor/") and "\\" not in x for x in entries)
        assert z.read("hashe-fa7-elementor/readme.txt") == (plugin / "readme.txt").read_bytes()

print("RELEASE_README_AND_PORTABLE_ZIP_TEST=PASS")
print("VERSION=" + version)
print("PACKAGE_FILE_COUNT=" + str(len(files)))
