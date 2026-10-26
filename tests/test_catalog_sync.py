"""Offline regression tests for the candidate-only public catalog builder."""
import importlib.util
import json
import tempfile
import unittest
from pathlib import Path

SOURCE = Path(__file__).resolve().parents[1] / "tools" / "sync_fontawesome_catalogs.py"
spec = importlib.util.spec_from_file_location("fa_catalog_sync", SOURCE)
sync = importlib.util.module_from_spec(spec)
spec.loader.exec_module(sync)


def fixture_fetch(path):
    if path == "/releases":
        return {"releases": [{"version": "7.3.1"}, {"version": "7.4.0"}, {"version": "8.0.0"}]}
    if path == "/releases/7.4.0/family-styles":
        return {"familyStyles": [
            {"family": "classic", "style": "solid", "prefix": "fas", "shorthand": "solid"},
            {"family": "classic", "style": "regular", "prefix": "far", "shorthand": "regular"},
            {"family": "classic", "style": "brands", "prefix": "fab", "shorthand": "brands"},
            {"family": "notdog-duo", "style": "solid", "prefix": "fands", "shorthand": "notdog-duo-solid"},
        ]}
    if path == "/releases/7.4.0/icons-minimal?page=1&page_size=500":
        return {"totalPageCount": 1, "icons": [
            {"id": "house", "familyStylesByLicense": {"free": [{"prefix": "fas"}], "pro": [{"prefix": "fas"}]}},
            {"id": "address-book", "familyStylesByLicense": {"free": [{"prefix": "far"}], "pro": [{"prefix": "far"}]}},
            {"id": "github", "familyStylesByLicense": {"free": [{"prefix": "fab"}], "pro": [{"prefix": "fab"}]}},
            {"id": "notdog-house", "familyStylesByLicense": {"free": [], "pro": [{"prefix": "fands"}]}},
        ]}
    raise AssertionError("Unexpected request: " + path)


class CatalogSyncTests(unittest.TestCase):
    def test_new_family_is_discovered_without_hardcoding(self):
        self.assertEqual(sync.latest_seven(fixture_fetch), "7.4.0")
        files = sync.build_catalog("7.4.0", fixture_fetch)
        self.assertEqual(files["fands.json"]["icons"], ["notdog-house"])
        self.assertEqual(files["free-solid.json"]["icons"], ["house"])
        self.assertEqual(files["manifest.json"]["family_styles"]["fands"]["family"], "notdog-duo")
        self.assertTrue(files["manifest.json"]["candidate"])

    def test_candidate_written_separately(self):
        files = sync.build_catalog("7.4.0", fixture_fetch)
        with tempfile.TemporaryDirectory() as base:
            destination = Path(base) / "candidate"
            sync.save_files(destination, files)
            self.assertTrue((destination / "fands.json").is_file())
            loaded = json.loads((destination / "manifest.json").read_text(encoding="utf-8"))
            self.assertEqual(loaded["version"], "7.4.0")
            self.assertTrue(loaded["candidate"])

    def test_rejects_missing_or_mismatched_data(self):
        with self.assertRaises(ValueError):
            sync.build_catalog("8.0.0", fixture_fetch)
        with self.assertRaises(ValueError):
            sync.build_catalog("../7.4.0", fixture_fetch)
        def corrupt(path):
            obj = fixture_fetch(path)
            if "/icons-minimal?" in path:
                obj["icons"][0]["familyStylesByLicense"]["pro"] = [{"prefix": "unknown"}]
            return obj
        with self.assertRaises(ValueError):
            sync.build_catalog("7.4.0", corrupt)


if __name__ == "__main__":
    unittest.main()
