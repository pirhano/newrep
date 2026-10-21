<?php
/**
 * Slot computation: opening hours, closed days, notice period and existing bookings.
 *
 * @package RhomijaBooking
 */

defined( 'ABSPATH' ) || exit;

class RB_Availability {

	/**
	 * Opening ranges for an ISO weekday (1 = Monday), in minutes since midnight.
	 *
	 * @return array<int,array{0:int,1:int}>
	 */
	public static function ranges_for_weekday( $weekday ) {
		$hours = RB_Settings::get( 'hours' );
		$value = isset( $hours[ $weekday ] ) ? $hours[ $weekday ] : '';
		$out   = array();
		foreach ( explode( ',', (string) $value ) as $part ) {
			if ( preg_match( '/(\d{2}):(\d{2})-(\d{2}):(\d{2})/', $part, $m ) ) {
				$out[] = array( $m[1] * 60 + $m[2], $m[3] * 60 + $m[4] );
			}
		}
		return $out;
	}

	public static function now() {
		return new DateTimeImmutable( 'now', wp_timezone() );
	}

	public static function parse_date( $ymd ) {
		if ( ! is_string( $ymd ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $ymd ) ) {
			return null;
		}
		$d = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, wp_timezone() );
		return ( $d && $d->format( 'Y-m-d' ) === $ymd ) ? $d : null;
	}

	/**
	 * First and last bookable dates (inclusive).
	 *
	 * @return array{0:DateTimeImmutable,1:DateTimeImmutable}
	 */
	public static function window() {
		$today = self::now()->setTime( 0, 0 );
		$last  = $today->modify( '+' . (int) RB_Settings::get( 'max_days_ahead' ) . ' days' );
		return array( $today, $last );
	}

	/**
	 * Number of active (pending or confirmed) bookings per "Y-m-d H:i" between two dates.
	 *
	 * @return array<string,int>
	 */
	public static function booked_counts( $from_ymd, $to_ymd, $exclude_id = 0 ) {
		$ids = get_posts(
			array(
				'post_type'        => RB_Appointments::POST_TYPE,
				'post_status'      => 'any',
				'fields'           => 'ids',
				'posts_per_page'   => -1,
				'no_found_rows'    => true,
				'suppress_filters' => true,
				'exclude'          => $exclude_id ? array( (int) $exclude_id ) : array(),
				'meta_query'       => array(
					'relation' => 'AND',
					array(
						'key'     => '_rb_date',
						'value'   => array( $from_ymd, $to_ymd ),
						'compare' => 'BETWEEN',
						'type'    => 'CHAR',
					),
					array(
						'key'     => '_rb_status',
						'value'   => RB_Appointments::active_statuses(),
						'compare' => 'IN',
					),
				),
			)
		);
		$counts = array();
		foreach ( $ids as $id ) {
			$key            = get_post_meta( $id, '_rb_date', true ) . ' ' . get_post_meta( $id, '_rb_time', true );
			$counts[ $key ] = isset( $counts[ $key ] ) ? $counts[ $key ] + 1 : 1;
		}
		return $counts;
	}

	/**
	 * All slot start times ("H:i") the clinic offers on a date, ignoring bookings.
	 *
	 * @return string[]
	 */
	public static function day_slots( DateTimeImmutable $day ) {
		if ( in_array( $day->format( 'Y-m-d' ), RB_Settings::closed_dates(), true ) ) {
			return array();
		}
		$step  = max( 5, (int) RB_Settings::get( 'slot_minutes' ) );
		$slots = array();
		foreach ( self::ranges_for_weekday( (int) $day->format( 'N' ) ) as $range ) {
			for ( $t = $range[0]; $t + $step <= $range[1]; $t += $step ) {
				$slots[] = sprintf( '%02d:%02d', intdiv( $t, 60 ), $t % 60 );
			}
		}
		return $slots;
	}

	/**
	 * Free slots on a date, given pre-fetched booking counts.
	 *
	 * @return string[]
	 */
	public static function free_slots( DateTimeImmutable $day, array $counts ) {
		list( $first, $last ) = self::window();
		if ( $day < $first || $day > $last ) {
			return array();
		}
		$earliest = self::now()->modify( '+' . (int) RB_Settings::get( 'min_notice_hours' ) . ' hours' );
		$capacity = max( 1, (int) RB_Settings::get( 'capacity' ) );
		$ymd      = $day->format( 'Y-m-d' );
		$free     = array();
		foreach ( self::day_slots( $day ) as $hm ) {
			$start = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $ymd . ' ' . $hm, wp_timezone() );
			if ( $start < $earliest ) {
				continue;
			}
			$taken = isset( $counts[ $ymd . ' ' . $hm ] ) ? $counts[ $ymd . ' ' . $hm ] : 0;
			if ( $taken < $capacity ) {
				$free[] = $hm;
			}
		}
		return $free;
	}

	/**
	 * @return string[]
	 */
	public static function slots_for_date( $ymd, $exclude_id = 0 ) {
		$day = self::parse_date( $ymd );
		if ( ! $day ) {
			return array();
		}
		return self::free_slots( $day, self::booked_counts( $ymd, $ymd, $exclude_id ) );
	}

	public static function is_available( $ymd, $hm, $exclude_id = 0 ) {
		return in_array( $hm, self::slots_for_date( $ymd, $exclude_id ), true );
	}

	/**
	 * Free-slot count for every bookable day of a month ("Y-m").
	 *
	 * @return array<string,int>
	 */
	public static function month( $ym ) {
		$start = self::parse_date( $ym . '-01' );
		if ( ! $start ) {
			return array();
		}
		$end    = $start->modify( 'last day of this month' );
		$counts = self::booked_counts( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );
		$out    = array();
		for ( $d = $start; $d <= $end; $d = $d->modify( '+1 day' ) ) {
			$out[ $d->format( 'Y-m-d' ) ] = count( self::free_slots( $d, $counts ) );
		}
		return $out;
	}

	/**
	 * Earliest free slot within the booking window.
	 *
	 * @return array{date:string,time:string}|null
	 */
	public static function next_available() {
		list( $first, $last ) = self::window();
		$counts               = self::booked_counts( $first->format( 'Y-m-d' ), $last->format( 'Y-m-d' ) );
		for ( $d = $first; $d <= $last; $d = $d->modify( '+1 day' ) ) {
			$free = self::free_slots( $d, $counts );
			if ( $free ) {
				return array(
					'date' => $d->format( 'Y-m-d' ),
					'time' => $free[0],
				);
			}
		}
		return null;
	}

	/**
	 * "09:00-13:00, 14:30-18:00" → "09:00 – 13:00 · 14:30 – 18:00".
	 */
	private static function pretty_ranges( $value ) {
		$parts = array_filter( array_map( 'trim', explode( ',', (string) $value ) ) );
		return implode( ' · ', array_map( static function ( $r ) {
			return str_replace( '-', ' – ', $r );
		}, $parts ) );
	}

	/**
	 * Consecutive days sharing the same hours are grouped: "Lundi – Samedi" => "09:00 – 17:00".
	 *
	 * @return array<string,string>
	 */
	public static function hours_display() {
		$days   = RB_Settings::days();
		$hours  = RB_Settings::get( 'hours' );
		$groups = array();
		foreach ( $days as $num => $label ) {
			$value = isset( $hours[ $num ] ) ? (string) $hours[ $num ] : '';
			$last  = count( $groups ) - 1;
			if ( $last >= 0 && $groups[ $last ]['value'] === $value ) {
				$groups[ $last ]['to'] = $label;
			} else {
				$groups[] = array(
					'from'  => $label,
					'to'    => $label,
					'value' => $value,
				);
			}
		}
		$out = array();
		foreach ( $groups as $g ) {
			$key         = $g['from'] === $g['to'] ? $g['from'] : $g['from'] . ' – ' . $g['to'];
			$out[ $key ] = '' === $g['value'] ? __( 'Fermé', 'rhomija-booking' ) : self::pretty_ranges( $g['value'] );
		}
		return $out;
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function hours_schema() {
		$names = array( 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday' );
		$specs = array();
		foreach ( $names as $num => $name ) {
			foreach ( self::ranges_for_weekday( $num ) as $r ) {
				$key = $r[0] . '-' . $r[1];
				if ( ! isset( $specs[ $key ] ) ) {
					$specs[ $key ] = array(
						'@type'     => 'OpeningHoursSpecification',
						'dayOfWeek' => array(),
						'opens'     => sprintf( '%02d:%02d', intdiv( $r[0], 60 ), $r[0] % 60 ),
						'closes'    => sprintf( '%02d:%02d', intdiv( $r[1], 60 ), $r[1] % 60 ),
					);
				}
				$specs[ $key ]['dayOfWeek'][] = $name;
			}
		}
		return array_values( $specs );
	}
}
