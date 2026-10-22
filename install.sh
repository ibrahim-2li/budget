#!/bin/sh
# Adams installer. Its SHA-256 is published beside it at https://getadams.com/api/install.sha256: check it before you run this.
# It downloads the signed Lite bundle, verifies the signature with openssl before it unpacks anything, shows the licence, and installs only after you type yes.
set -e
BASE="https://getadams.com/api"
case "$ADAMS_API" in http://127.0.0.1:*|http://localhost:*) BASE="$ADAMS_API" ;; esac
PUB="04c03abca232461223e10d7c219f45234549f6efccda23dbb683bdfc5387d9697d4f984a272311dc03e35e8e9ac1c350bdabea49b6986cd741547b134ed180c377"
DEST="$ADAMS_HOME"
[ -n "$DEST" ] || DEST="$HOME/.local/share/adams"
TTY="$ADAMS_TTY"
[ -n "$TTY" ] || TTY=/dev/tty
for t in curl openssl python3 tar; do
  command -v "$t" >/dev/null 2>&1 || { echo "Adams needs $t and could not find it." >&2; exit 1; }
done
if [ -e "$DEST/ADAMS_BUNDLE.json" ]; then echo "Adams is already installed in $DEST. Run: adams update"; exit 0; fi
T="$(mktemp -d)"
trap 'rm -rf "$T"' EXIT
curl -fsSL "$BASE/bundles/manifest" -o "$T/manifest.json"
V="$(python3 -c 'import json,re,sys; v=json.load(open(sys.argv[1]))["lite"]["version"]; assert re.fullmatch(r"[0-9]+[.][0-9]+[.][0-9]+(-[0-9A-Za-z.]+)?", v); print(v)' "$T/manifest.json")"
curl -fsSL "$BASE/bundles/lite/$V" -o "$T/bundle.tgz"
curl -fsSL "$BASE/bundles/lite/$V.sig" -o "$T/bundle.sig"
python3 -c 'import base64,sys; b=base64.b64encode(bytes.fromhex("3059301306072a8648ce3d020106082a8648ce3d030107034200")+bytes.fromhex(sys.argv[1])).decode(); print("-----BEGIN PUBLIC KEY-----"); print("\n".join(b[i:i+64] for i in range(0,len(b),64))); print("-----END PUBLIC KEY-----")' "$PUB" > "$T/key.pem"
if ! ( printf 'adams-bundle-v1\0'; cat "$T/bundle.tgz" ) | openssl dgst -sha256 -verify "$T/key.pem" -signature "$T/bundle.sig" >/dev/null 2>&1; then
  echo "The signature of the Adams bundle does not match. Nothing was installed." >&2
  exit 1
fi
tar -xzOf "$T/bundle.tgz" LICENSE
printf '\nType yes to accept this licence and install Adams %s: ' "$V"
ANSWER=""
read ANSWER < "$TTY" || true
[ "$ANSWER" = "yes" ] || { echo "Not installed."; exit 1; }
mkdir -p "$T/unpack"
tar -xzf "$T/bundle.tgz" -C "$T/unpack"
mkdir -p "$(dirname "$DEST")"
mv "$T/unpack" "$DEST"
echo "Adams $V is in $DEST"
if command -v claude >/dev/null 2>&1; then
  claude plugin marketplace add "$DEST" && claude plugin install adams@adams || echo "Claude Code did not take the plugin. In Claude Code run: /plugin marketplace add $DEST"
else
  echo "Claude Code was not found. Later, in Claude Code run: /plugin marketplace add $DEST"
fi
python3 "$DEST/bin/adams" install || echo "adams install did not finish. Run it again after fixing the message above."
python3 "$DEST/bin/adams" sync || echo "Copilot instructions were not written. Run: adams sync"
echo "Start a new session. Pro files arrive after: adams activate YOUR-KEY"
