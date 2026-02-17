<?php

/**
 * Admin-specific functionality for plugin import flow.
 *
 * @package    Gn_Import_Export_Woocommerce_Shipping_Settings
 * @subpackage Gn_Import_Export_Woocommerce_Shipping_Settings/admin
 */
class Gn_Import_Export_Woocommerce_Shipping_Settings_Admin {

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	private $admin_page_hook_suffix;

	/**
	 * Constructor.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $version Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->admin_page_hook_suffix = '';
	}

	/**
	 * Add submenu.
	 */
	public function add_plugin_admin_menu() {
		$this->admin_page_hook_suffix = add_submenu_page(
			'woocommerce',
			__( 'Shipping Import', 'gn-import-export-woocommerce-shipping-settings' ),
			__( 'Shipping Import', 'gn-import-export-woocommerce-shipping-settings' ),
			'manage_woocommerce',
			$this->plugin_name,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render page.
	 */
	public function render_admin_page() {
		if ( ! $this->current_user_can_manage_imports() ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		global $wpdb;
		$current_db_prefix = $wpdb->prefix;

		require plugin_dir_path( __FILE__ ) . 'partials/gn-import-export-woocommerce-shipping-settings-admin-display.php';
	}

	/**
	 * Enqueue admin CSS only on plugin page.
	 *
	 * @param string $hook_suffix Screen hook suffix.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( ! $this->is_plugin_admin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/gn-import-export-woocommerce-shipping-settings-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Enqueue admin JS only on plugin page.
	 *
	 * @param string $hook_suffix Screen hook suffix.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( ! $this->is_plugin_admin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/gn-import-export-woocommerce-shipping-settings-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);
	}

	/**
	 * Handle import POST.
	 */
	public function handle_import_action() {
		if ( ! $this->current_user_can_manage_imports() ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		check_admin_referer( 'gn_import_export_woocommerce_shipping_settings_import', 'gn_import_export_woocommerce_shipping_settings_nonce' );

		$redirect_url = add_query_arg(
			array(
				'page' => $this->plugin_name,
			),
			admin_url( 'admin.php' )
		);

		if ( empty( $_FILES['gn_ie_wcss_dump_file'] ) || ! is_array( $_FILES['gn_ie_wcss_dump_file'] ) ) {
			$this->set_admin_notice( 'error', __( 'No file was uploaded.', 'gn-import-export-woocommerce-shipping-settings' ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$uploaded_dump = $this->store_uploaded_dump_file( $_FILES['gn_ie_wcss_dump_file'] );
		if ( is_wp_error( $uploaded_dump ) ) {
			$this->set_admin_notice( 'error', $uploaded_dump->get_error_message() );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$import_result = $this->import_dump_file( $uploaded_dump['path'], $uploaded_dump['extension'] );

		if ( file_exists( $uploaded_dump['path'] ) ) {
			@unlink( $uploaded_dump['path'] );
		}

		if ( is_wp_error( $import_result ) ) {
			$this->set_admin_notice( 'error', $import_result->get_error_message() );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$details = array(
			sprintf(
				/* translators: %s: source dump prefix. */
				__( 'Detected source prefix: %s', 'gn-import-export-woocommerce-shipping-settings' ),
				'' !== $import_result['source_prefix'] ? $import_result['source_prefix'] : __( '(not found)', 'gn-import-export-woocommerce-shipping-settings' )
			),
			sprintf(
				/* translators: %s: target prefix. */
				__( 'Target prefix used: %s', 'gn-import-export-woocommerce-shipping-settings' ),
				$import_result['target_prefix']
			),
			sprintf(
				/* translators: %d: count. */
				__( 'Imported zones: %d', 'gn-import-export-woocommerce-shipping-settings' ),
				$import_result['counts']['zones']
			),
			sprintf(
				/* translators: %d: count. */
				__( 'Imported zone methods: %d', 'gn-import-export-woocommerce-shipping-settings' ),
				$import_result['counts']['zone_methods']
			),
			sprintf(
				/* translators: %d: count. */
				__( 'Imported zone locations: %d', 'gn-import-export-woocommerce-shipping-settings' ),
				$import_result['counts']['zone_locations']
			),
			sprintf(
				/* translators: %d: count. */
				__( 'Imported shipping settings: %d', 'gn-import-export-woocommerce-shipping-settings' ),
				$import_result['counts']['options']
			),
			sprintf(
				/* translators: %s: backup file path. */
				__( 'Backup file: %s', 'gn-import-export-woocommerce-shipping-settings' ),
				$import_result['backup_file']
			),
		);

		$this->set_admin_notice(
			'success',
			__( 'Shipping settings import completed successfully.', 'gn-import-export-woocommerce-shipping-settings' ),
			$details
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render stored notice.
	 */
	public function maybe_display_admin_notice() {
		if ( ! $this->is_plugin_admin_page() ) {
			return;
		}

		$notice = get_transient( $this->get_admin_notice_transient_key() );
		if ( ! is_array( $notice ) || empty( $notice['message'] ) ) {
			return;
		}

		delete_transient( $this->get_admin_notice_transient_key() );

		$type = isset( $notice['type'] ) ? sanitize_key( $notice['type'] ) : 'info';
		if ( ! in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ) {
			$type = 'info';
		}

		echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
		echo '<p>' . esc_html( $notice['message'] ) . '</p>';
		if ( ! empty( $notice['details'] ) && is_array( $notice['details'] ) ) {
			echo '<ul>';
			foreach ( $notice['details'] as $detail ) {
				echo '<li>' . esc_html( $detail ) . '</li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}

	/**
	 * Import workflow.
	 *
	 * @param string $file_path Temp file path.
	 * @param string $extension File extension.
	 * @return array|WP_Error
	 */
	private function import_dump_file( $file_path, $extension ) {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 0 );
		}

		$shipping_data = $this->parse_shipping_data_from_dump( $file_path, $extension );
		if ( is_wp_error( $shipping_data ) ) {
			return $shipping_data;
		}

		$backup_result = $this->create_database_backup();
		if ( is_wp_error( $backup_result ) ) {
			return $backup_result;
		}

		$import_result = $this->import_parsed_shipping_data( $shipping_data );
		if ( is_wp_error( $import_result ) ) {
			return $import_result;
		}

		$import_result['backup_file'] = $backup_result['path'];
		$import_result['source_prefix'] = $shipping_data['source_prefix'];

		return $import_result;
	}

	/**
	 * Parse dump and extract only shipping rows.
	 *
	 * @param string $file_path File path.
	 * @param string $extension Extension.
	 * @return array|WP_Error
	 */
	private function parse_shipping_data_from_dump( $file_path, $extension ) {
		$reader = $this->open_dump_reader( $file_path, $extension );
		if ( is_wp_error( $reader ) ) {
			return $reader;
		}

		$parsed_data = array(
			'source_prefix'  => '',
			'zones'          => array(),
			'zone_methods'   => array(),
			'zone_locations' => array(),
			'options'        => array(),
		);

		$current_block = '';

		while ( true ) {
			$line = $this->read_dump_line( $reader );
			if ( false === $line ) {
				break;
			}

			$trimmed_line = trim( $line );
			if ( '' === $trimmed_line ) {
				continue;
			}

			if ( '' === $parsed_data['source_prefix'] ) {
				$parsed_data['source_prefix'] = $this->detect_source_prefix_from_line( $trimmed_line );
			}

			if ( preg_match( '/^INSERT INTO\s+`([^`]+)`(?:\s*\(([^)]*)\))?\s+VALUES\s*(.*)$/i', $trimmed_line, $insert_match ) ) {
				$table_name = $insert_match[1];
				$inline_data = isset( $insert_match[3] ) ? trim( $insert_match[3] ) : '';

				if ( $this->table_name_has_suffix( $table_name, 'woocommerce_shipping_zone_locations' ) ) {
					$current_block = 'zone_locations';
				} elseif ( $this->table_name_has_suffix( $table_name, 'woocommerce_shipping_zone_methods' ) ) {
					$current_block = 'zone_methods';
				} elseif ( $this->table_name_has_suffix( $table_name, 'woocommerce_shipping_zones' ) ) {
					$current_block = 'zones';
				} elseif ( $this->table_name_has_suffix( $table_name, 'options' ) ) {
					$current_block = 'options';
				} else {
					$current_block = '';
				}

				if ( '' !== $current_block && '' !== $inline_data ) {
					$this->capture_parsed_row_from_line( $current_block, $inline_data, $parsed_data );
				}

				if ( ';' === substr( $trimmed_line, -1 ) ) {
					$current_block = '';
				}

				continue;
			}

			if ( '' !== $current_block ) {
				$this->capture_parsed_row_from_line( $current_block, $trimmed_line, $parsed_data );

				if ( ';' === substr( $trimmed_line, -1 ) ) {
					$current_block = '';
				}
			}
		}

		$this->close_dump_reader( $reader );

		if ( empty( $parsed_data['zones'] ) || empty( $parsed_data['zone_methods'] ) || empty( $parsed_data['zone_locations'] ) ) {
			return new WP_Error(
				'gn_ie_wcss_invalid_dump',
				__( 'Could not find complete WooCommerce shipping data in the uploaded dump.', 'gn-import-export-woocommerce-shipping-settings' )
			);
		}

		$parsed_data['options'] = array_values( $parsed_data['options'] );

		return $parsed_data;
	}

	/**
	 * Import parsed rows into current DB prefix.
	 *
	 * @param array $parsed_data Parsed rows.
	 * @return array|WP_Error
	 */
	private function import_parsed_shipping_data( $parsed_data ) {
		global $wpdb;

		$zones_table = $wpdb->prefix . 'woocommerce_shipping_zones';
		$zone_methods_table = $wpdb->prefix . 'woocommerce_shipping_zone_methods';
		$zone_locations_table = $wpdb->prefix . 'woocommerce_shipping_zone_locations';
		$options_table = $wpdb->prefix . 'options';

		$transaction_started = ( false !== $wpdb->query( 'START TRANSACTION' ) );
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );

		$reset_queries = array(
			'TRUNCATE TABLE `' . $this->escape_identifier( $zone_locations_table ) . '`',
			'TRUNCATE TABLE `' . $this->escape_identifier( $zone_methods_table ) . '`',
			'TRUNCATE TABLE `' . $this->escape_identifier( $zones_table ) . '`',
			"DELETE FROM `{$this->escape_identifier( $options_table )}` WHERE `option_name` REGEXP '^woocommerce_[A-Za-z0-9_]+_[0-9]+_settings$'",
		);

		foreach ( $reset_queries as $query ) {
			if ( false === $wpdb->query( $query ) ) {
				$this->rollback_import( $transaction_started );
				return new WP_Error(
					'gn_ie_wcss_import_reset_failed',
					sprintf(
						/* translators: %s: DB error. */
						__( 'Import failed while resetting existing data: %s', 'gn-import-export-woocommerce-shipping-settings' ),
						$wpdb->last_error
					)
				);
			}
		}

		foreach ( $parsed_data['zones'] as $zone_row ) {
			$result = $wpdb->insert(
				$zones_table,
				array(
					'zone_id'    => $zone_row['zone_id'],
					'zone_name'  => $zone_row['zone_name'],
					'zone_order' => $zone_row['zone_order'],
				),
				array( '%d', '%s', '%d' )
			);

			if ( false === $result ) {
				$this->rollback_import( $transaction_started );
				return new WP_Error( 'gn_ie_wcss_insert_zones_failed', $wpdb->last_error );
			}
		}

		foreach ( $parsed_data['zone_methods'] as $method_row ) {
			$result = $wpdb->insert(
				$zone_methods_table,
				array(
					'zone_id'      => $method_row['zone_id'],
					'instance_id'  => $method_row['instance_id'],
					'method_id'    => $method_row['method_id'],
					'method_order' => $method_row['method_order'],
					'is_enabled'   => $method_row['is_enabled'],
				),
				array( '%d', '%d', '%s', '%d', '%d' )
			);

			if ( false === $result ) {
				$this->rollback_import( $transaction_started );
				return new WP_Error( 'gn_ie_wcss_insert_methods_failed', $wpdb->last_error );
			}
		}

		foreach ( $parsed_data['zone_locations'] as $location_row ) {
			$result = $wpdb->insert(
				$zone_locations_table,
				array(
					'location_id'   => $location_row['location_id'],
					'zone_id'       => $location_row['zone_id'],
					'location_code' => $location_row['location_code'],
					'location_type' => $location_row['location_type'],
				),
				array( '%d', '%d', '%s', '%s' )
			);

			if ( false === $result ) {
				$this->rollback_import( $transaction_started );
				return new WP_Error( 'gn_ie_wcss_insert_locations_failed', $wpdb->last_error );
			}
		}

		foreach ( $parsed_data['options'] as $option_row ) {
			$result = $wpdb->replace(
				$options_table,
				array(
					'option_name'  => $option_row['option_name'],
					'option_value' => $option_row['option_value'],
					'autoload'     => $option_row['autoload'],
				),
				array( '%s', '%s', '%s' )
			);

			if ( false === $result ) {
				$this->rollback_import( $transaction_started );
				return new WP_Error( 'gn_ie_wcss_insert_options_failed', $wpdb->last_error );
			}
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );
		if ( $transaction_started && false === $wpdb->query( 'COMMIT' ) ) {
			$this->rollback_import( $transaction_started );
			return new WP_Error( 'gn_ie_wcss_import_commit_failed', __( 'Import failed while finalizing DB transaction.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		if ( function_exists( 'wc_delete_shipping_zone_transients' ) ) {
			wc_delete_shipping_zone_transients();
		}

		return array(
			'target_prefix' => $wpdb->prefix,
			'counts'        => array(
				'zones'          => count( $parsed_data['zones'] ),
				'zone_methods'   => count( $parsed_data['zone_methods'] ),
				'zone_locations' => count( $parsed_data['zone_locations'] ),
				'options'        => count( $parsed_data['options'] ),
			),
		);
	}

	/**
	 * Backup all tables under current DB prefix.
	 *
	 * @return array|WP_Error
	 */
	private function create_database_backup() {
		global $wpdb;

		$uploads = wp_upload_dir();
		if ( ! empty( $uploads['error'] ) ) {
			return new WP_Error( 'gn_ie_wcss_backup_upload_dir_error', $uploads['error'] );
		}

		$backup_directory = trailingslashit( $uploads['basedir'] ) . $this->plugin_name . '/backups';
		if ( ! wp_mkdir_p( $backup_directory ) ) {
			return new WP_Error( 'gn_ie_wcss_backup_directory_error', __( 'Could not create backup directory.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		$use_gzip = function_exists( 'gzopen' );
		$backup_filename = 'db-backup-' . gmdate( 'Ymd-His' ) . ( $use_gzip ? '.sql.gz' : '.sql' );
		$backup_path = trailingslashit( $backup_directory ) . $backup_filename;

		$tables = $wpdb->get_col(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			)
		);

		if ( empty( $tables ) ) {
			return new WP_Error( 'gn_ie_wcss_backup_no_tables', __( 'No tables found for current prefix.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		$handle = $use_gzip ? gzopen( $backup_path, 'wb9' ) : fopen( $backup_path, 'wb' );
		if ( false === $handle ) {
			return new WP_Error( 'gn_ie_wcss_backup_open_failed', __( 'Could not open backup file for writing.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		$write_result = $this->write_backup_chunk(
			$handle,
			$use_gzip,
			'-- Backup generated by ' . $this->plugin_name . "\n-- UTC: " . gmdate( 'Y-m-d H:i:s' ) . "\nSET FOREIGN_KEY_CHECKS=0;\n"
		);
		if ( is_wp_error( $write_result ) ) {
			$this->close_backup_handle( $handle, $use_gzip );
			return $write_result;
		}

		foreach ( $tables as $table_name ) {
			$table_identifier = '`' . $this->escape_identifier( $table_name ) . '`';
			$create_table = $wpdb->get_row( "SHOW CREATE TABLE {$table_identifier}", ARRAY_N );
			if ( empty( $create_table[1] ) ) {
				continue;
			}

			$sql  = "\n--\n-- Table structure for table {$table_identifier}\n--\n\n";
			$sql .= "DROP TABLE IF EXISTS {$table_identifier};\n";
			$sql .= $create_table[1] . ";\n\n";

			$write_result = $this->write_backup_chunk( $handle, $use_gzip, $sql );
			if ( is_wp_error( $write_result ) ) {
				$this->close_backup_handle( $handle, $use_gzip );
				return $write_result;
			}

			$offset = 0;
			$chunk_size = 200;

			while ( true ) {
				$rows = $wpdb->get_results( "SELECT * FROM {$table_identifier} LIMIT {$offset}, {$chunk_size}", ARRAY_N );
				if ( empty( $rows ) ) {
					break;
				}

				$value_rows = array();
				foreach ( $rows as $row ) {
					$escaped_values = array();
					foreach ( $row as $value ) {
						$escaped_values[] = $this->database_value_to_sql_literal( $value );
					}
					$value_rows[] = '(' . implode( ',', $escaped_values ) . ')';
				}

				$write_result = $this->write_backup_chunk(
					$handle,
					$use_gzip,
					'INSERT INTO ' . $table_identifier . " VALUES\n" . implode( ",\n", $value_rows ) . ";\n"
				);

				if ( is_wp_error( $write_result ) ) {
					$this->close_backup_handle( $handle, $use_gzip );
					return $write_result;
				}

				$offset += count( $rows );
				if ( count( $rows ) < $chunk_size ) {
					break;
				}
			}
		}

		$this->close_backup_handle( $handle, $use_gzip );

		return array(
			'path' => $backup_path,
		);
	}

	/**
	 * Rollback if needed.
	 *
	 * @param bool $transaction_started Transaction flag.
	 */
	private function rollback_import( $transaction_started ) {
		global $wpdb;

		if ( $transaction_started ) {
			$wpdb->query( 'ROLLBACK' );
		}

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );
	}

	/**
	 * SQL literal from DB value.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private function database_value_to_sql_literal( $value ) {
		if ( null === $value ) {
			return 'NULL';
		}

		$string_value = (string) $value;
		$string_value = str_replace(
			array( "\\", "\0", "\n", "\r", "'", '"', chr( 26 ) ),
			array( "\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z" ),
			$string_value
		);

		return "'" . $string_value . "'";
	}

	/**
	 * Write backup chunk.
	 *
	 * @param resource $handle File handle.
	 * @param bool     $use_gzip Gzip flag.
	 * @param string   $chunk SQL chunk.
	 * @return true|WP_Error
	 */
	private function write_backup_chunk( $handle, $use_gzip, $chunk ) {
		$result = $use_gzip ? gzwrite( $handle, $chunk ) : fwrite( $handle, $chunk );
		if ( false === $result ) {
			return new WP_Error( 'gn_ie_wcss_backup_write_failed', __( 'Failed writing to backup file.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		return true;
	}

	/**
	 * Close backup handle.
	 *
	 * @param resource $handle File handle.
	 * @param bool     $use_gzip Gzip flag.
	 */
	private function close_backup_handle( $handle, $use_gzip ) {
		if ( $use_gzip ) {
			gzclose( $handle );
		} else {
			fclose( $handle );
		}
	}

	/**
	 * Capture parsed row for current block.
	 *
	 * @param string $block_name Block key.
	 * @param string $line Raw line.
	 * @param array  $parsed Parsed data.
	 */
	private function capture_parsed_row_from_line( $block_name, $line, &$parsed ) {
		if ( 'zone_locations' === $block_name ) {
			$row = $this->parse_zone_locations_row( $line );
			if ( false !== $row ) {
				$parsed['zone_locations'][] = $row;
			}
			return;
		}

		if ( 'zone_methods' === $block_name ) {
			$row = $this->parse_zone_methods_row( $line );
			if ( false !== $row ) {
				$parsed['zone_methods'][] = $row;
			}
			return;
		}

		if ( 'zones' === $block_name ) {
			$row = $this->parse_zones_row( $line );
			if ( false !== $row ) {
				$parsed['zones'][] = $row;
			}
			return;
		}

		if ( 'options' === $block_name ) {
			$row = $this->parse_shipping_option_row( $line );
			if ( false !== $row ) {
				$parsed['options'][ $row['option_name'] ] = $row;
			}
		}
	}

	/**
	 * Parse zone locations tuple.
	 *
	 * @param string $line SQL tuple line.
	 * @return array|false
	 */
	private function parse_zone_locations_row( $line ) {
		$tuple = $this->normalize_sql_tuple_line( $line );
		if ( '' === $tuple ) {
			return false;
		}

		if ( ! preg_match( '/^\(\s*(\d+)\s*,\s*(\d+)\s*,\s*\'((?:\\\\.|[^\'\\\\])*)\'\s*,\s*\'((?:\\\\.|[^\'\\\\])*)\'\s*\)$/', $tuple, $matches ) ) {
			return false;
		}

		return array(
			'location_id'   => (int) $matches[1],
			'zone_id'       => (int) $matches[2],
			'location_code' => $this->unescape_mysql_string( $matches[3] ),
			'location_type' => $this->unescape_mysql_string( $matches[4] ),
		);
	}

	/**
	 * Parse zone methods tuple.
	 *
	 * @param string $line SQL tuple line.
	 * @return array|false
	 */
	private function parse_zone_methods_row( $line ) {
		$tuple = $this->normalize_sql_tuple_line( $line );
		if ( '' === $tuple ) {
			return false;
		}

		if ( ! preg_match( '/^\(\s*(\d+)\s*,\s*(\d+)\s*,\s*\'((?:\\\\.|[^\'\\\\])*)\'\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/', $tuple, $matches ) ) {
			return false;
		}

		return array(
			'zone_id'      => (int) $matches[1],
			'instance_id'  => (int) $matches[2],
			'method_id'    => $this->unescape_mysql_string( $matches[3] ),
			'method_order' => (int) $matches[4],
			'is_enabled'   => (int) $matches[5],
		);
	}

	/**
	 * Parse zones tuple.
	 *
	 * @param string $line SQL tuple line.
	 * @return array|false
	 */
	private function parse_zones_row( $line ) {
		$tuple = $this->normalize_sql_tuple_line( $line );
		if ( '' === $tuple ) {
			return false;
		}

		if ( ! preg_match( '/^\(\s*(\d+)\s*,\s*\'((?:\\\\.|[^\'\\\\])*)\'\s*,\s*(\d+)\s*\)$/', $tuple, $matches ) ) {
			return false;
		}

		return array(
			'zone_id'    => (int) $matches[1],
			'zone_name'  => $this->unescape_mysql_string( $matches[2] ),
			'zone_order' => (int) $matches[3],
		);
	}

	/**
	 * Parse options tuple and keep only shipping instance settings.
	 *
	 * @param string $line SQL tuple line.
	 * @return array|false
	 */
	private function parse_shipping_option_row( $line ) {
		$tuple = $this->normalize_sql_tuple_line( $line );
		if ( '' === $tuple ) {
			return false;
		}

		if ( ! preg_match( '/^\(\s*(?:\d+\s*,\s*)?\'((?:\\\\.|[^\'\\\\])*)\'\s*,\s*\'((?:\\\\.|[^\'\\\\])*)\'\s*,\s*\'((?:\\\\.|[^\'\\\\])*)\'\s*\)$/', $tuple, $matches ) ) {
			return false;
		}

		$option_name = $this->unescape_mysql_string( $matches[1] );
		if ( ! preg_match( '/^woocommerce_[a-z0-9_]+_[0-9]+_settings$/i', $option_name ) ) {
			return false;
		}

		return array(
			'option_name'  => $option_name,
			'option_value' => $this->unescape_mysql_string( $matches[2] ),
			'autoload'     => $this->unescape_mysql_string( $matches[3] ),
		);
	}

	/**
	 * Normalize line to tuple.
	 *
	 * @param string $line Raw line.
	 * @return string
	 */
	private function normalize_sql_tuple_line( $line ) {
		$trimmed = trim( $line );
		if ( '' === $trimmed || '(' !== substr( $trimmed, 0, 1 ) ) {
			return '';
		}

		return rtrim( $trimmed, ",;\t\n\r " );
	}

	/**
	 * Unescape MySQL dump string.
	 *
	 * @param string $value Escaped value.
	 * @return string
	 */
	private function unescape_mysql_string( $value ) {
		return strtr(
			$value,
			array(
				'\\\\' => '\\',
				'\\0'  => "\0",
				'\\n'  => "\n",
				'\\r'  => "\r",
				'\\Z'  => chr( 26 ),
				"\\'"  => "'",
				'\\"'  => '"',
			)
		);
	}

	/**
	 * Save uploaded file to temp path.
	 *
	 * @param array $uploaded_file Uploaded file.
	 * @return array|WP_Error
	 */
	private function store_uploaded_dump_file( $uploaded_file ) {
		if ( empty( $uploaded_file['tmp_name'] ) || ! isset( $uploaded_file['error'] ) ) {
			return new WP_Error( 'gn_ie_wcss_upload_missing', __( 'Uploaded file is invalid.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		$upload_error = (int) $uploaded_file['error'];
		if ( UPLOAD_ERR_OK !== $upload_error ) {
			return new WP_Error( 'gn_ie_wcss_upload_error', $this->get_upload_error_message( $upload_error ) );
		}

		$original_name = isset( $uploaded_file['name'] ) ? sanitize_file_name( $uploaded_file['name'] ) : '';
		$extension = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );
		if ( ! in_array( $extension, array( 'sql', 'zip', 'gz' ), true ) ) {
			return new WP_Error( 'gn_ie_wcss_upload_extension', __( 'Unsupported file format. Upload a .sql, .zip, or .gz dump.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$temp_path = wp_tempnam( $original_name );
		if ( empty( $temp_path ) ) {
			return new WP_Error( 'gn_ie_wcss_upload_temp_file', __( 'Could not create a temporary file for upload.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		$moved = @move_uploaded_file( $uploaded_file['tmp_name'], $temp_path );
		if ( ! $moved ) {
			$moved = @rename( $uploaded_file['tmp_name'], $temp_path );
		}

		if ( ! $moved ) {
			return new WP_Error( 'gn_ie_wcss_upload_move_failed', __( 'Could not move uploaded file to temporary storage.', 'gn-import-export-woocommerce-shipping-settings' ) );
		}

		return array(
			'path'      => $temp_path,
			'extension' => $extension,
		);
	}

	/**
	 * Upload error helper.
	 *
	 * @param int $error_code Upload error code.
	 * @return string
	 */
	private function get_upload_error_message( $error_code ) {
		switch ( $error_code ) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return __( 'The uploaded file exceeds the configured upload size limit.', 'gn-import-export-woocommerce-shipping-settings' );
			case UPLOAD_ERR_PARTIAL:
				return __( 'The uploaded file was only partially uploaded.', 'gn-import-export-woocommerce-shipping-settings' );
			case UPLOAD_ERR_NO_FILE:
				return __( 'No file was uploaded.', 'gn-import-export-woocommerce-shipping-settings' );
			case UPLOAD_ERR_NO_TMP_DIR:
				return __( 'Missing a temporary folder on the server.', 'gn-import-export-woocommerce-shipping-settings' );
			case UPLOAD_ERR_CANT_WRITE:
				return __( 'Failed to write uploaded file to disk.', 'gn-import-export-woocommerce-shipping-settings' );
			case UPLOAD_ERR_EXTENSION:
				return __( 'A PHP extension stopped the file upload.', 'gn-import-export-woocommerce-shipping-settings' );
			default:
				return __( 'Unknown upload error.', 'gn-import-export-woocommerce-shipping-settings' );
		}
	}

	/**
	 * Open line reader for sql/gz/zip.
	 *
	 * @param string $file_path File path.
	 * @param string $extension Extension.
	 * @return array|WP_Error
	 */
	private function open_dump_reader( $file_path, $extension ) {
		if ( 'sql' === $extension ) {
			$handle = fopen( $file_path, 'rb' );
			if ( false === $handle ) {
				return new WP_Error( 'gn_ie_wcss_open_sql_failed', __( 'Could not open uploaded SQL file.', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			return array(
				'type'   => 'sql',
				'handle' => $handle,
			);
		}

		if ( 'gz' === $extension ) {
			if ( ! function_exists( 'gzopen' ) ) {
				return new WP_Error( 'gn_ie_wcss_open_gz_unsupported', __( 'This server does not support .gz files (gzip extension missing).', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			$handle = gzopen( $file_path, 'rb' );
			if ( false === $handle ) {
				return new WP_Error( 'gn_ie_wcss_open_gz_failed', __( 'Could not open uploaded GZ file.', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			return array(
				'type'   => 'gz',
				'handle' => $handle,
			);
		}

		if ( 'zip' === $extension ) {
			if ( ! class_exists( 'ZipArchive' ) ) {
				return new WP_Error( 'gn_ie_wcss_open_zip_unsupported', __( 'This server does not support .zip files (ZipArchive missing).', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			$zip_archive = new ZipArchive();
			$open_result = $zip_archive->open( $file_path );
			if ( true !== $open_result ) {
				return new WP_Error( 'gn_ie_wcss_open_zip_failed', __( 'Could not open uploaded ZIP file.', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			$sql_entry_name = $this->select_sql_entry_from_zip( $zip_archive );
			if ( '' === $sql_entry_name ) {
				$zip_archive->close();
				return new WP_Error( 'gn_ie_wcss_open_zip_no_sql', __( 'ZIP archive does not contain a .sql file.', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			$handle = $zip_archive->getStream( $sql_entry_name );
			if ( false === $handle ) {
				$zip_archive->close();
				return new WP_Error( 'gn_ie_wcss_open_zip_stream_failed', __( 'Could not read SQL file from ZIP archive.', 'gn-import-export-woocommerce-shipping-settings' ) );
			}

			return array(
				'type'       => 'zip',
				'handle'     => $handle,
				'zip_handle' => $zip_archive,
			);
		}

		return new WP_Error( 'gn_ie_wcss_open_extension_invalid', __( 'Unsupported dump file format.', 'gn-import-export-woocommerce-shipping-settings' ) );
	}

	/**
	 * Read one line from reader.
	 *
	 * @param array $reader Reader data.
	 * @return string|false
	 */
	private function read_dump_line( $reader ) {
		if ( 'gz' === $reader['type'] ) {
			return gzgets( $reader['handle'] );
		}

		return fgets( $reader['handle'] );
	}

	/**
	 * Close reader.
	 *
	 * @param array $reader Reader data.
	 */
	private function close_dump_reader( $reader ) {
		if ( 'gz' === $reader['type'] ) {
			gzclose( $reader['handle'] );
			return;
		}

		fclose( $reader['handle'] );
		if ( isset( $reader['zip_handle'] ) && $reader['zip_handle'] instanceof ZipArchive ) {
			$reader['zip_handle']->close();
		}
	}

	/**
	 * Select largest .sql entry inside zip.
	 *
	 * @param ZipArchive $zip_archive Zip handle.
	 * @return string
	 */
	private function select_sql_entry_from_zip( $zip_archive ) {
		$selected_entry = '';
		$selected_size = -1;

		for ( $index = 0; $index < $zip_archive->numFiles; $index++ ) {
			$entry_stat = $zip_archive->statIndex( $index );
			if ( empty( $entry_stat['name'] ) ) {
				continue;
			}

			$entry_name = $entry_stat['name'];
			if ( '/' === substr( $entry_name, -1 ) ) {
				continue;
			}

			if ( ! preg_match( '/\.sql$/i', $entry_name ) ) {
				continue;
			}

			$entry_size = isset( $entry_stat['size'] ) ? (int) $entry_stat['size'] : 0;
			if ( $entry_size > $selected_size ) {
				$selected_size = $entry_size;
				$selected_entry = $entry_name;
			}
		}

		return $selected_entry;
	}

	/**
	 * Detect source prefix from shipping table line.
	 *
	 * @param string $line SQL line.
	 * @return string
	 */
	private function detect_source_prefix_from_line( $line ) {
		if ( preg_match( '/`([a-zA-Z0-9_]+)woocommerce_shipping_zone_locations`/', $line, $matches ) ) {
			return $matches[1];
		}

		if ( preg_match( '/`([a-zA-Z0-9_]+)woocommerce_shipping_zone_methods`/', $line, $matches ) ) {
			return $matches[1];
		}

		if ( preg_match( '/`([a-zA-Z0-9_]+)woocommerce_shipping_zones`/', $line, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	/**
	 * Check suffix for table.
	 *
	 * @param string $table_name Full table name.
	 * @param string $suffix Suffix.
	 * @return bool
	 */
	private function table_name_has_suffix( $table_name, $suffix ) {
		return $suffix === substr( $table_name, -strlen( $suffix ) );
	}

	/**
	 * Escape identifier.
	 *
	 * @param string $identifier Identifier.
	 * @return string
	 */
	private function escape_identifier( $identifier ) {
		return str_replace( '`', '``', $identifier );
	}

	/**
	 * Check capability.
	 *
	 * @return bool
	 */
	private function current_user_can_manage_imports() {
		return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Identify plugin admin page.
	 *
	 * @param string $hook_suffix Optional hook suffix.
	 * @return bool
	 */
	private function is_plugin_admin_page( $hook_suffix = '' ) {
		$page = '';
		if ( isset( $_GET['page'] ) ) {
			$page = sanitize_key( wp_unslash( $_GET['page'] ) );
		}

		if ( $this->plugin_name === $page ) {
			return true;
		}

		return '' !== $hook_suffix && '' !== $this->admin_page_hook_suffix && $hook_suffix === $this->admin_page_hook_suffix;
	}

	/**
	 * Set notice for redirect.
	 *
	 * @param string $type Notice type.
	 * @param string $message Message.
	 * @param array  $details Optional detail list.
	 */
	private function set_admin_notice( $type, $message, $details = array() ) {
		set_transient(
			$this->get_admin_notice_transient_key(),
			array(
				'type'    => $type,
				'message' => $message,
				'details' => $details,
			),
			60
		);
	}

	/**
	 * Transient key helper.
	 *
	 * @return string
	 */
	private function get_admin_notice_transient_key() {
		return 'gn_ie_wcss_notice_' . get_current_user_id();
	}
}
