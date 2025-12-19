# ContentDM Explorer

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Import and display ContentDM digital collections in WordPress. Create CPTs for collections and items with shortcodes.

## Description

ContentDM Explorer allows you to connect your WordPress site to OCLC's ContentDM digital collection management system. Import collections and items as Custom Post Types and display them using built-in shortcodes.

## Features

- Connect to any ContentDM instance
- Import collections as CPTs
- Import items with full metadata
- Editable metadata fields for imported collections and items
- Custom preview image upload for collections and items
- Multiple shortcodes for display
- Theme-isolated CSS to prevent style conflicts
- Responsive frontend templates
- Easy-to-use admin interface

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[cdm_collections]` | Display collection grid |
| `[cdm_items]` | Display items grid |
| `[cdm_item]` | Display single item |
| `[cdm_gallery]` | Lightbox image gallery |
| `[cdm_search]` | Search form |

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to **CDM Explorer > Settings**
4. Enter your ContentDM server URL
5. Import collections and items

## Frequently Asked Questions

### What is ContentDM?

ContentDM is a digital collection management software from OCLC used by libraries, archives, and museums to organize, store, and deliver digital content.

### How do I find my ContentDM URL?

Your ContentDM URL typically follows this pattern: `https://cdmXXXXX.contentdm.oclc.org` where XXXXX is your institution's unique identifier.

### Can I customize the display?

Yes! You can override the plugin's templates by copying them to your theme folder. CSS variables are also available for styling customization.

### Can I edit imported metadata?

Yes! When editing a collection or item, you'll find meta boxes where you can modify the imported metadata fields like aliases, URLs, and item counts. You can also upload a custom preview image to override the ContentDM image.

### Will the shortcodes conflict with my theme styles?

No. The plugin uses isolated CSS with high specificity to prevent theme styles from interfering with the shortcode output.

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher

## Changelog

### 1.0.0-beta
- Initial beta release
- Collection and item import
- Five shortcodes included
- Admin dashboard with statistics
- Frontend templates
- Editable metadata meta boxes for collections and items
- Custom preview image upload with WordPress Media Library integration
- Theme-isolated CSS to prevent style conflicts with shortcode output

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

Developed by [Christian Abou Daher](https://ca11.tech/)
