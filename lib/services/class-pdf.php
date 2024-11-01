<?php
namespace QuadLayers\WCIM\Services;

use Exception;
use DOMDocument;
use Dompdf\Dompdf;
use Dompdf\Options;
use Intervention\Image\ImageManagerStatic as Image;

class PDF {

	public function create( $html, $size = 'a4', $orientation = 'portrait', $filename = null ) {

		// Check if the filename is set.
		if ( ! $filename ) {
			throw new Exception( esc_html__( 'Filename is required', 'wc-invoice-manager' ) );
		}

		// Check if the filename has the .pdf extension.
		if ( 'pdf' !== pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			throw new Exception( esc_html__( 'Filename must have the .pdf extension', 'wc-invoice-manager' ) );
		}

		$options = new Options();
		$options->set( 'isRemoteEnabled', true );
		$dompdf = new Dompdf( $options );
		$html   = $this->convert_png_images_to_jpg( $html );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( $size, (string) $orientation );
		$dompdf->render();
		return array(
			$dompdf->output(),
			$filename,
		);
	}

	private function convert_png_images_to_jpg( $html ) {
		// Load the HTML into a DOMDocument.
		$dom = new DOMDocument();
		@$dom->loadHTML( $html );

		// Find all img elements.
		$imgs = $dom->getElementsByTagName( 'img' );

		foreach ( $imgs as $img ) {
			$src = $img->getAttribute( 'src' );

			// Check if the image is a PNG.
			if ( strtolower( pathinfo( $src, PATHINFO_EXTENSION ) ) === 'png' ) {
				// Convert the image to a JPG using the Intervention Image library.
				$image     = Image::make( $src );
				$filename  = pathinfo( $src, PATHINFO_FILENAME ) . '.jpg';
				$temp_path = sys_get_temp_dir() . '/' . $filename;
				$image->save( $temp_path );

				// Convert the image to base64.
				$type   = pathinfo( $temp_path, PATHINFO_EXTENSION );
				$data   = file_get_contents( $temp_path );
				$base64 = "data:image/{$type};base64," . base64_encode( $data );

				// Replace the img src in the HTML with the base64 string.
				$img->setAttribute( 'src', $base64 );
			}
		}

		// Return the modified HTML.
		return $dom->saveHTML();
	}
}
