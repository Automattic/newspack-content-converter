# Newspack Content Converter (NCC)

Browser-based WordPress plugin that mass-converts classic editor HTML to Gutenberg blocks. Conversion happens client-side via the Block Editor's `rawHandler` JS API; PHP orchestrates batches and applies pre/post-conversion patches.

**Sibling repos:** NCCM = newspack-custom-content-migrator (CLI migrators), NMT = newspack-migration-tools (shared NMT lib). NCC does NOT depend on NMT.

## Architecture

### Conversion Flow

1. Admin clicks "Run Conversion" in WP dashboard
2. PHP prepares batches of post IDs (hardcoded batch size: 100)
3. Browser opens Block Editor page, React fetches a batch via REST API
4. For each post: fetch filtered HTML → insert as Classic block → call `rawHandler()` to convert to Gutenberg blocks → POST converted blocks back
5. PHP applies post-conversion patchers, backs up original content as postmeta, saves
6. Page reloads for next batch until all batches complete

### Core Classes

- **`Converter`** (`lib/class-converter.php`) — Entry point. Registers admin pages, enqueues scripts, registers patcher filter callbacks, hooks REST routes.
- **`ConverterController`** (`lib/class-convertercontroller.php`) — `WP_REST_Controller` subclass. 12 REST endpoints. Permission: `edit_others_posts`.
- **`ConversionProcessor`** (`lib/class-conversionprocessor.php`) — Batch queue management, post content retrieval with pre-conversion filters, post updating with post-conversion filters, backup/restore via postmeta (`ncc_post_content_original`).

### Constructor Injection

The plugin uses constructor injection (not singletons):
```php
// newspack-content-converter.php
new Converter( new ConverterController( new ConversionProcessor() ) );
```

## Content Patcher System

Patchers fix content that Gutenberg's `rawHandler` loses or mangles. Two-tier filter chain:

### Pre-Conversion Patchers

Hook: `ncc_filter_html_before_conversion` — receives `( string $html, int $post_id )`, returns patched HTML.

Registered order in `Converter::register_filters()`:
1. `BlockEncodePatcher` — Base64-encodes existing Gutenberg blocks so they survive round-trip (MUST run first)
2. `WpFiltersPatcher` — Applies `the_content` filter minus `do_shortcode`
3. `ShortcodePreconversionPatcher` — Ensures gallery shortcodes start on new lines

Interface: `PreconversionPatcherInterface` → abstract: `PreconversionPatcherAbstract`
```php
public function patch_html_source( $html_content, $post_id ): string;
```

### Post-Conversion Patchers

Hook: `ncc_filter_blocks_after_conversion` — receives `( string $blocks, string $original_html, int $post_id )`, returns patched blocks.

Registered order (all at priority 10):
1. `ImgPatcher` — Restores lost height/width/align on images
2. `CaptionImgPatcher` — Restores `[caption]` text as `<figcaption>`
3. `ParagraphPatcher` — Restores lost `dir` attribute
4. `BlockquotePatcher` — Restores lost `data-lang` attribute
5. `VideoPatcher` — Restores `[video]` shortcodes as `<video>` blocks
6. `AudioPatcher` — Restores `[audio]` shortcodes as `<audio>` blocks
7. `ShortcodeModulePatcher` — Converts `[module]` shortcodes to pullquote blocks
8. `ShortcodePullquotePatcher` — Converts `[pullquote]` shortcodes to pullquote blocks
9. `BlockDecodePatcher` — Decodes base64 blocks from step 1 (MUST run last)

Interface: `PatcherInterface` → abstract: `PatcherAbstract`
```php
public function patch_blocks_contents( $block_content, $html_content, $post_id ): string;
```

### Element Manipulators

Shared regex-based helpers in `lib/content-patcher/elementManipulators/`:
- `HtmlElementManipulator` — Match/get/replace HTML element attributes
- `SquareBracketsElementManipulator` — Match/parse shortcode-style `[tag]` elements
- `WpBlockManipulator` — Match/modify `<!-- wp:block -->` comment delimiters

