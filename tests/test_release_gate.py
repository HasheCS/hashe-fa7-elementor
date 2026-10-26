"""Contract tests: incomplete work cannot be labeled a complete release."""
import json
import tempfile
import unittest
from pathlib import Path
import importlib.util

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location("fa7_gate", ROOT / "tools" / "release_gate.py")
gate = importlib.util.module_from_spec(spec)
spec.loader.exec_module(gate)

class GateTests(unittest.TestCase):
    def test_real_manifest_is_blocked_until_complete(self):
        pending = gate.evaluate(ROOT / "qa" / "acceptance-evidence.json")
        self.assertTrue(pending)
        self.assertTrue(any("T09" in line for line in pending))

    def test_all_passed_with_evidence(self):
        checks = [
            {"id": "T%02d" % i, "status": "pass", "evidence": "Verified browser and frontend test with preserved output", "url": "https://example.test/qa"}
            for i in range(1,25)
        ]
        with tempfile.TemporaryDirectory() as tmp:
            file = Path(tmp) / "acceptance.json"
            file.write_text(json.dumps({"checks":checks}), encoding="utf-8")
            self.assertEqual([], gate.evaluate(file))
            checks[4]["status"] = "pending"
            file.write_text(json.dumps({"checks":checks}), encoding="utf-8")
            self.assertTrue(gate.evaluate(file))

    def test_external_limit_requires_signoff_and_evidence(self):
        checks = [
            {"id": "T%02d" % i, "status": "pass", "evidence": "Verified browser and frontend test with preserved output", "url": "https://example.test/qa"}
            for i in range(1,25)
        ]
        checks[3].update({"status": "external-limitation", "approved_external_limitation": False, "signoff": ""})
        with tempfile.TemporaryDirectory() as tmp:
            file = Path(tmp) / "acceptance.json"
            file.write_text(json.dumps({"checks":checks}), encoding="utf-8")
            self.assertTrue(gate.evaluate(file))
            checks[3].update({"approved_external_limitation": True, "signoff": "Mamoon Rashid"})
            file.write_text(json.dumps({"checks":checks}), encoding="utf-8")
            self.assertEqual([], gate.evaluate(file))

    def test_duplicate_or_missing_ids_are_rejected(self):
        with tempfile.TemporaryDirectory() as tmp:
            file = Path(tmp) / "bad.json"
            file.write_text(json.dumps({"checks":[{"id":"T01","status":"pass"}] * 24}), encoding="utf-8")
            with self.assertRaises(ValueError):
                gate.evaluate(file)

if __name__ == "__main__":
    unittest.main()
