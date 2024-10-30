<?php
/**
 * Plugin Name:       JVH WP All Import Extender
 * Description:       Export and import VC single images and VC snippets
 * Version:           1.4.2
 * Author:            JVH webbouw
 * Author URI:        https://jvhwebbouw.nl
 * License:           GPL-v3
 * Requires PHP:      7.3
 * Requires at least: 5.0
 */

require_once __DIR__ . '/inc/VcImage.php';
require_once __DIR__ . '/inc/VcSnippet.php';

require_once __DIR__ . '/inc/ExportExtender.php';
require_once __DIR__ . '/inc/ImportExtender.php';

require_once __DIR__ . '/inc/ExportEpoExtender.php';
require_once __DIR__ . '/inc/ImportEpoExtender.php';

require_once __DIR__ . '/inc/ExportAcfRepeaterExtender.php';
require_once __DIR__ . '/inc/ImportAcfRepeaterExtender.php';