## REST API

Namespace: `newspack-content-converter`. All require `edit_others_posts`.

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/settings/get-info` | Post types and statuses config |
| GET | `/conversion/get-info` | Conversion state (counts, batches) |
| GET | `/conversion/prepare` | Initialize batch queue |
| GET | `/conversion/get-batch-data` | Next batch of post IDs |
| GET | `/conversion/reset` | Clear conversion queue |
| GET | `/get-post-content/{id}` | Filtered post content for one post |
| POST | `/conversion/update-post` | Save converted blocks content |
| GET | `/conversion/get-all-converted-ids` | CSV of converted post IDs |
| GET | `/conversion/get-all-unconverted-ids` | CSV of unconverted post IDs |
| GET | `/conversion/flush-all-meta-backups` | Delete all backup postmeta |
| GET | `/restore/get-info` | Count of convertible posts to restore |
| POST | `/restore/restore-post-contents` | Restore posts to pre-conversion state |

## Frontend

React/JSX in `assets/src/`, built with `newspack-scripts`. Entry: `assets/src/index.js`.

| Component | Mount point | Purpose |
|-----------|-------------|---------|
| `Conversion` | `#ncc-conversion` | Dashboard: counts, ID filters, run/reset |
| `ContentConverter` | Block Editor page | Executes batch conversion in browser |
| `Restore` | `#ncc-restore` | Restore converted posts, flush backups |
| `Settings` | `#ncc-settings` | Display post types/statuses (read-only) |

Core JS conversion logic is in `assets/src/utilities/index.js` — `runSinglePost()` pipeline: `removeAllBlocks → getPostContentById → insertClassicBlockWithContent → dispatchConvertClassicToBlocks → getAllBlocksContents → updatePost`.

## Linting

```bash
vendor/bin/phpcs                    # PHP code standards (no composer script aliases)
vendor/bin/phpcbf                   # Auto-fix PHP issues
npm run lint                        # JS/JSX linting
```

Config: `phpcs.xml` (WPCS), `.eslintrc` via newspack-scripts.

## Testing

PHPUnit for patcher classes. Tests use a data provider pattern with fixture classes.

```bash
bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
vendor/bin/phpunit
```

Structure:
- `tests/unit/content-patcher/patchers/test-*-patcher.php` — Test classes extending `WP_UnitTestCase`
- `tests/fixtures/unit/content-patcher/patchers/class-dataprovider*patcher.php` — Test data (HTML input, blocks before, blocks expected)

## Before Submitting

1. `vendor/bin/phpcs` — No PHPCS errors
2. `vendor/bin/phpunit` — All patcher tests pass
3. `npm run build` — JS builds without errors

## Gotchas

- **Classmap autoloading** — `composer.json` uses `classmap` on `lib/` and `tests/`, not PSR-4. Run `composer dump-autoload` after adding files.
- **PascalCase classes** — Class names use `PascalCase` (e.g., `ConversionProcessor`), deviating from WordPress `Upper_Snake_Case` convention. File names follow WP convention (`class-conversionprocessor.php`).
- **No CLI** — This plugin has zero WP-CLI commands. Everything runs through REST API + browser.
- **Hardcoded batch size** — `ConversionProcessor::get_conversion_batch_size()` returns `100` with no filter or option.
- **Regex HTML parsing** — Patchers use regex to match/modify HTML. Fragile but intentional for the specific patterns handled.
- **`lib/` not `src/`** — Source code lives in `lib/`, not `src/`.
- **Dead `PatchHandler` code** — `lib/content-patcher/class-patchhandler.php` is never instantiated. The original architecture was replaced by the filter-based approach in `Converter::register_filters()`. `ConversionProcessor` still declares an unused `$patcher_handler` property.
- **No logging** — No structured logging system. Errors surface only in REST responses.
- **camelCase directory** — `elementManipulators/` uses camelCase, unlike the rest of the codebase.

