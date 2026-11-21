from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED

root = Path(__file__).resolve().parents[1]
files = [
    "napi-idezet-elementor.php", "quote-storage.php", "admin.php",
    "admin-view.php", "widget.php", "admin.css", "widget.css",
    "readme.txt", "LICENSE",
]
output = root / "dist" / "napi-idezet-elementor.zip"
output.parent.mkdir(exist_ok=True)
with ZipFile(output, "w", ZIP_DEFLATED) as archive:
    for name in files:
        archive.write(root / name, "napi-idezet-elementor/" + name)
    for path in sorted((root / "languages").glob("*")):
        if path.suffix in {".pot", ".po", ".mo"}:
            archive.write(path, "napi-idezet-elementor/languages/" + path.name)
print(output)
