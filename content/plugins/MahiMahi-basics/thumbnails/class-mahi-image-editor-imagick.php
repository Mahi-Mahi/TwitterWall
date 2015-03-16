<?php

class Mahi_Image_Editor_Imagick extends WP_Image_Editor_Imagick {

	function set_progressive() {
		$this->image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
	}

	function wp_constrain_dimensions( $width, $height) {
		return wp_constrain_dimensions( $this->size['width'], $this->size['height'], $width, $height);
	}

	/**
	 * Scales current image.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param int $max_w
	 * @param int $max_h
	 * @param boolean $crop
	 * @return boolean|WP_Error
	 */
	public function scale( $max_w, $max_h, $bgcolor, $r = array() ) {

		/*
		if ( ( $this->size['width'] < $max_w ) && ( $this->size['height'] < $max_h ) )
			return true;
		*/

		/*
		$format = $this->image->getImageFormat();
		if ($format == 'GIF') {
			return $this->image;
			$resized = $this->_scaleGif( $max_w, $max_h, $bgcolor, $r );
		}
		else {
			$resized = $this->_scale( $max_w, $max_h, $bgcolor, $r );
		}
		*/

		$resized = $this->_scale( $max_w, $max_h, $bgcolor, $r );

		if ( get_class( $resized ) == 'Imagick' ) {
			$this->image = $resized;
			return true;
		}
		elseif ( is_wp_error( $resized ) ) {
			logr(get_caller());
			logr($resized);
			return $resized;
		}

		return new WP_Error( 'image_resize_error', __('Image resize failed.'), $this->file );
	}

	protected function _scale( $max_w, $max_h, $bgcolor, $r = array()) {

		list($dest_w, $dest_h) = wp_constrain_dimensions( $this->size['width'], $this->size['height'], $max_w, $max_h );

		if ( $r['transparent'] ):
			$background = new ImagickPixel("rgba(255,255,255,0)");
		else:
			preg_match("#\#?([\dA-F]{2})([\dA-F]{2})([\dA-F]{2})#i", $bgcolor, $c);
			$background = new ImagickPixel("rgba(".hexdec($c[1]).", ".hexdec($c[2]).", ".hexdec($c[3]).",1)");
		endif;

		$resized = new Imagick();
		$resized->newImage($max_w, $max_h, $background);

		$this->resize( $dest_w, $dest_h );
		$resized->compositeImage($this->image, Imagick::COMPOSITE_DEFAULT,  ($max_w - $dest_w) /2, ($max_h - $dest_h) / 2);

		$this->update_size( $dst_w, $dst_h );
		return $resized;

		return new WP_Error( 'image_resize_error', __('Image resize failed.'), $this->file );
	}

	// Animated GIF : not working


	protected function _scaleGif( $max_w, $max_h, $bgcolor, $r = array()) {


		list($dest_w, $dest_h) = wp_constrain_dimensions( $this->size['width'], $this->size['height'], $max_w, $max_h );

		if ( true || $r['transparent'] ):
			$background = new ImagickPixel("rgba(255,255,255,0)");
		else:
			preg_match("#\#?([\dA-F]{2})([\dA-F]{2})([\dA-F]{2})#i", $bgcolor, $c);
			$background = new ImagickPixel("rgba(".hexdec($c[1]).", ".hexdec($c[2]).", ".hexdec($c[3]).",1)");
		endif;

		$resized = new Imagick();
		$resized->newImage($max_w, $max_h, $background);
		$resized->setFormat("gif");

		$frames = $this->image->coalesceImages();
		foreach($frames as $frame):
			$frame->scaleImage( $dest_w, $dest_h, 1 );
			$frame->setImagePage($max_w, $max_h, 0, 0);
			// $resized->addImage($frame);

			$f = new Imagick();
			$f->newImage($max_w, $max_h, $background);
			$f->compositeImage($frame, Imagick::COMPOSITE_DEFAULT,  ($max_w - $dest_w) /2, ($max_h - $dest_h) / 2);
			$f->setImageDelay(1);
    		$resized->addImage($f);

		endforeach;

		header("Content-Type: image/gif");
		echo $frames->getImagesBlob();
		exit();

		return $frames;


//		$resized = $resized->deconstructImages();

//		$frames = $frames->deconstructImages();

		/*
			trace();
			$f->compositeImage($frame, Imagick::COMPOSITE_DEFAULT,  ($max_w - $dest_w) /2, ($max_h - $dest_h) / 2);
			$f->setImageDelay(10);
		*/

		$this->update_size( $dst_w, $dst_h );
		return $resized;

		return new WP_Error( 'image_resize_error', __('Image resize failed.'), $this->file );
	}


}
