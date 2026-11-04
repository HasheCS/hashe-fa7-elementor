#!/usr/bin/env python3
"""Deterministic development ZIPs for independent Hashe and WordPress.org channels.

No release, remote upload, or site mutation. The same slug is retained deliberately;
the two channel editions are alternatives, not simultaneously installable plugins.
"""
import argparse
import hashlib
import re
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
PLUGIN = ROOT / "hashe-fa7-elementor"
SLUG = "hashe-fa7-elementor"

def exactly_once(content, original, replacement, name):
    if content.count(original) != 1:
        raise ValueError(f"{name}: expected one occurrence, found {content.count(original)}")
    return content.replace(original, replacement, 1)

def channel_payloads():
    main = (PLUGIN / (SLUG + ".php")).read_text(encoding="utf-8")
    readme = (PLUGIN / "readme.txt").read_text(encoding="utf-8")
    header = re.search(r"(?m)^ \* Version: (\d+\.\d+\.\d+)$", main)
    stable = re.search(r"(?m)^Stable tag: (\d+\.\d+\.\d+)$", readme)
    if not header or not stable or header.group(1) != stable.group(1):
        raise ValueError("Main header/readme version mismatch")
    if " * Update URI: https://plugins.hashe.com/" + SLUG not in main:
        raise ValueError("Independent update URI missing")
    version = header.group(1)
    independent = {}
    for f in sorted(PLUGIN.rglob("*")):
        if not f.is_file():
            continue
        if f.is_symlink():
            raise ValueError("Symlinks cannot be packaged")
        relative = f.relative_to(PLUGIN)
        if ".." in relative.parts or any(p.startswith(".") for p in relative.parts):
            raise ValueError("Unsafe or hidden package path")
        independent[(Path(SLUG) / relative).as_posix()] = f.read_bytes()
    if len(independent) != 20:
        raise ValueError(f"Expected existing 20-file independent baseline; got {len(independent)}")
    wporg = dict(independent)
    wporg_main = main
    wporg_main = exactly_once(wporg_main,
        " * Update URI: https://plugins.hashe.com/" + SLUG + "\n", "", "Update URI header")
    wporg_main = exactly_once(wporg_main,
        "require_once __DIR__ . '/includes/hashe-core/v1/update-client.php';\n", "", "Updater import")
    updater_bootstrap = (
        "( new \\Hashe\\FA7\\PluginTools\\V1\\UpdateClient(\n"
        "    'hashe-fa7-elementor',\n"
        "    plugin_basename( __FILE__ ),\n"
        "    'https://plugins.hashe.com/hashe-fa7-elementor',\n"
        "    'https://plugins.hashe.com/api/v1/updates/hashe-fa7-elementor'\n"
        ") )->register();\n"
    )
    wporg_main = exactly_once(wporg_main, updater_bootstrap, "", "Updater bootstrap")
    wporg[f"{SLUG}/{SLUG}.php"] = wporg_main.encode("utf-8")
    wporg.pop(f"{SLUG}/includes/hashe-core/v1/update-client.php")
    org_readme = readme
    org_readme = exactly_once(org_readme,
        "1. Download the installable plugin ZIP from the latest GitHub release, not GitHub's automatically generated source ZIP.",
        "1. Install the directory edition from WordPress.org once approved by its plugin review team.",
        "WordPress.org installation instructions")
    wporg[f"{SLUG}/readme.txt"] = org_readme.encode("utf-8")
    if " * Update URI:" in wporg_main or "update_plugins_plugins.hashe.com" in wporg_main:
        raise ValueError("WordPress.org build contains third-party update hooks")
    if "\\Hashe\\FA7\\PluginTools\\V1\\UpdateClient" in wporg_main:
        raise ValueError("WordPress.org updater bootstrap was not removed")
    if "includes/hashe-core/v1/consent-client.php" not in wporg_main:
        raise ValueError("WordPress.org consent capability must be retained")
    if "telemetry/report" not in (PLUGIN / "includes/hashe-core/v1/consent-client.php").read_text(encoding="utf-8"):
        raise ValueError("Expected mandatory optional consent client missing")
    return version, {"independent": independent, "wordpress-org": wporg}

def build(destination):
    version, payloads = channel_payloads()
    destination.mkdir(parents=True, exist_ok=True)
    sums = []
    for channel, payload in payloads.items():
        suffix = "development" if channel == "independent" else "wporg-development"
        target = destination / f"{SLUG}-v{version}-{suffix}.zip"
        if target.exists():
            raise FileExistsError("Refusing to overwrite an existing candidate")
        with ZipFile(target, "w", ZIP_DEFLATED, compresslevel=9) as archive:
            for path, data in sorted(payload.items()):
                item = ZipInfo(path, date_time=(1980, 1, 1, 0, 0, 0))
                item.create_system = 3
                item.external_attr = 0o100644 << 16
                item.compress_type = ZIP_DEFLATED
                archive.writestr(item, data, compress_type=ZIP_DEFLATED, compresslevel=9)
        with ZipFile(target) as archive:
            if archive.testzip() is not None or archive.namelist() != sorted(payload):
                raise ValueError(f"Corrupt or unsorted ZIP for {channel}")
            if archive.read(f"{SLUG}/{SLUG}.php") != payload[f"{SLUG}/{SLUG}.php"]:
                raise ValueError("Main PHP entry mismatch")
        digest = hashlib.sha256(target.read_bytes()).hexdigest()
        print(f"CHANNEL={channel} VERSION={version} FILES={len(payload)} SHA256={digest}")
        sums.append(f"{digest}  {target.name}")
    (destination / "SHA256SUMS.txt").write_text("\n".join(sums) + "\n", encoding="utf-8")
    print("PUBLICATION=NOT_AUTHORIZED_UNTIL_ACCEPTANCE")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", required=True, type=Path)
    args = parser.parse_args()
    build(args.output)
