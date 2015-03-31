<?php
App::uses('AppModel', 'Model');
class TmpEmployeeValuationItDev extends AppModel {

	public $useDbConfig = 'tmp';
	public $tablePrefix = '_';
	public $useTable = 'tmp_employee_valuation_it_dev';
	public $displayField = 'id';

	public $hasOne = array(
		'Employee' => array(
			'className' => 'Employee',
			'foreignKey' => 'nik',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
}