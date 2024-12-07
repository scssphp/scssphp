# Customizing the resolution of importers

Custom resolution of imports can be implemented by registering a custom importer.

All imports are modelled with importers (registering an import path is a shortcut
for registering a `FilesystemImporter`).

Importers must extend `\ScssPhp\ScssPhp\Importer\Importer` and implement the
resolution of the canonical URL and the loading of the content.
