<?php

namespace PsourceLabs\SiteReviews;

class Date
{
	/**
	 * [60, 1],
	 * [60 * 100, 60],
	 * [3600 * 70, 3600],
	 * [3600 * 24 * 10, 3600 * 24],
	 * [3600 * 24 * 30, 3600 * 24 * 7],
	 * [3600 * 24 * 30 * 30, 3600 * 24 * 30],
	 * [INF, 3600 * 24 * 265],
	 */
	protected static $TIME_PERIODS = [
		[60, 1],
		[6000, 60],
		[252000, 3600],
		[864000, 86400],
		[2592000, 604800],
		[77760000, 2592000],
		[INF, 22896000],
	];

	/**
	 * @return string
	 */
	public function relative( $date )
	{
		$diff = time() - strtotime( $date );
		foreach( static::$TIME_PERIODS as $i => $timePeriod ) {
			if( $diff > $timePeriod[0] )continue;
			$unit = intval( floor( $diff / $timePeriod[1] ));
			$relativeDates = [
				_n( 'vor %s Sekunde', 'vor %s Sekunden', $unit, 'blogs-directory' ),
				_n( 'vor %s Minute', 'vor %s Minuten', $unit, 'blogs-directory' ),
				_n( 'vor einer Stunde', 'vor %s Stunden', $unit, 'blogs-directory' ),
				_n( 'gestern', 'vor %s Tagen', $unit, 'blogs-directory' ),
				_n( 'vor einer Woche', 'vor %s Wochen', $unit, 'blogs-directory' ),
				_n( 'vor %s Monat', 'vor %s Monaten', $unit, 'blogs-directory' ),
				_n( 'vor %s Jahr', 'vor %s Jahren', $unit, 'blogs-directory' ),
			];
			$relativeDate = $relativeDates[$i];
			if( strpos( $relativeDate, '%s' ) !== false ) {
				return sprintf( $relativeDate, $unit );
			}
			return $relativeDate;
		}
	}
}
