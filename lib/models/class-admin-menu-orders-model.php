<?php
namespace QuadLayers\WCIM\Models;

use QuadLayers\WCIM\Entities\Admin_Menu_Orders;
use QuadLayers\WP_Orm\Builder\SingleRepositoryBuilder;

class Admin_Menu_Orders_Model {

	protected static $instance;
	protected $repository;

	private function __construct() {
		$builder = ( new SingleRepositoryBuilder() )
		->setTable( 'wcim_admin' )
		->setEntity( Admin_Menu_Orders::class );

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
			$admin = new Admin_Menu_Orders();
			return $admin->getProperties();
		}
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
