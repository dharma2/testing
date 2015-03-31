<?php
App::uses('AppModel', 'Model');
class TmpEmployeeValuationAdminCreditKabag extends AppModel {

	public $useDbConfig = 'tmp';
	public $tablePrefix = '_';
	public $useTable = 'tmp_employee_valuation_admin_credit_kabag';
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