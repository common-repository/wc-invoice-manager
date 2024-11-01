<?php
namespace QuadLayers\WCIM\Models;

use QuadLayers\WCIM\Entities\Admin_Menu_Emails;
use QuadLayers\WP_Orm\Builder\SingleRepositoryBuilder;

class Admin_Menu_Emails_Model {

	protected static $instance;
	protected $repository;

	private function __construct() {
		$builder = ( new SingleRepositoryBuilder() )
		->setTable( 'wcim_emails' )
		->setEntity( Admin_Menu_Emails::class );

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
			$admin = new Admin_Menu_Emails();
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
