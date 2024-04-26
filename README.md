# newspack-content-converter
Mass-conversion of pre-Gutenberg Classic HTML Posts and Pages to the Gutenberg Blocks.

### Disclaimer

Make sure to back up your site contents fully, because this converter updates the content of your database permanently by replacing the classic HTML content with Gutenberg Blocks content.

This plugin is open source, and the creators can not be held responsible for any data loss or consequences of its usage.

### Convert HTML to Blocks

The "Newspack Content Converter" > "Converter" submenu item in the Dashboard's main allows you to mass convert all your HTML posts to Gutenberg blocks.

Every time this screen is loaded, it will scan your existing posts and display the number of unconverted posts which can then be converted. All Posts which do not begin with block syntax `<!-- wp...` can be converted.

Click "Run conversion" to actually begin the conversion. The page will reloaded and conversion will begin. This page with conversion running should not be stopped or closed until it is fully completed.

While the conversion is running, a link will allow you to open several conversion browser tabs at once, which can speed up the entire conversion. Each such additional tab picks up the next batch of posts an converts it in a parallel process. Depending on your computer performance, it is usually recommended to run between 1 to 10 maximum parallel tabs.

In case that the conversion page gets closed or unexpectedly terminated while it hasn't finished converting, the "Converter" page will let you "Reset" the conversion and simply continue where you left off.

After the conversion is complete, it may be necessary to flush the object cache to see the effects in Gutenberg editor or the front page.

#### If the conversion page is not loading

After "Run conversion" is clicked, if the conversion page is not properly displaying and running, an alert with an error message will pop up after some time.

If the problem persists, temporarily deactivate all other active plugins and try running the conversion again. Once the conversion is complete, reactivate your site plugins.

#### Restore original content

Plugin backs up and stores original post content before conversion to blocks as custom postmeta. The "Restore content" page allows you to restore converted posts to the latest available backup.

It also lets you delete all this custom postmeta from your database, which will permanently delete the backups.

#### Settings

"Settings" page displays the post types and post statuses which get converted.

### Development

- use PHP 8.1 or greater
- `composer install`
- `nvm use 16`
- `npm ci`
- `npm run build` for a single build or `npm start` to compile the JS files and start the file watcher
- `npm run release:archive` to package a release. The archive will be created in `assets/release/newspack-content-converter.zip`
