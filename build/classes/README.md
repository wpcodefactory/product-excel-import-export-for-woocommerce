# Updating the vendored Classes/ directory

`Classes/` contains the Composer-managed PhpSpreadsheet dependency tree used by
the plugin. It is fully generated — never edit files under `Classes/` by hand.

This folder (`build/classes/`) holds the build manifest; it is excluded from the
plugin zip via `/build/ export-ignore` in the root `.gitattributes`.

## Update steps

1. Change the version in `composer.json`, e.g.:

   composer require "phpoffice/phpspreadsheet:^3.11" --working-dir=build/classes

   Keep `config.platform.php` in sync with the plugin's minimum PHP version.
   It pins dependency resolution (e.g. ZipStream stays 3.1.1 on PHP 8.1;
   3.1.2 needs 8.2 and 3.2.x needs 8.3).

2. Rebuild the vendor tree:

   php composer.phar update --working-dir=build/classes --no-interaction --prefer-dist

   The packages land in `build/classes/vendor/` (gitignored install cache).

3. Replace the old tree in `Classes/`:
   - Delete: `Classes/autoload.php`, `Classes/composer/`, `Classes/maennchen/`,
     `Classes/markbaker/`, `Classes/phpoffice/`, `Classes/psr/`.
   - Move the same names from `build/classes/vendor/` into `Classes/`.
   - Note: the `composer/pcre` package lives inside `Classes/composer/` — moving
     the whole `composer/` folder carries it along with the autoload files.

4. Verify:
   - Smoke test: load `sample_excel/import_products.xlsx` and write a new xlsx
     with the autoloader from `Classes/autoload.php`.
   - `composer audit --working-dir=build/classes`
   - Check for reintroduced badly named/dev files (e.g.
     `Classes/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Chart/Renderer/PHP Charting Libraries.txt`)
     and remove them (release zip exclusions live in `.gitattributes`).

5. Commit `Classes/` plus the updated `composer.json`/`composer.lock`, and add
   a changelog entry in `readme.txt`.

## Gotchas

- markbaker/complex|matrix dists contain no `functions/`/`operations/` dirs;
  PhpSpreadsheet uses only their class APIs (`Complex\Functions::*`,
  `Complex->add()`, `Matrix\Functions::*`). Do not re-add the old global-function
  files or a `files` autoload entry.
- `composer.lock` is export-ignored from the plugin zip, but commit it here for
  reproducible builds.
