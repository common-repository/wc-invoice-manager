<?php
namespace QuadLayers\WCIM\Services;

class Zip {

	public function create( $pdf_list ) {
		// Create a temporary file.
		$zipname = wp_tempnam( 'zip' );
		$zip     = new \ZipArchive();
		if ( $zip->open( $zipname, \ZipArchive::CREATE ) !== true ) {
			exit( sprintf( esc_html__( 'Cannot open %s', 'wc-invoice-manager' ), esc_html( $zipname ) ) );
		}

		foreach ( $pdf_list as $pdf ) {
			list( $pdf, $filename ) = $pdf;
			$zip->addFromString( $filename, $pdf );
		}

		// Close the zip.
		$zip->close();

		// Clean the output buffer.
		if ( ob_get_level() ) {
			ob_clean();
			flush();
		}

		return $zipname;
	}

	public function delete( $zip_file ) {
		unlink( $zip_file );
	}
}