## Recipes

### Add a Post-Conversion Patcher

1. Create `lib/content-patcher/patchers/class-mypatcher.php`:
```php
namespace NewspackContentConverter\ContentPatcher\Patchers;

class MyPatcher extends PatcherAbstract {
    public function patch_blocks_contents( $block_content, $html_content, $post_id ): string {
        // Compare $html_content (original) with $block_content (converted)
        // Fix anything rawHandler lost or mangled
        return $block_content;
    }
}
```
2. Register in `Converter::register_filters()` BEFORE `BlockDecodePatcher` (which must be last):
```php
add_filter( 'ncc_filter_blocks_after_conversion', [ new MyPatcher(), 'patch_blocks_contents' ], 10, 3 );
```
3. Run `composer dump-autoload`
4. Add test + data provider in `tests/`

### Add a Pre-Conversion Patcher

1. Create `lib/content-patcher/patchers/class-mypreconversionpatcher.php`:
```php
namespace NewspackContentConverter\ContentPatcher\Patchers;

class MyPreconversionPatcher extends PreconversionPatcherAbstract {
    public function patch_html_source( $html_content, $post_id ): string {
        // Modify HTML before it goes to rawHandler
        return $html_content;
    }
}
```
2. Register in `Converter::register_filters()` AFTER `BlockEncodePatcher` (which must be first):
```php
add_filter( 'ncc_filter_html_before_conversion', [ new MyPreconversionPatcher(), 'patch_html_source' ], 10, 2 );
```
3. Run `composer dump-autoload`

## Directory Structure

```
newspack-content-converter/
├── newspack-content-converter.php          # Entry point
├── composer.json                           # classmap autoload on lib/ and tests/
├── package.json                            # newspack-scripts build
├── phpcs.xml
├── webpack.config.js
├── lib/
│   ├── class-converter.php                 # Admin pages, script enqueue, filter registration
│   ├── class-convertercontroller.php       # REST API (WP_REST_Controller)
│   ├── class-conversionprocessor.php       # Batch logic, content get/update, backup/restore
│   └── content-patcher/
│       ├── interface-patch-handler.php     # PatchHandlerInterface (dead code)
│       ├── class-patchhandler.php          # PatchHandler (dead code)
│       ├── elementManipulators/
│       │   ├── class-htmlelementmanipulator.php
│       │   ├── class-squarebracketselementmanipulator.php
│       │   └── class-wpblockmanipulator.php
│       └── patchers/
│           ├── interface-patcher.php       # PatcherInterface (post-conversion)
│           ├── interface-preconversionpatcher.php
│           ├── class-patcherabstract.php
│           ├── class-preconversionpatcherabstract.php
│           └── class-*-patcher.php         # Individual patchers
├── assets/src/
│   ├── index.js                            # Entry, mounts React components
│   ├── utilities/index.js                  # API calls, conversion pipeline
│   ├── conversion/index.js                 # Dashboard UI
│   ├── content-converter/index.js          # Block Editor conversion runner
│   ├── restore/index.js                    # Restore UI
│   └── settings/index.js                   # Settings UI (read-only)
└── tests/
    ├── bootstrap.php
    ├── unit/content-patcher/patchers/      # Test classes
    └── fixtures/unit/content-patcher/patchers/  # Data providers
```

## WordPress Coding Standards

Follow WPCS with these NCC-specific notes:
- **Class naming**: Use `PascalCase` (project convention), not `Upper_Snake_Case`
- **File naming**: Follow WP convention: `class-lowercasename.php`
- **Tabs** for indentation, **spaces inside parentheses**: `function_name( $arg )`
- **Yoda conditions**: `if ( null === $value )`
- Run `vendor/bin/phpcs` before committing
