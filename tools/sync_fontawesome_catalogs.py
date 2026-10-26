#!/usr/bin/env python3
"""Build a CANDIDATE Font Awesome 7 catalog from public REST metadata.

No Font Awesome account, credentials, commercial assets, or Kit-specific data
are used. This is an offline/release engineering helper, NOT the site's Kit
selection mechanism and NOT an automatic plugin update mechanism.

Do not replace the production plugin data automatically: Free CSS/webfonts must
match the target release, and Kit-specific filtering must be separately proven.
"""
import argparse
import json
import os
import re
import tempfile
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

API = "https://api.fontawesome.com"
VERSION_RE = re.compile(r"^7\.[0-9]+\.[0-9]+$")
PREFIX_RE = re.compile(r"^[a-z0-9]+$")
ICON_RE = re.compile(r"^[a-z0-9-]+$")


def fetch_json(path):
    if not path.startswith("/releases"):
        raise ValueError("Only the Font Awesome public releases API is permitted")
    req = Request(API + path, headers={"Accept": "application/json", "User-Agent": "Hashe-FA7-Catalog-Builder/0.1"})
    try:
        with urlopen(req, timeout=30) as response:
            if response.status != 200:
                raise ValueError("Unexpected HTTP status %s" % response.status)
            return json.load(response)
    except (HTTPError, URLError) as error:
        raise RuntimeError("Font Awesome API request failed: %s" % path) from error


def latest_seven(fetch):
    payload = fetch("/releases")
    releases = payload.get("releases") if isinstance(payload, dict) else None
    if not isinstance(releases, list):
        raise ValueError("Missing releases array")
    versions = [
        item["version"] for item in releases
        if isinstance(item, dict)
        and isinstance(item.get("version"), str)
        and VERSION_RE.fullmatch(item["version"])
    ]
    if not versions:
        raise ValueError("No Font Awesome 7 releases found")
    return max(versions, key=lambda value: tuple(map(int, value.split("."))))


def build_catalog(version, fetch):
    if not VERSION_RE.fullmatch(version):
        raise ValueError("This helper supports Font Awesome 7 patch releases only")
    family_data = fetch("/releases/%s/family-styles" % version)
    family_styles = family_data.get("familyStyles") if isinstance(family_data, dict) else None
    if not isinstance(family_styles, list) or not family_styles:
        raise ValueError("Missing family styles")
    families = {}
    for item in family_styles:
        if not isinstance(item, dict):
            raise ValueError("Invalid family style")
        prefix, family, style = item.get("prefix"), item.get("family"), item.get("style")
        if (not isinstance(prefix, str) or not PREFIX_RE.fullmatch(prefix)
                or not isinstance(family, str) or not isinstance(style, str)):
            raise ValueError("Malformed family-style metadata")
        if prefix in families:
            raise ValueError("Duplicate family-style prefix")
        families[prefix] = {"family": family, "style": style, "shorthand": item.get("shorthand", "")}

    names = {prefix: set() for prefix in families}
    free_names = {"fas": set(), "far": set(), "fab": set()}
    page, total_pages, found_icons = 1, None, set()
    while True:
        if page > 250:
            raise ValueError("API pagination exceeds safety limit")
        data = fetch("/releases/%s/icons-minimal?page=%s&page_size=500" % (version, page))
        if not isinstance(data, dict) or not isinstance(data.get("icons"), list):
            raise ValueError("Malformed icon page")
        count = data.get("totalPageCount")
        if not isinstance(count, int) or count < 1 or count > 250:
            raise ValueError("Invalid page count")
        if total_pages is None:
            total_pages = count
        elif count != total_pages:
            raise ValueError("Page count changed during download")
        if not data["icons"]:
            raise ValueError("Empty icon page")
        for icon in data["icons"]:
            if not isinstance(icon, dict):
                raise ValueError("Invalid icon")
            name = icon.get("id")
            if not isinstance(name, str) or not ICON_RE.fullmatch(name):
                raise ValueError("Invalid icon ID")
            found_icons.add(name)
            by_license = icon.get("familyStylesByLicense")
            if not isinstance(by_license, dict):
                raise ValueError("Missing license metadata")
            for license_name in ("free", "pro"):
                members = by_license.get(license_name)
                if not isinstance(members, list):
                    raise ValueError("Missing family-style license list")
                for entry in members:
                    prefix = entry.get("prefix") if isinstance(entry, dict) else None
                    if prefix not in names:
                        raise ValueError("Unknown family-style prefix")
                    names[prefix].add(name)
                    if license_name == "free" and prefix in free_names:
                        free_names[prefix].add(name)
        if page == total_pages:
            break
        page += 1

    files = {}
    for prefix, values in names.items():
        if values:
            files[prefix + ".json"] = {"icons": sorted(values)}
    for prefix, target in (("fas", "free-solid"), ("far", "free-regular"), ("fab", "free-brands")):
        if not free_names[prefix]:
            raise ValueError("Free family catalog missing")
        files[target + ".json"] = {"icons": sorted(free_names[prefix])}
    files["manifest.json"] = {
        "candidate": True,
        "version": version,
        "source": API + "/releases/" + version + "/icons-minimal",
        "distinct_icons": len(found_icons),
        "styles": {key: len(values) for key, values in sorted(names.items()) if values},
        "family_styles": {key: families[key] for key in sorted(families)},
        "warning": "Candidate metadata only. Requires Kit filtering and matching Free assets before activation.",
    }
    return files


def save_files(output, files):
    output.mkdir(parents=True, exist_ok=True)
    staged = []
    try:
        for name, data in sorted(files.items()):
            if not re.fullmatch(r"[a-z0-9-]+\.json", name):
                raise ValueError("Invalid output filename")
            handle, tempname = tempfile.mkstemp(prefix=".fa7-", dir=str(output), text=True)
            staged.append((tempname, output / name))
            with os.fdopen(handle, "w", encoding="utf-8") as file:
                json.dump(data, file, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
                file.write("\n")
        for source, destination in staged:
            os.replace(source, destination)
    finally:
        for source, _ in staged:
            if os.path.exists(source):
                os.unlink(source)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--version", help="7.x.y release (default: latest 7.x.y)")
    parser.add_argument("--output-dir", required=True, type=Path, help="Candidate output directory, separate from installed assets")
    args = parser.parse_args()
    version = args.version or latest_seven(fetch_json)
    files = build_catalog(version, fetch_json)
    save_files(args.output_dir, files)
    print("Candidate catalog built: %s | %s files" % (version, len(files)))
    print("Not activated. No Kit-specific filtering or font asset update was performed.")


if __name__ == "__main__":
    main()
