<?php
namespace QuadLayers\WCIM\Services;

class File {

	protected static $uploads_dir = '/uploads/wc-invoice-manager';
	protected $wp_filesystem;

	public function __construct() {
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;
		}
		$this->wp_filesystem = $wp_filesystem;
	}

	public static function get_uploads_folder_path() {
		return self::$uploads_dir;
	}

	public static function get_uploads_path() {
		return WP_CONTENT_DIR . self::$uploads_dir;
	}

	public function create( $pdf, $filename ) {
		if ( ! is_dir( self::get_uploads_path() ) ) {
			$this->wp_filesystem->mkdir( self::get_uploads_path(), 0755 );
			$this->secure_folder_path( self::get_uploads_path() );
		}
		$this->wp_filesystem->put_contents( self::get_uploads_path() . '/' . $filename, $pdf );

		return array( $pdf, $filename );
	}

	public function get( $filename ) {
		if ( $this->wp_filesystem->exists( self::get_uploads_path() . '/' . $filename ) ) {
			return array( $this->wp_filesystem->get_contents( self::get_uploads_path() . '/' . $filename ), $filename );
		}
	}

	public function secure_folder_path( $path ) {
		// Create the folder if it doesn't exist.
		if ( ! is_dir( $path ) ) {
			$this->wp_filesystem->mkdir( $path, 0755, true );
		}

		// Set the permissions for the folder.
		$this->wp_filesystem->chmod( $path, 0755 );

		// Create or update the index.php file.
		$index_file    = trailingslashit( $path ) . 'index.php';
		$index_content = '<?php // Silence is golden.';
		$this->wp_filesystem->put_contents( $index_file, $index_content );

		// Create or update the .htaccess file.
		$htaccess_file    = trailingslashit( $path ) . '.htaccess';
		$htaccess_content = "Options -Indexes\nDeny from all";
		$this->wp_filesystem->put_contents( $htaccess_file, $htaccess_content );

		return $path;
	}

	public function delete( $filename ) {
		unlink( self::get_uploads_path() . '/' . $filename );
	}
}
