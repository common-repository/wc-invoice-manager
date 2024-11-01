<?php
namespace QuadLayers\WCIM\Models;

use QuadLayers\WCIM\Entities\Admin_Menu_Settings;
use QuadLayers\WP_Orm\Builder\SingleRepositoryBuilder;

class Admin_Menu_Settings_Model {

	protected static $instance;
	protected $repository;

	private function __construct() {
		$builder = ( new SingleRepositoryBuilder() )
		->setTable( 'wcim_settings' )
		->setEntity( Admin_Menu_Settings::class );

		$this->repository = $builder->getRepository();
	}

	public function get_table() {
		return $this->repository->getTable();
	}

	public function get() {
		$entity = $this->repository->find();

		if ( $entity ) {
			return $entity->getProperties();
		} else {
			$settings = new Admin_Menu_Settings();
			return $settings->getProperties();
		}
		$settings = new Admin_Menu_Settings();
		return $settings->getDefaults();
	}

	public function delete_all() {
		return $this->repository->delete();
	}

	public function save( $data ) {
		$entity = $this->repository->create( $data );
		if ( $entity ) {
			return true;
		}
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
