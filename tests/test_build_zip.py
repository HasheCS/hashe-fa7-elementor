"""Deterministic WordPress-installable package contract tests."""
import importlib.util
import tempfile
import unittest
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SPEC = importlib.util.spec_from_file_location("hashe_fa7_zip", ROOT / "tools" / "build_zip.py")
builder = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(builder)


class PackageTests(unittest.TestCase):
    def test_installer_root_assets_version_and_reproducibility(self):
        with tempfile.TemporaryDirectory() as directory:
            path_a = Path(directory) / "first.zip"
            path_b = Path(directory) / "second.zip"
            a = builder.build(path_a)
            b = builder.build(path_b)
            self.assertEqual(a["version"], b["version"])
            self.assertEqual(a["sha256"], b["sha256"])
            self.assertGreater(a["files"], 20)
            with zipfile.ZipFile(path_a) as archive:
                self.assertIsNone(archive.testzip())
                names = archive.namelist()
                self.assertTrue(all(p.startswith("hashe-fa7-elementor/") for p in names))
                self.assertTrue(any(p.endswith("/hashe-fa7-elementor.php") for p in names))
                self.assertTrue(any(p.endswith("/readme.txt") for p in names))
                self.assertFalse(any(p.startswith("__MACOSX/") for p in names))
                self.assertFalse(any("/.git/" in p for p in names))
                self.assertFalse(any("pro" in Path(p).name.lower() and p.endswith(".woff2") for p in names))

    def test_release_gate_is_separate_from_archive_integrity(self):
        self.assertTrue((ROOT / "tools" / "release_gate.py").is_file())
        self.assertTrue((ROOT / "qa" / "acceptance-evidence.json").is_file())


if __name__ == "__main__":
    unittest.main()
