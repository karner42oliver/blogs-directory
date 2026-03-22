<?php

namespace Geminilabs\SiteReviews;

use PsourceLabs\SiteReviews\App;

abstract class Shortcode
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Database
	 */
	protected $db;

	public function __construct( App $app )
	{
		$this->app = $app;
		$this->db  = $app->make( 'Database' );
	}

	/**
	 * Add a help page for the shortcodes
	 *
	 * @return void
	 */
	public function addHelpPage()
	{
		add_action( 'load-post.php',     [ $this, 'helpTabsHook'] );
		add_action( 'load-post-new.php', [ $this, 'helpTabsHook'] );
	}

	/**
	 * Add the shortcode help tabs and content
	 *
	 * @return void
	 */
	public function addHelpTabs()
	{
		get_current_screen()->add_help_tab([
			'id'      => $this->app->id . '-shortcodes',
			'title'   => $this->app->name . ' Shortcodes',
			'content' => $this->helpContent(),
		]);
	}

	/**
	 * The shortcode help content
	 *
	 * @return string
	 */
	public function helpContent()
	{
		return '';
	}

	/**
	 * Hook to insert help tabs at the end of existing tabs
	 *
	 * @return void
	 */
	public function helpTabsHook()
	{
		add_action( 'in_admin_header', [$this, 'addHelpTabs'] );
	}
}
