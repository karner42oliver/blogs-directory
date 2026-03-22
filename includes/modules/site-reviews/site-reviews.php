<?php
/**
 * Module Name: Site Reviews
 * Description: Receive and display site reviews
 * Version:     2.12.5
 * Author:      DerNerd
 */

defined( 'WPINC' ) || die;

require_once __DIR__ . '/activate.php';

if( !glsr_version_check() )return;

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/compatibility.php';
require_once __DIR__ . '/helpers.php';

use GeminiLabs\SiteReviews\App;
use GeminiLabs\SiteReviews\Providers\MainProvider;

$app = App::load();

$app->register( new MainProvider );

register_activation_hook( __FILE__, array( $app, 'activate' ));
register_deactivation_hook( __FILE__, array( $app, 'deactivate' ));

$app->init();
