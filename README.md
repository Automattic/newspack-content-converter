# newspack-content-converter
Plugin which mass-converts pre-Gutenberg Classic HTML Posts and Pages to the Gutenberg Blocks.

### Disclaimer

Please make sure to back up your site contents fully, because this converter updates the content permanently by replacing the classic HTML content with Gutenberg Blocks content.

This plugin is open source, and the creators can not be held responsible for any data loss or consequences of its usage.

The plugin is presently in Alpha, and used primarily as a Developer's tool -- please check back for a full and improved version soon.


### Usage

Clicking "Newspack Content Converter" in the Admin area's main left-hand menu, opens a page where the number of Posts and number of batches queued for Conversion is displayed. Clicking the "Run conversion" button there actually initializes the conversion and starts converting your queued Posts and Pages to Blocks.

After a conversion is complete, it's sometimes necessary to flush the cache, as well.

After running Newspack Content Converter, if you need to undo the conversion, you restore the `wp_posts` table to its pre-conversion state by running `wp newspack-content-converter restore-content`. This will also undo any other editorial changes that have been made to the content in the interim.

To re-scan your freshest HTML Posts, and to update the conversion queue, run the CLI command `wp newspack-content-converter reset`.

### Development

- `composer install`
- `nvm use 16`
- `npm ci --legacy-peer-deps`
- `npm run build`
- Run `npm run release:archive` to package a release. The archive will be created in `assets/release/newspack-content-converter.zip`
- Run `npm start` to compile the JS files, and start file watcher
