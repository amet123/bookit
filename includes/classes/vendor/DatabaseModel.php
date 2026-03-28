<?php

namespace Bookit\Classes\Vendor;

abstract class DatabaseModel {

	public static $primary_key     = 'id';
	protected static $table_prefix = 'bookit_';

	/**
	 * Generate Table Name from Called Class
	 * @return string
	 */
	public static function _table() {
		global $wpdb;
		$classname = explode( '\\', strtolower( get_called_class() ) );
		$tablename = self::$table_prefix . end( $classname );
		return $wpdb->prefix . $tablename;
	}

	/**
	 * SQL Fetch from Table
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	private static function _fetch_sql( $key, $value ) {
		global $wpdb;
		$sql = sprintf( 'SELECT * FROM `%s` WHERE `%s` = %%s', esc_sql( self::_table() ), esc_sql( $key ) );
		return $wpdb->prepare( $sql, $value );
	}

	/**
	 * Get Rows with Pagination
	 *
	 * Security: $search must be a trusted SQL fragment (e.g. "WHERE col = %s") with placeholders;
	 * use $prepare_values for any user input. $sort and $order are validated to prevent SQL injection.
	 *
	 * @param int   $limit          Number of rows.
	 * @param int   $offset         Offset.
	 * @param string $search        Optional SQL fragment (e.g. WHERE clause with %s placeholders). Must not contain user input.
	 * @param string $sort          Column name for ORDER BY; only alphanumeric and underscore allowed, or empty for primary key.
	 * @param string $order         'ASC' or 'DESC' (case-insensitive).
	 * @param array $prepare_values Values for $search placeholders when using $wpdb->prepare().
	 *
	 * @return mixed
	 */
	public static function get_paged( $limit, $offset, $search = '', $sort = '', $order = '', $prepare_values = array() ) {
		global $wpdb;
		$table     = esc_sql( self::_table() );
		$sort_col  = esc_sql( empty( $sort ) ? static::$primary_key : $sort );
		$order_dir = esc_sql( empty( $order ) ? 'DESC' : $order );
		$limit  = absint( $limit );
		$offset = absint( $offset );

		if ( ! empty( $prepare_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "SELECT * FROM `{$table}` {$search} ORDER BY `{$sort_col}` {$order_dir} LIMIT %d OFFSET %d";
			return $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $prepare_values, array( $limit, $offset ) ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "SELECT * FROM `{$table}` {$search} ORDER BY `{$sort_col}` {$order_dir} LIMIT %d OFFSET %d";
		return $wpdb->get_results( $wpdb->prepare( $sql, $limit, $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get All Rows
	 */
	public static function get_all() {
		global $wpdb;
		return $wpdb->get_results(
			sprintf( 'SELECT * FROM `%s` ORDER BY `%s` DESC', esc_sql( self::_table() ), esc_sql( static::$primary_key ) ),
			ARRAY_A
		);
	}

	/**
	 * Get Total Count of Rows
	 * @return mixed
	 */
	public static function get_count() {
		global $wpdb;
		return $wpdb->get_var( sprintf( 'SELECT COUNT(*) FROM `%s`', esc_sql( self::_table() ) ) );
	}

	/**
	 * Get Row by ID
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public static function get( $key, $value ) {
		global $wpdb;
		return $wpdb->get_row( self::_fetch_sql( $key, $value ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Insert data to Table
	 * @param $data
	 */
	public static function insert( $data ) {
		global $wpdb;

		add_filter( 'query', array( self::class, 'wp_db_null_value' ) );

		if ( isset( $data['nonce'] ) ) {
			unset( $data['nonce'] );
		}

		$data = array_map(
			function( $item ) {
				$item = (string) $item; // Cast to string to prevent null values.
				if ( trim( $item, ' \'"' ) ) {
					return trim( $item );
				}
				return null;
			},
			$data
		);

		$wpdb->insert( self::_table(), $data );

		remove_filter( 'query', array( self::class, 'wp_db_null_value' ) );
	}

	/**
	 * Update data in Table with $where clause
	 * @param $data
	 * @param $where
	 */
	public static function update( $data, $where ) {
		global $wpdb;

		add_filter( 'query', array( self::class, 'wp_db_null_value' ) );

		if ( isset( $data['nonce'] ) ) {
			unset( $data['nonce'] );
		}

		$data = array_map(
			function( $item ) {
				$item = (string) $item; // Cast to string to prevent null values.
				if ( trim( $item, ' \'"' ) ) {
					return trim( $item );
				}
				return null;
			},
			$data
		);

		$wpdb->update( self::_table(), $data, $where );

		remove_filter( 'query', array( self::class, 'wp_db_null_value' ) );
	}

	/**
	 * Delete data from Table by ID
	 * @param $value
	 * @return mixed
	 */
	public static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM `%s` WHERE `%s` = %%s', esc_sql( self::_table() ), esc_sql( static::$primary_key ) );
		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}

	/**
	 * Delete data from Table Where
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public static function delete_where( $key, $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM `%s` WHERE `%s` = %%s', esc_sql( self::_table() ), esc_sql( $key ) );
		return $wpdb->query( $wpdb->prepare( $sql, $value ) );
	}

	/**
	 * Get Inserted data ID
	 * @return mixed
	 */
	public static function insert_id() {
		global $wpdb;
		return $wpdb->insert_id;
	}

	/**
	 * Replace the 'NULL' string with NULL
	 *
	 * @param  string $query
	 * @return string $query
	 */
	public static function wp_db_null_value( $query ) {
		return str_ireplace( "'NULL'", 'NULL', $query );
	}

	/**
	 * Show Last Query
	 * @return mixed
	 */
	public static function show_last_query() {
		global $wpdb;
		echo $wpdb->last_query; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Drop all bookit tables on uninstall
	 */
	public static function drop_tables() {
		global $wpdb;

		$sql = self::generate_drop_table_statement();
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Generate sql statement to remove all bookit tables by prefix
	 */
	private static function generate_drop_table_statement() {
		global $wpdb;

		$prefix = $wpdb->prefix . self::$table_prefix;
		$like   = '%' . $wpdb->esc_like( $prefix ) . '%';
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT CONCAT( 'DROP TABLE ', GROUP_CONCAT(DISTINCT( table_name) ) , ';' )  AS statement
				FROM information_schema.tables
				WHERE table_name LIKE %s",
				$like
			)
		);
	}
}
