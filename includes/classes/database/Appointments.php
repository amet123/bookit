<?php

namespace Bookit\Classes\Database;

use Bookit\Classes\Vendor\DatabaseModel;

class Appointments extends DatabaseModel {

	public static $pending    = 'pending';
	public static $approved   = 'approved';
	public static $cancelled  = 'cancelled';
	public static $complete   = 'complete';
	public static $delete     = 'delete'; // if deleted
	public static $statusList = array( 'pending', 'approved', 'cancelled', 'delete' );
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE {$table_name} (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			service_id INT UNSIGNED NOT NULL,
			staff_id INT UNSIGNED NOT NULL,
			customer_id INT UNSIGNED NOT NULL,
			date_timestamp INT NOT NULL,
			start_time INT NOT NULL,
			end_time INT NOT NULL,
			price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			notes longtext DEFAULT NULL,
			created_from ENUM('front', 'back') NOT NULL DEFAULT 'front',
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY ({$primary_key}),
            INDEX `idx_service_id` (`service_id`),
            INDEX `idx_staff_id` (`staff_id`),
            INDEX `idx_customer_id` (`customer_id`),
            INDEX `idx_date_timestamp` (`date_timestamp`),
            INDEX `idx_start_time` (`start_time`),
            INDEX `idx_end_time` (`end_time`),
            INDEX `idx_status` (`status`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	/**
	 * Create Appointment with payment
	 */
	public static function create_appointment( $data ) {

		$appointment_data = array(
			'staff_id'       => $data['staff_id'],
			'customer_id'    => $data['customer_id'],
			'service_id'     => $data['service_id'],
			'status'         => $data['status'],
			'date_timestamp' => $data['date_timestamp'],
			'start_time'     => $data['start_time'],
			'end_time'       => $data['end_time'],
			'price'          => number_format( (float) $data['clear_price'], 2, ' . ', '' ),
			'notes'          => $data['notes'],
			'created_at'     => wp_date( 'Y-m-d H:i:s' ),
			'updated_at'     => wp_date( 'Y-m-d H:i:s' ),
		);

		self::insert( $appointment_data );
		$appointment_id = self::insert_id();

		/** create payment **/
		if ( 0 == (float) $appointment_data['price'] ) {
			$data['payment_method'] = Payments::$freeType;
			$data['payment_status'] = Payments::$completeType;
		}

		$payment_data = array(
			'appointment_id' => $appointment_id,
			'type'           => ( ! empty( $data['payment_method'] ) ) ? $data['payment_method'] : Payments::$defaultType,
			'status'         => ( ! empty( $data['payment_status'] ) ) ? $data['payment_status'] : Payments::$defaultStatus,
			'total'          => $appointment_data['price'],
			'created_at'     => wp_date( 'Y-m-d H:i:s' ),
			'updated_at'     => wp_date( 'Y-m-d H:i:s' ),
		);

		Payments::insert( $payment_data );

		return $appointment_id;
	}

	/**
	 * Update Appointment with payment
	 */
	public static function update_appointment( $data, $id ) {

		$appointment = array(
			'staff_id'       => $data['staff_id'],
			'service_id'     => $data['service_id'],
			'date_timestamp' => $data['date_timestamp'],
			'start_time'     => $data['start_time'],
			'end_time'       => $data['end_time'],
			'price'          => number_format( (float) $data['price'], 2, ' . ', '' ),
			'status'         => $data['status'],
			'notes'          => $data['notes'],
			'created_from'   => $data['created_from'],
			'updated_at'     => wp_date( 'Y-m-d H:i:s' ),
		);

		self::update( $appointment, array( 'id' => $id ) );

		/** update payment **/
		if ( Payments::$freeType == $data['payment_method'] ) {
			$data['payment_status'] = Payments::$completeType;
		}

		$payment_data = array(
			'type'       => $data['payment_method'],
			'status'     => $data['payment_status'],
			'total'      => $appointment['price'],
			'updated_at' => wp_date( 'Y-m-d H:i:s' ),
		);
		Payments::update( $payment_data, array( 'appointment_id' => $id ) );
	}

	/**
	 * Change Appointment status to delete and delete payment
	 */
	public static function delete_appointment( $id ) {

		$payment          = Payments::get( 'appointment_id', $id );
		$notes['payment'] = $payment;

		/** update appointment , add payment info before delete */
		$appointment = array(
			'status'     => self::$delete,
			'notes'      => serialize( $notes ),
			'updated_at' => wp_date( 'Y-m-d H:i:s' ),
		);
		self::update( $appointment, array( 'id' => $id ) );

		/** delete payment **/
		Payments::delete( $payment->id );
	}


	/**
	 * Get Customer Appointments
	 * @param $customer_id int
	 * @return mixed
	 */
	public static function customer_appointments( int $customer_id ) {
	    global $wpdb;
	    $sql = sprintf(
	        'SELECT `%1$s`.*
	                FROM `%1$s`
	                WHERE customer_id = %%d ORDER BY `%1$s`.id ASC',
	        esc_sql( self::_table() )
	    );
	    return $wpdb->get_results( $wpdb->prepare( $sql, $customer_id ) );
	}

	/**
	 * Get Category Appointments
	 * @param $category_id int
	 * @return mixed
	 */
	public static function category_appointments( $category_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT `%1$s`.*
					FROM `%1$s`
					LEFT JOIN `%2$s` ON `%1$s`.service_id = `%2$s`.id
					WHERE `%2$s`.category_id = %%d',
	        esc_sql( self::_table() ),
			esc_sql( Services::_table() )
		);
		return $wpdb->get_results( $wpdb->prepare( $sql, intval( $category_id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
	/**
	 * Get Service Appointments
	 * @param $service_id int
	 * @return mixed
	 */
	public static function service_appointments( int $service_id ) {
	    global $wpdb;
	    $sql = sprintf(
	        'SELECT `%1$s`.*
	                FROM `%1$s`
	                WHERE service_id = %%d ORDER BY `%1$s`.id ASC',
	        esc_sql( self::_table() )
	    );
	    return $wpdb->get_results( $wpdb->prepare( $sql, $service_id ) );
	}

	/**
	 * Get Staff Appointments
	 * @param $staff_id int
	 * @return mixed
	 */
	public static function staff_appointments( $staff_id ) {
		global $wpdb;
		$sql = sprintf(
		  'SELECT `%1$s`.*
		          FROM `%1$s`
		          WHERE staff_id = %%d ORDER BY `%1$s`.id ASC',
		  esc_sql( self::_table() )
		);

		return $wpdb->get_results( $wpdb->prepare( $sql, $staff_id ) );
	}

	/**
	 * Get Rows with Pagination
	 *
	 * @param $limit
	 * @param $offset
	 * @param string $status
	 * @param string $sort
	 * @param string $order
	 *
	 * @return mixed
	 */
	public static function get_paged( $limit, $offset, $status = '', $sort = '', $order = '', $filter = array() ) {
		global $wpdb;

		$search_sql    = '';
		$search_values = array();
		if ( ! empty( $filter['search'] ) ) {
			$like          = '%' . $wpdb->esc_like( $filter['search'] ) . '%';
			$ct            = esc_sql( Customers::_table() );
			$search_sql    = " AND (`{$ct}`.phone LIKE %s OR `{$ct}`.full_name LIKE %s OR `{$ct}`.email LIKE %s)";
			$search_values = array( $like, $like, $like );
		}

		$at  = esc_sql( self::_table() );
		$ct  = esc_sql( Customers::_table() );
		$st  = esc_sql( Staff::_table() );
		$svt = esc_sql( Services::_table() );
		$pt  = esc_sql( Payments::_table() );
		$del = esc_sql( self::$delete );
		$pk  = empty( $sort ) ? esc_sql( static::$primary_key ) : esc_sql( $sort );
		$od  = empty( $order ) ? 'DESC' : esc_sql( $order );
		$lim = intval( $limit );
		$off = intval( $offset );

		$status_sql = ! empty( $status ) ? " AND `{$at}`.status = '" . esc_sql( $status ) . "'" : '';
		$start_sql  = ! empty( $filter['start'] ) ? " AND `{$at}`.start_time >= " . intval( $filter['start'] ) : '';
		$end_sql    = ! empty( $filter['end'] ) ? " AND `{$at}`.end_time <= " . intval( $filter['end'] ) : '';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "SELECT `{$at}`.*,
		                `{$pt}`.type as payment_method,
		                `{$pt}`.status as payment_status,
		                `{$pt}`.total as total,
		                `{$ct}`.full_name as customer_name,
		                `{$ct}`.email as customer_email,
		                `{$ct}`.phone as customer_phone,
		                `{$st}`.full_name as staff_name,
		                `{$svt}`.title as service_name
			FROM `{$at}`
			LEFT JOIN `{$ct}` ON `{$at}`.customer_id = `{$ct}`.id
			LEFT JOIN `{$st}` ON `{$at}`.staff_id = `{$st}`.id
			LEFT JOIN `{$svt}` ON `{$at}`.service_id = `{$svt}`.id
			LEFT JOIN `{$pt}` ON `{$at}`.id = `{$pt}`.appointment_id
			WHERE `{$at}`.status != '{$del}'
			{$status_sql} {$start_sql} {$end_sql} {$search_sql}
			ORDER BY `{$at}`.`{$pk}` {$od}
			LIMIT {$lim} OFFSET {$off}";

		if ( ! empty( $search_values ) ) {
			return $wpdb->get_results( $wpdb->prepare( $sql, $search_values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Export All Rows
	 *
	 * @return mixed
	 */
	public static function export_all() {
		global $wpdb;
		return $wpdb->get_results(
			sprintf(
				'SELECT `%1$s`.*,
		                `%5$s`.type as payment_method,
		                `%5$s`.status as payment_status,
		                `%5$s`.total as total,
		                `%2$s`.full_name as customer,
		                `%2$s`.phone as customer_phone,
		                `%3$s`.full_name as staff,
		                `%4$s`.title as service
				FROM `%1$s`
				LEFT JOIN `%2$s` ON `%1$s`.customer_id = `%2$s`.id
				LEFT JOIN `%3$s` ON `%1$s`.staff_id = `%3$s`.id
				LEFT JOIN `%4$s` ON `%1$s`.service_id = `%4$s`.id
				LEFT JOIN `%5$s` ON `%1$s`.id = `%5$s`.appointment_id
				ORDER BY `%1$s`.`%6$s` DESC',
				esc_sql( self::_table() ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				esc_sql( Customers::_table() ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				esc_sql( Staff::_table() ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				esc_sql( Services::_table() ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				esc_sql( Payments::_table() ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				esc_sql( static::$primary_key ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			),
			ARRAY_A
		);
	}

	/**
	 * Check Appointment
	 * @param $data
	 * @return mixed
	 */
	public static function checkAppointment( $data ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT `%1$s`.id FROM `%1$s`
					WHERE staff_id = %%d
					AND service_id = %%d
					AND status != "%2$s"
					AND status != "%3$s"
					AND date_timestamp = %%d
					AND ( start_time <= %%d AND end_time >= %%d )',
			esc_sql( self::_table() ),
			esc_sql( self::$cancelled ),
			esc_sql( self::$delete )
		);
		return $wpdb->get_var(
			$wpdb->prepare(
				$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				intval( $data['staff_id'] ),
				intval( $data['service_id'] ),
				intval( $data['date_timestamp'] ),
				intval( $data['start_time'] ),
				intval( $data['end_time'] )
			)
		);
	}

	/**
	 * Get Months Appointments
	 * @param $data
	 * @return mixed
	 */
	public static function month_appointments( $data ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT date_timestamp, COUNT(*) appointments FROM `%s`
					WHERE service_id = %%d AND status NOT IN ( "%2$s", "%3$s" )
					AND ( ( date_timestamp = %%d AND start_time >= %%d ) OR date_timestamp BETWEEN %%d AND %%d )
					GROUP BY date_timestamp',
			esc_sql( self::_table() ),
			esc_sql( self::$cancelled ),
			esc_sql( self::$delete )
		);
		return $wpdb->get_results(
			$wpdb->prepare(
				$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				intval( $data['service_id'] ),
				intval( $data['today_timestamp'] ),
				intval( $data['now_timestamp'] ),
				intval( $data['start_timestamp'] ),
				intval( $data['end_timestamp'] )
			)
		);
	}

	/**
	 * Get Day Appointments
	 * @param $data
	 * @return mixed
	 */
	public static function day_appointments( $data ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT * FROM `%s` WHERE date_timestamp = %%d %s %s AND status NOT IN ( "%4$s", "%5$s" ) ORDER BY `%1$s`.`%6$s`',
			esc_sql( self::_table() ),
			( ! empty( $data['service_id'] ) ) ? sprintf( "AND service_id = %d", intval( $data['service_id'] ) ) : '',
			( ! empty( $data['staff_id'] ) ) ? sprintf( "AND staff_id = %d", intval( $data['staff_id'] ) ) : '',
			esc_sql( self::$cancelled ),
			esc_sql( self::$delete ),
			'start_time'
		);
		return $wpdb->get_results(
			$wpdb->prepare(
				$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				intval( $data['date_timestamp'] )
			)
		);
	}

	/**
	 * Get Pending Appointments Count
	 * @return mixed
	 */
	public static function pending_appointments() {
		global $wpdb;
		$date_utc = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		$sql      = sprintf(
			'SELECT COUNT(*) FROM `%s` WHERE status = "%s" AND start_time >= %%d',
			esc_sql( self::_table() ),
			esc_sql( self::$pending )
		);
		return $wpdb->get_var(
			$wpdb->prepare(
				$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$date_utc->getTimestamp()
			)
		);
	}

	/**
	 * Get Total Count of Rows by status
	 * @param $status
	 * @return mixed
	 */
	public static function get_appointments_count( $status, $filter = array() ) {
		global $wpdb;

		$search_sql    = '';
		$search_values = array();
		if ( ! empty( $filter['search'] ) ) {
			$like          = '%' . $wpdb->esc_like( $filter['search'] ) . '%';
			$ct            = esc_sql( Customers::_table() );
			$search_sql    = " AND (`{$ct}`.phone LIKE %s OR `{$ct}`.full_name LIKE %s OR `{$ct}`.email LIKE %s)";
			$search_values = array( $like, $like, $like );
		}

		$at         = esc_sql( self::_table() );
		$ct         = esc_sql( Customers::_table() );
		$del        = esc_sql( self::$delete );
		$status_sql = ! empty( $status ) ? " AND status = '" . esc_sql( $status ) . "'" : '';
		$start_sql  = ! empty( $filter['start'] ) ? " AND `{$at}`.start_time >= " . intval( $filter['start'] ) : '';
		$end_sql    = ! empty( $filter['end'] ) ? " AND `{$at}`.end_time <= " . intval( $filter['end'] ) : '';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "SELECT COUNT(*) FROM `{$at}` LEFT JOIN `{$ct}` ON `{$at}`.customer_id = `{$ct}`.id WHERE `{$at}`.status != '{$del}' {$status_sql} {$start_sql} {$end_sql} {$search_sql}";

		if ( ! empty( $search_values ) ) {
			return $wpdb->get_var( $wpdb->prepare( $sql, $search_values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Change Appointment Status
	 * @param $id
	 * @param $status
	 */
	public static function change_status( $id, $status ) {
		$data  = array( 'status' => $status );
		$where = array( 'id' => $id );
		self::update( $data, $where );
	}

	/**
	 * Change Payment Status | used just for pro version
	 * @param $id
	 * @param $payment_status
	 */
	public static function change_payment_status( $id, $payment_status ) {
		Payments::change_payment_status( $id, $payment_status );
	}

	/**
	 * Get Admin Day Appointments
	 * @param $data
	 * @return mixed
	 */
	public static function get_full_appointment_by_id( $id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*, 
		                %2$s.email as customer_email,
		                %2$s.full_name as customer_name,
		                %2$s.phone as customer_phone,
		                %3$s.id as staff_id,
		                %3$s.email as staff_email,
		                %3$s.full_name as staff_name,
		                %3$s.phone as staff_phone,
		                %4$s.total as total,
		                %4$s.type as payment_method,
		                %4$s.status as payment_status,
		                `%4$s`.id as payment_id,
		                `%5$s`.title as service_name
			FROM `%1$s`
			LEFT JOIN `%2$s` ON `%1$s`.customer_id = `%2$s`.id
			LEFT JOIN `%3$s` ON `%1$s`.staff_id = `%3$s`.id
			LEFT JOIN `%4$s` ON `%1$s`.id = `%4$s`.appointment_id
			LEFT JOIN `%5$s` ON `%1$s`.service_id = `%5$s`.id
			WHERE `%1$s`.id = %%d',
			esc_sql( self::_table() ),
			esc_sql( Customers::_table() ),
			esc_sql( Staff::_table() ),
			esc_sql( Payments::_table() ),
			esc_sql( Services::_table() )
		);

		return $wpdb->get_row( $wpdb->prepare( $sql, intval( $id ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get appointments short data filter by date range
	 * @return array
	 */
	public static function appointments_by_date_full( $start, $end, $filter_data = array() ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT
               	 	`%1$s`.*,
					`%2$s`.title as service,
	                `%2$s`.icon_id as icon,
	                `%3$s`.email as customer_email,
	                `%3$s`.full_name as customer_name,
	                `%3$s`.phone as customer_phone,
	                `%4$s`.full_name as staff_name,
	                `%5$s`.type as payment_method,
	                `%5$s`.status as payment_status,
	                `%5$s`.total as total
			FROM `%1$s`
				LEFT JOIN `%2$s` ON `%1$s`.service_id = `%2$s`.id
				LEFT JOIN `%3$s` ON `%1$s`.customer_id = `%3$s`.id
				LEFT JOIN `%4$s` ON `%1$s`.staff_id = `%4$s`.id
				LEFT JOIN `%5$s` ON `%1$s`.id = `%5$s`.appointment_id
			WHERE `%1$s`.status != "%6$s" AND date_timestamp BETWEEN %%d AND %%d
			%7$s %8$s %9$s
			ORDER BY `%1$s`.start_time',
			esc_sql( self::_table() ),
			esc_sql( Services::_table() ),
			esc_sql( Customers::_table() ),
			esc_sql( Staff::_table() ),
			esc_sql( Payments::_table() ),
			esc_sql( self::$delete ),
			( ! empty( $filter_data['service_ids'] ) ) ? sprintf( "AND service_id IN ( %s )", esc_sql( $filter_data['service_ids'] ) ) : '',
			( ! empty( $filter_data['staff_id'] ) ) ? sprintf( "AND staff_id = %d", intval( $filter_data['staff_id'] ) ) : '',
			( ! empty( $filter_data['status'] ) ) ? sprintf( "AND `%s`.status = '%s'", esc_sql( self::_table() ), esc_sql( $filter_data['status'] ) ) : ''
		);

		return $wpdb->get_results( $wpdb->prepare( $sql, intval( $start ), intval( $end ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get total appointments for service | staff
	 * @return array
	 */
	public static function get_total_active_assosiated_appointments( $service_ids = '', $staff_ids = '', $customer_ids = '' ) {
		global $wpdb;

		$sql = sprintf(
			'SELECT COUNT(`%1$s`.id)
					FROM `%1$s`
					WHERE start_time > %%d
					%2$s %3$s %4$s',
			esc_sql( self::_table() ),
			( ! empty( $service_ids ) ) ? sprintf( "AND service_id IN ( %s )", esc_sql( $service_ids ) ) : '',
			( ! empty( $staff_ids ) ) ? sprintf( "AND staff_id IN ( %s )", esc_sql( $staff_ids ) ) : '',
			( ! empty( $customer_ids ) ) ? sprintf( "AND customer_id IN ( %s )", esc_sql( $customer_ids ) ) : ''
		);

		$now = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		return $wpdb->get_var( $wpdb->prepare( $sql, intval( $now ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
