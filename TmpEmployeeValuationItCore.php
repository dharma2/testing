<?php
App::uses('AppModel', 'Model');
class TmpEmployeeValuationItCore extends AppModel {

	public $useDbConfig = 'tmp';
	public $tablePrefix = '_';
	public $useTable = 'tmp_employee_valuation_it_core';
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