#!/usr/bin/env python3
"""Fail-closed release-approval gate for the Hashe FA7 Elementor blueprint.

The gate never writes to GitHub, WordPress or customer sites. Evidence must
be independently produced before a status may be changed to 'pass'.
An external limitation is release-compatible ONLY when the owner signs it off.
"""
import argparse
import json
import re
import sys
from pathlib import Path

EXPECTED = {"T%02d" % i for i in range(1, 25)}
STATUSES = {"pending", "pass", "external-limitation"}

def evaluate(path):
    obj = json.loads(Path(path).read_text(encoding="utf-8"))
    checks = obj.get("checks")
    if not isinstance(checks, list):
        raise ValueError("Acceptance manifest requires checks array")
    ids = [r.get("id") for r in checks if isinstance(r, dict)]
    if set(ids) != EXPECTED or len(ids) != 24:
        raise ValueError("Exactly T01-T24 with unique identifiers required")
    blocked = []
    for row in checks:
        ident = row["id"]
        status = row.get("status")
        evidence = row.get("evidence")
        url = row.get("url")
        if status not in STATUSES:
            raise ValueError("%s has unrecognized status" % ident)
        if status == "pending":
            blocked.append("%s pending" % ident)
        elif status == "pass":
            if not isinstance(evidence, str) or len(evidence.strip()) < 20 or not isinstance(url, str) or not url.startswith("https://"):
                blocked.append("%s missing independently inspectable pass evidence" % ident)
        elif status == "external-limitation":
            approved = row.get("approved_external_limitation") is True
            signed = row.get("signoff") == "Mamoon Rashid"
            supported = isinstance(evidence, str) and len(evidence.strip()) >= 40
            cited = isinstance(url, str) and url.startswith("https://")
            if not (approved and signed and supported and cited):
                blocked.append("%s external limitation missing written owner sign-off or evidence" % ident)
    return blocked

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("manifest", nargs="?", default="qa/acceptance-evidence.json")
    args = parser.parse_args()
    try:
        blocked = evaluate(args.manifest)
    except (ValueError, OSError, json.JSONDecodeError) as exc:
        print("RELEASE GATE: ERROR -", exc)
        return 2
    if blocked:
        print("RELEASE GATE: BLOCKED (%d remaining requirements)" % len(blocked))
        for item in blocked:
            print(" -", item)
        return 1
    print("RELEASE GATE: ACCEPTANCE RECORDED. Independent artifact and install verification still required.")
    return 0

if __name__ == "__main__":
    sys.exit(main())
