<?php
App::uses('AppModel', 'Model');
class TmpEmployeeValuationLegalKabag extends AppModel {

	public $useDbConfig = 'tmp';
	public $tablePrefix = '_';
	public $useTable = 'tmp_employee_valuation_legal_kabag';
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