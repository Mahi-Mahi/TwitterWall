<?php

class Mahi_Image_Editor_GD extends WP_Image_Editor_GD {

	function set_progressive() {
		imageinterlace($this->image, true);
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

		$resized = $this->_scale( $max_w, $max_h, $bgcolor, $r );

		if ( is_resource( $resized ) ) {
			imagedestroy( $this->image );
			$this->image = $resized;
			return true;

		} elseif ( is_wp_error( $resized ) )
			return $resized;

		return new WP_Error( 'image_resize_error', __('Image resize failed.'), $this->file );
	}

	protected function _scale( $max_w, $max_h, $bgcolor, $r = array()) {

		list($dest_w, $dest_h) = wp_constrain_dimensions( $this->size['width'], $this->size['height'], $max_w, $max_h );

		$resized = wp_imagecreatetruecolor( $max_w, $max_h );

		if ( $r['transparent'] ):
			$transparent = imagecolortransparent($resized);
			imagefill($resized, 0, 0, $transparent);
		else:
			preg_match("#\#?([\dA-F]{2})([\dA-F]{2})([\dA-F]{2})#i", $bgcolor, $c);
			$background = imagecolorallocate($resized, hexdec($c[1]), hexdec($c[2]), hexdec($c[3]));
			imagefill($resized, 0, 0, $background);
		endif;

		imagecopyresampled( $resized, $this->image, ($max_w - $dest_w) /2, ($max_h - $dest_h) / 2, 0, 0, $dest_w, $dest_h, $this->size['width'], $this->size['height'] );

		if ( is_resource( $resized ) ) {
			$this->update_size( $dst_w, $dst_h );
			return $resized;
		}

		return new WP_Error( 'image_resize_error', __('Image resize failed.'), $this->file );
	}

}
