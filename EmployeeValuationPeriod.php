<?php
App::uses('AppModel', 'Model');
App::uses('CakeNumber', 'Utility');
class EmployeeValuationPeriod extends AppModel {

	public $displayField = 'period';
	
	public $order = "EmployeeValuationPeriod.created DESC";
	
	public $validate = array(
		'employee_id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'notEqual' => array(
				'rule' => 'notEqual',
				'message' => 'Employee and Supervisor should not be the same.'
			),
			'noDuplicates' => array(
				/* In the parameters, after 'description', there can be added more fields to validate */
				'rule' => array('noDuplicates',	array('valuation_type', 'period')),
				'message' => 'Employee already evaluated in this period.',
				//'on' => 'create',
			)
		),
		'supervisor_id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
			),
			'noDuplicates' => array(
				/* In the parameters, after 'description', there can be added more fields to validate */
				'rule' => array('noDuplicates',	array('valuation_type', 'period')),
				'message' => 'Supervisor already set to evaluated in this employee and period.',
				//'on' => 'create',
			)
		),
		'period' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Please select period.'
			),
			'minLength' => array(
				'rule' => array('minLength', 4),
				'message' => 'Please select period.'
			),
			'maxLength' => array(
				'rule' => array('maxLength', 6),
				'message' => 'Please select period.'
			),
		),
	);

	public $belongsTo = array(
		'Employee' => array(
			'className' => 'Employee',
			'foreignKey' => 'employee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Supervisor' => array(
			'className' => 'Employee',
			'foreignKey' => 'supervisor_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Editor' => array(
			'className' => 'User',
			'foreignKey' => 'editor',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	public $hasMany = array(
		'EmployeeValuation' => array(
			'className' => 'EmployeeValuation',
			'foreignKey' => 'valuation_period_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);
	
	public function notEqual($check) {
		if (isset($this->data[$this->alias]['supervisor_id'])) {
			if ($this->data[$this->alias]['supervisor_id'] == $check['employee_id']) {
				return false;
			}
		}
		return true;
	}
	
	/* Checks to see if there is already a duplicate of the specific combination of fields */
	function noDuplicates($value, $params){
		switch ($this->data[$this->alias]['valuation_type']) {
			case '536c45cb-8270-4388-b967-052d7f000101' : /* star */
				$params[] =  'supervisor_id';
				break;
			case '536c4592-a484-4877-95bd-0c8c7f000101' : /* contract */
			case '536c45f3-ad0c-4d9c-9953-0c8b7f000101' : /* selfPerformance */
			case '5476a636-d50c-4eec-ac14-3e947f000101' : /* monthlyPBO */
			default :
				$params[] =  'employee_id';
				break;
		}
		
		/* if editing an existing record then don't count this record in the check for duplicates */
		if (!empty($this->id)) $conditions[] = array($this->primaryKey . ' <>' => $this->id);
		
		/* Add a condition for each field we want to check against */
		foreach ($params as $field){
			/* check if value is empty. if it is then check for a NULL value against this field */
			if($this->data[$this->name][$field])
				$fieldVal = $this->data[$this->name][$field];
			else
				$fieldVal = null;
			$conditions[] = array($field => $fieldVal);
		}
		
		$existingFieldsCount = $this->find( 'count', array('conditions'=> $conditions, 'recursive' => -1, 'callbacks'=>false) );
		return $existingFieldsCount < 1;
	}

	public function beforeFind($query) {
		App::uses('CakeSession', 'Model/Datasource');
		
		/* group id not in Root, HRD */
		if(!in_array(CakeSession::read('Auth.User.group_id'), array('50f7a59c-9d84-490f-a4c7-72327f000101', '51ba9d8d-0a20-4a38-8df8-30eb7f000101'))){
			$query['conditions']['OR'][$this->alias . '.supervisor_id'] = CakeSession::read('Auth.User.Employee.id');
			$query['conditions']['OR']['AND']['valuation_type'][] = '536c45f3-ad0c-4d9c-9953-0c8b7f000101'; /* selfPerformace */

			$query['conditions']['OR']['AND']['valuation_type'][] = '5476a636-d50c-4eec-ac14-3e947f000101'; /* montlyPBO */
			$query['conditions']['OR']['AND']['valuation_type'][] = '5487bca6-1150-4884-a6a5-2fd07f000101'; /* montlyAO */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54e32d2a-7d40-46a2-8828-4ca50aa80505'; /* montlyBM */
			$query['conditions']['OR']['AND']['valuation_type'][] = '5499fe3a-2de4-4c6a-b9a3-2e857f000101'; /* monthlyCreditRisk */
			$query['conditions']['OR']['AND']['valuation_type'][] = '5499fe6e-cfac-4bab-9c6c-35b97f000101'; /* monthlyCreditRiskAdmin */
			$query['conditions']['OR']['AND']['valuation_type'][] = '5499fe85-16a0-4b99-8636-2f2a7f000101'; /* monthlySID */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b37663-80a8-409b-81bd-3eddc0a80535'; /* montlyCustomerService */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54aa115d-a2b0-4043-b6ce-08927f000101'; /* montlyTeller */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54ff8000-15d0-424b-b704-8c0e0aa80505'; /* montlyTellerHead */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54aa117e-3224-4315-88c5-12727f000101'; /* montlyAdminBranch */
			$query['conditions']['OR']['AND']['valuation_type'][] = '60713fc6-bca1-11e4-9e3f-001517dcfff1'; /* montlyAdminCredit */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b47cd8-3470-4f26-b221-0e27c0a80535'; /* montlyTreasury */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b49061-2244-4574-8bfa-682ec0a80535'; /* montlyBOKredit */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b4908d-a000-4a08-bdc7-7372c0a80535'; /* montlyBODana */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b4be09-34bc-4074-8b8c-77ee7f000101'; /* montlyHCM-AyuKurnia */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b707cb-20fc-45a4-b5a7-0e27c0a80535'; /* montlyHCM-DodikWirawan */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b73d4a-05fc-42a6-b510-124ac0a80535'; /* montlyHCM-Yuliaheni */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b707a8-e098-437c-adac-0e26c0a80535'; /* montlyHCM-DianKrisna */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b76b0a-1e30-409f-b26a-1b29c0a80535'; /* montlyHCM-CitaRasmini */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b7a70e-1bd4-47a2-a4cf-2aa6c0a80535'; /* montlySekretariat-AyuSilviananda */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54b855fa-b8c8-4f17-b8bd-3714c0a80535'; /* montlySekretariat-IdaAyuPermata */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54beeceb-f1d8-4d2c-9812-6503c0a80535'; /* montlySKAI-Manager */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bf078d-52e8-433a-ba87-08b0c0a80535'; /* montlySKAI-Credit */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bf70b0-0158-4193-8187-17ffc0a80535'; /* montlySKAI-Operational */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54f01b20-c958-4c5a-8702-d6f20aa80505'; /* montlySysRep */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c72a5d-df5c-4e5d-95ba-1843c0a80535'; /* montlyAudit-It */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bf1e52-0934-4756-b194-08b0c0a80535'; /* montlyQS-Echa */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bf268f-7438-464e-9764-0904c0a80535'; /* montlyQS-Anya */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bf26ba-dbe0-4e95-aa78-0a39c0a80535'; /* montlyQS-Sri */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bf26f1-52f4-4591-9725-0cdac0a80535'; /* montlyQS-Indry */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bface4-b9b0-4d58-b19b-238ec0a80535'; /* montlyCompliance */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bfcfef-0828-4a8d-9532-2a25c0a80535'; /* montlyKabagBO */
			//$query['conditions']['OR']['AND']['valuation_type'][] = '54bfd882-a484-4246-836b-2cdbc0a80535'; /* montlyKadivOperasional */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c0e3be-d594-4eb4-8ffd-066cc0a80535'; /* montlyPengawasan */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c0fe1d-e034-4cff-8e8d-1e52c0a80535'; /* montlyMarcomm-YogaSugama */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c0e046-a194-4814-865d-1e52c0a80535'; /* montlyMarcomm-DayuMas */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c644c5-a9ac-418f-bf82-368dc0a80535'; /* montlyMarcomm-CokDewi */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c74032-3bd4-4e5c-8c1d-0e50c0a80535'; /* montlyMarcomm-Wisnu */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54ea98f5-b228-4db0-9ec1-1ee40aa80505'; /* montlyLegal */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bfc786-e13c-4e92-a534-17ffc0a80535'; /* montlyCoorporateLegal */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54bfd882-a484-4246-836b-2cdbc0a80535'; /* montlyKabagUmum */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c9e05c-718c-419e-b735-43b8c0a80535'; /* montlyKasKecil */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c9de3e-c608-4e42-b9b2-4363c0a80535'; /* montlyBagianUmum */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c9e23e-1cf4-4050-a90f-43a6c0a80535'; /* montlySecurity */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c9e34d-4474-43e0-804a-4435c0a80535'; /* montlyDriver */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54c9e465-9dc8-41da-a6f7-4305c0a80535'; /* montlyOB */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54f0260a-130c-4821-bfe3-d6f20aa80505'; /* montlyOperator */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54f02622-483c-4c5f-ad80-d6f20aa80505'; /* montlyEkspedisi */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54adcad7-3318-4b82-95c1-49600aa80505'; /* monthlyOnlyValue */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54ff9850-56c4-44e6-8e3b-416a0aa80505'; /* monthlyITCore */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54ff982e-7ec8-4a5a-a162-4c4f0aa80505'; /* monthlyITDev */
			$query['conditions']['OR']['AND']['valuation_type'][] = '550fc5ed-59a4-4b12-a674-514a0aa80505'; /* monthlyLegalKabag */
			$query['conditions']['OR']['AND']['valuation_type'][] = '5514168e-553c-4616-8624-fed90aa80505'; /* monthlyAdminCreditKabag */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54f033ea-7f1c-4c49-964c-4eb90aa80505'; /* monthlyLestariFirst */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54f03956-01f0-4d7f-96e5-b26e0aa80505'; /* monthlyAM */
			$query['conditions']['OR']['AND']['valuation_type'][] = '54f03945-9de0-496a-8ddb-b26e0aa80505'; /* monthlyRM */

			$query['conditions']['OR']['AND'][$this->alias . '.employee_id'] = CakeSession::read('Auth.User.Employee.id');
		}
		
		if(CakeSession::read('Auth.User.group_id') != '50f7a59c-9d84-490f-a4c7-72327f000101' && CakeSession::read('EmployeeValuationPeriod.beforeFind.action') == 'edit'){
			$query['conditions']['OR']['AND'][$this->alias . '.done'] = 0;
		}
		
		return $query;
	}
	
	public function afterValidate(){
		unset($this->data['Employee'], $this->data['Supervisor']);
	}
	
	public function finalResult($valuations = array(), $group_id = null, $join_date = null){
		/*-------------------- DISIPLIN --------------------*/
		foreach($valuations as $key=>$val):
			switch($key){
				case '532f89f0-9058-424f-9672-1aa934c901bb': /* Sakit */
					if(!in_array(strtolower($val['notes']), array('opnam', 'opname'))){
						if($val['score'] <= 2){
							$point['discipline']['sakit'] = 4;
						}elseif($val['score'] >= 3 && $val['score'] <= 4){
							$point['discipline']['sakit'] = 3;
						}elseif($val['score'] >= 5 && $val['score'] <= 6){
							$point['discipline']['sakit'] = 2;
						}else{
							$point['discipline']['sakit'] = 1;
						}
					}
					break;
				case '532f89f8-1e94-49aa-8443-12c034c901bb': /* Ijin */
					if($val['score'] <= 3){
						$point['discipline']['ijin'] = 4;
					}else{
						$point['discipline']['ijin'] = 1;
					}
					break;
				case '532f8a06-b440-4184-9e00-1cd334c901bb': /* Alpha */
					if($val['score'] > 0){
						$point['discipline']['alpha'] = 1;
					}else{
						$point['discipline']['alpha'] = 4;
					}
					break;
				case '532f8a0f-d1a4-43b0-a1b8-12c634c901bb': /* Cuti */
					$hak_cuti = 12;
					if(strtotime($join_date) >= strtotime('01-01-' . date('Y'))){
						$hak_cuti = 0;
					}elseif(strtotime($join_date) >= strtotime('01-01-' . date('Y') - 1) && strtotime($join_date) < strtotime('01-01-' . date('Y'))){
						$hak_cuti = 12 - (double)substr($join_date, 3,2);
					}
					if($val['score'] <= $hak_cuti){
						$point['discipline']['cuti'] = 4;
					}else{
						$point['discipline']['cuti'] = 1;
					}
					break;
				case '532f8a18-6854-4b42-88e7-12c434c901bb': /* Terlambat */
					if($val['score'] <= 2){
						$point['discipline']['late'] = 4;
					}elseif($val['score'] >= 3 && $val['score'] <= 4){
						$point['discipline']['late'] = 3;
					}elseif($val['score'] >= 5 && $val['score'] <= 6){
						$point['discipline']['late'] = 2;
					}else{
						$point['discipline']['late'] = 1;
					}
					break;
				case '532f8a30-ba80-4ba5-becf-1aac34c901bb': /* SP / Teguran */
					if($val['score'] > 0){
						$point['percentage']['discipline'] = 20;
					}
					break;
			}
		endforeach;
		if(!isset($point['percentage']['discipline'])){
			$point['percentage']['discipline'] = (array_sum($point['discipline']) / count($point['discipline'])) / 4 * 100;
		}
		
		/*-------------------- PERFORMANCE (REVIEW SUPERVISOR) --------------------*/
		switch ($group_id):
			case '541791d4-78d4-48ca-b04e-a4cb0aa80505' :/* Group Lestari First Manager */
				$lfTarget['a'] = $valuations['5417884b-49a4-438b-9e28-20ef7f000101']['score'];
				$lfTarget['b'] = $valuations['54178856-ed3c-49c5-b8be-0fbb7f000101']['score'];
				$lfPerformance['a'] = $valuations['5417886b-5bc0-41e4-9508-10dd7f000101']['score'];
				$lfPerformance['b'] = $valuations['54181ec6-8dd8-4069-9f50-1d637f000101']['score'];
				
				$point['percentage']['performance'] = (($lfPerformance['a'] / $lfTarget['a']) + ($lfPerformance['b'] / $lfTarget['b'])) / 2 * 100;
				break;
			case '5305989d-e8ac-49a9-9503-87b70aa80505' :/* Group Kadiv Business */
				/* Hanya persentase growth kredit saja yg dinilai */
				$kBusiness['Target']['growthKredit'] = $valuations['54178935-ae20-4146-b40f-11017f000101']['score'];
				$kBusiness['Perform']['growthKredit'] = $valuations['54178a7e-557c-44a9-a642-20f07f000101']['score'];
				$point['percentage']['performance'] = ($kBusiness['Perform']['growthKredit'] / $kBusiness['Target']['growthKredit']) * 100;
				break;
			case '5417d23a-dc64-4bbc-b428-773b0aa80505' :/* Group Kadiv Credit Risk & Special Asset Management */
				
				break;
			case '520052e3-0f0c-4efe-a7ac-d99f0aa80505' :/* Group Pengawasan Kredit */
				/* Perform by supervisor dibagi rata2 perform by supervisor */
				$pkSupervisor['NPL'] = $valuations['54181f50-94f4-4eb5-8009-205f7f000101']['score'];
				$pkSupervisor['WL'] = $valuations['54181f5e-6c68-4a95-89fa-1f8f7f000101']['score'];
				$pkSupervisor['AYDA'] = $valuations['54181f6a-21fc-4276-813e-20837f000101']['score'];
				$pkAvg[2014]['NPL'] = 2.713333;
				$pkAvg[2014]['WL'] = 3.594167;
				$pkAvg[2014]['AYDA'] = 0.090833;

				$point['percentage']['performance'] = (
					($pkSupervisor['NPL'] / $pkAvg[2014]['NPL']) + ($pkSupervisor['WL'] / $pkAvg[2014]['WL']) + ($pkSupervisor['AYDA'] / $pkAvg[2014]['AYDA'])
				) / 3 * 100;
				break;
			case '5315462b-9bd8-4e58-a1d9-42110aa80505' :/* Group Kadiv Retail Banking */
				$kRetail['Target']['NOAJumbo'] = $valuations['54178b79-16a8-4dc6-8dcb-407b7f000101']['score'];
				$kRetail['Target']['NOAUstyle'] = $valuations['54178ba9-eae4-489a-a114-20ef7f000101']['score'];
				$kRetail['Target']['NOALF'] = $valuations['54178bc1-ffec-4b52-80d4-20f17f000101']['score'];
				$kRetail['Target']['DPK'] = $valuations['54178bcc-391c-4f2c-8dde-0fb87f000101']['score'];
				$kRetail['Perform']['NOAJumbo'] = $valuations['54178be3-0bac-4feb-9265-405e7f000101']['score'];
				$kRetail['Perform']['NOAUstyle'] = $valuations['54178bef-aa88-43b1-9909-0fbc7f000101']['score'];
				$kRetail['Perform']['NOALF'] = $valuations['54178c07-be3c-4d96-a3e9-405d7f000101']['score'];
				$kRetail['Perform']['DPK'] = $valuations['54178c12-bc50-497c-b854-20f07f000101']['score'];
				$point['percentage']['performance'] = (
					($kRetail['Perform']['NOAJumbo'] / $kRetail['Target']['NOAJumbo']) + ($kRetail['Perform']['NOAUstyle'] / $kRetail['Target']['NOAUstyle']) +
					($kRetail['Perform']['NOALF'] / $kRetail['Target']['NOALF']) + ($kRetail['Perform']['DPK'] / $kRetail['Target']['DPK'])
				) / 4 * 100;
				break;
			case '50f7a7f3-31ac-4856-9208-72ca7f000101' :/* Group BM */
				$lfTarget['a'] = $valuations['54178cf4-8f08-4116-9f6e-405d7f000101']['score'];
				$lfTarget['b'] = $valuations['54178cff-5c5c-40e7-8dfb-20f07f000101']['score'];
				$lfTarget['c'] = $valuations['54178d0a-4a34-428e-a032-407b7f000101']['score'];
				$lfTarget['d'] = $valuations['54178d13-8628-42cb-9fd3-0fba7f000101']['score'];
				$lfPerformance['a'] = $valuations['54178d22-caa0-4320-896e-20ef7f000101']['score'];
				$lfPerformance['b'] = $valuations['54178d54-4558-4aa3-88f1-0fbc7f000101']['score'];
				$lfPerformance['c'] = $valuations['54178d60-5ec0-41c8-aede-405d7f000101']['score'];
				$lfPerformance['d'] = $valuations['54178d78-5d74-4099-85b9-10dd7f000101']['score'];
				
				$point['percentage']['performance'] = (
					($lfPerformance['a'] / $lfTarget['a']) + ($lfPerformance['b'] / $lfTarget['b']) +
					($lfPerformance['c'] / $lfTarget['c']) + ($lfPerformance['d'] / $lfTarget['d'])
				) / 4 * 100;
				break;
			/* Employee */
			default :
				$point['percentage']['performance'] = ($valuations['5408537c-dfbc-482f-84fd-2fbb7f000101']['score'] / $valuations['5408536d-bac8-44f7-af6e-2fba7f000101']['score']) * 100;
				break;
		endswitch;
		
		/*-------------------- 7 VALUE (REVIEW SUPERVISOR) --------------------*/
		$point['percentage']['value'] = (
				$valuations['5407d284-f324-494d-81e2-0dee7f000101']['score'] + /* Care */
				$valuations['5407d291-fca0-4f6d-90e8-0def7f000101']['score'] + /* Honest */
				$valuations['5407d29c-d0f0-4647-8593-06cc7f000101']['score'] + /* Perfection */
				$valuations['5407d2a8-9bd0-40b7-8c40-06ca7f000101']['score'] + /* Positiv */
				$valuations['5407d2b6-3bf0-48c2-8f4f-0dfb7f000101']['score'] + /* Entusiam */
				$valuations['5407d2c1-47b0-4de0-9ac8-0dfd7f000101']['score'] + /* Eneryg */
				$valuations['5407d2cc-7098-45ba-abf2-0de97f000101']['score'] /* Knowledge */
			) / (4 * 7) * 100;
		
		$point['avg'] = $finalPercentage = round(array_sum($point['percentage']) / count($point['percentage']), 2);
		
		if($finalPercentage >= 85){
			$point['final'] = 'A';
		}elseif($finalPercentage >= 70 && $finalPercentage < 85){
			$point['final'] = 'B';
		}elseif($finalPercentage >= 55 && $finalPercentage < 70){
			$point['final'] = 'C';
		}else{
			$point['final'] = 'D';
		}
		
		return $point;
	}
	
	public function monthlyGetPoint($setting, $score){
		$rule = '';
		if(is_array(json_decode($setting['val2'], true))){
			$params = json_decode($setting['val2'], true);
			switch($params['rule']):
				case "range":
					foreach($params['range'] as $key2 => $val2){
						$cond = $rulex = '';
						foreach($val2 as $key3=>$val3){
							$cond .= __('%s %s %s && ', $score, $key3, $val3);
							$rulex .= __('%s %s & ', $key3, $val3);
						}
						$rule .= __("%s: %s %s\n", $key2, $setting['val1'], substr($rulex, 0, -3));
						
						$cond = (substr($cond, 0, -4));
						if(eval("return $cond;")){
							$point = $key2;
						}
					}
					break;
				case "rangeDivision":
					foreach($params['range'] as $key2 => $val2){
						$cond = $rulex = '';
						foreach($val2 as $key3=>$val3){
							$cond .= __('%s %s %s && ', $score, $key3, $val3);
							$rulex .= __('%s %s & ', $key3, $val3);
						}
						$rule .= __("%s: %s %s\n", $key2, $setting['val1'], substr($rulex, 0, -3));
						
						$cond = (substr($cond, 0, -4));
						if(eval("return $cond;")){
							$point = $key2;
						}
					}
					break;
				case "targetBobot":
					$point = $score / $params['target'] * $params['bobot'];
					$parameter = $setting['val3'];
					break;
				case "multiplication":
					$point = $params['multiplier'] * $score;
					break;
				case "equal":
					$point = $score;
					break;
			endswitch;
		}
		
		return (double)$point;
	}
	
	public function monthly($settings, $valuations, $valuationType=null){
		$i=0;
		$return['tr'] = '';
		
		// GANTI METODE FOREACH MENGGUNAKAN $valuations
		//exit('GANTI METODE FOREACH MENGGUNAKAN $valuations di Model/EmployeeValuationPeriod.php');
		
		foreach($settings as $key1=>$val1){
			if(isset($valuations[$key1])){
				$score = (double)$valuations[$key1]['score'];
				$rule = '';
				if(is_array(json_decode($val1['val2'], true))){
					$params = json_decode($val1['val2'], true);
					switch($params['rule']):
						case "range":
							foreach($params['range'] as $key2 => $val2){
								$cond = $rulex = '';
								foreach($val2 as $key3=>$val3){
									$cond .= __('%s %s %s && ', $score, $key3, $val3);
									$rulex .= __('%s %s & ', $key3, $val3);
								}
								$rule .= __("%s: %s %s\n", $key2, $val1['val1'], substr($rulex, 0, -3));
								
								$cond = (substr($cond, 0, -4));
								if(eval("return $cond;")){
									$point[$key1] = $key2;
								}
							}
							break;
						case "rangePointBobot":
							foreach($params['range'] as $key2 => $val2){
								$cond = $rulex = '';
								foreach($val2 as $key3=>$val3){
									$cond .= __('%s %s %s && ', $score, $key3, $val3);
									$rulex .= __('%s %s & ', $key3, $val3);
								}
								$rule .= __("%s: %s\n", $key2, substr($rulex, 0, -3));
								
								$cond = (substr($cond, 0, -4));
								if(eval("return $cond;")){
									$pointSkai = $key2;
									$point[$key1] = $key2 * $params['bobot'] / 100;
								}
							}
							break;
						case "rangeScorePoint":
							foreach($params['range'] as $key2 => $val2){
								$cond = $rulex = '';
								foreach($val2 as $key3=>$val3){
									$cond .= __('%s %s %s && ', $score, $key3, $val3);
									$rulex .= __('%s %s & ', $key3, $val3);
								}
								$rule .= __("%s: %s\n", $key2, substr($rulex, 0, -3));
								
								$cond = (substr($cond, 0, -4));
								if(eval("return $cond;")){
									$pointSkai = $key2;
									$point[$key1] = $key2 / 4 * $params['bobot'];
								}
							}
							break;
						case "rangeDivision":
							$score = $score/$valuations[$params['division']]['score']*100;
							foreach($params['range'] as $key2 => $val2){
								$cond = $rulex = '';
								
								foreach($val2 as $key3=>$val3){
									$cond .= __('%s %s %s && ', $score, $key3, $val3);
									$rulex .= __('%s %s & ', $key3, $val3);
								}
								$rule .= __("%s: %s %s\n", $key2, $val1['val1'], substr($rulex, 0, -3));
								
								$cond = (substr($cond, 0, -4));
								if(eval("return $cond;")){
									$point[$key1] = $key2;
								}
							}
							break;
						case "targetBobot":
							$point[$key1] = $score / $params['target'] * $params['bobot'];
							$parameter = $val1['val3'];
							break;
						case "multiplication":
							$point[$key1] = $params['multiplier'] * $score;
							break;
						case "equal":
							$point[$key1] = $score;
							break;
					endswitch;
				}
				if(isset($point[$key1])){
					$pointDisplay = $point[$key1];
				}else{
					$pointDisplay = '-';
				}
				
				switch($valuationType) :
					case '54e32d2a-7d40-46a2-8828-4ca50aa80505' /* monthly BM */:
						$bmTarget = $bmPencapaian = $bmPercenPencapaian = '-';
						switch($val1['id']):
							case '54e30787-cef0-4603-bd02-a70f0aa80505' /* Asset */:
							case '54e3079f-02ac-4b05-a02c-45280aa80505' /* Credit */:
							case '54e307ba-93d0-433a-8bf2-40af0aa80505' /* DPK */:
							case '54e307cf-4834-4839-a7ba-45440aa80505' /* Laba */:
								$bmTarget = (double)$valuations[$params['division']]['score'];
								$bmPencapaian = (double)$valuations[$key1]['score'];
								$bmPercenPencapaian = (double)$score;
								break;
							default :
								$bmPencapaian = (double)$valuations[$key1]['score'];
						endswitch;
						
						$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
							++$i,
							$val1['val1'],
							nl2br($rule),//$val1['val3'],
							CakeNumber::currency($bmTarget, ''),
							CakeNumber::currency($bmPencapaian, ''),
							CakeNumber::precision($bmPercenPencapaian, 2),
							$pointDisplay
						);
						
						if($key1 == '54d30aaf-4f28-4a70-b0ec-45fd0aa80505'){
							$return['extraordinary'] = 0;
							if((double)$score == 7){
								$return['extraordinary'] = 1;
							}elseif((double)$score > 7){
								$return['extraordinary'] = 2;
							}
						}
						break;
					case '5476a636-d50c-4eec-ac14-3e947f000101' /* montlyPBO */:
						switch($val1['id']):
							case '548442e4-7498-43c4-9c09-147e7f000101':
								$point['performancePBO']['point'] = $this->monthlyGetPoint($settings['548442e4-7498-43c4-9c09-147e7f000101'], $valuations['548442e4-7498-43c4-9c09-147e7f000101']['score']);
								$point['performancePBO']['pencapaian'] = $this->monthlyGetPoint($settings['54844322-1704-4039-ba33-12ed7f000101'], $valuations['54844322-1704-4039-ba33-12ed7f000101']['score']);
								$pointPencapaian = 60/100 * (
										$point['performancePBO']['point'] + 
										$point['performancePBO']['pencapaian']
									);
								$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td rowspan='2'>%s</td><td rowspan='2'>%s</td></tr>",
									++$i,
									$val1['val1'],
									nl2br($rule),//$val1['val3'],
									(double)$score,
									$pointDisplay,
									'60%',
									$pointPencapaian
								);
								break;
							case '5484438e-c2e8-4af3-9b64-147e7f000101':
								$point['performancePBO']['cif'] = $this->monthlyGetPoint($settings['5484438e-c2e8-4af3-9b64-147e7f000101'], $valuations['5484438e-c2e8-4af3-9b64-147e7f000101']['score']);
								$point['performancePBO']['noaJumbo'] = $this->monthlyGetPoint($settings['548443d5-f920-4df4-b630-12ed7f000101'], $valuations['548443d5-f920-4df4-b630-12ed7f000101']['score']);
								$point['performancePBO']['noaUStyle'] = $this->monthlyGetPoint($settings['5484441f-22c4-4624-bbfc-15017f000101'], $valuations['5484441f-22c4-4624-bbfc-15017f000101']['score']);
								$cifNoaJumboUstyle = 40/100 * (
										$point['performancePBO']['cif'] + 
										$point['performancePBO']['noaJumbo'] + 
										$point['performancePBO']['noaUStyle']
									);
								$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td rowspan='3'>%s</td><td rowspan='3'>%s</td></tr>",
									++$i,
									$val1['val1'],
									nl2br($rule),//$val1['val3'],
									(double)$score,
									$pointDisplay,
									'40%',
									$cifNoaJumboUstyle
								);
								break;
							default:
								$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td colspan='3'>%s</td></tr>",
									++$i,
									$val1['val1'],
									nl2br($rule),//$val1['val3'],
									(double)$score,
									$pointDisplay
								);
								break;
						endswitch;
						$return['pointPbo'] = ($pointPencapaian + $cifNoaJumboUstyle)/2;
						break;
					case '5499fe3a-2de4-4c6a-b9a3-2e857f000101' /* monthlyCreditRisk */:
						$number = $rowSpanTitle = $rowSpanScore = '';
						switch($val1['id']):
							case '549a04a2-0500-4234-845a-2dcf7f000101':
								$params_1d = json_decode($settings['549a04a2-0500-4234-845a-2dcf7f000101']['val2'], true);
								$params_2d = json_decode($settings['549a04bf-83fc-45b1-8525-2e957f000101']['val2'], true);
								$params_3d = json_decode($settings['549a04d0-b46c-45d5-9597-2dcf7f000101']['val2'], true);
								$params_4d = json_decode($settings['549a04e4-5d54-4773-b863-2f2a7f000101']['val2'], true);
								$nilai = ($valuations['549a04a2-0500-4234-845a-2dcf7f000101']['score'] * $params_1d['multiplier']) + 
									($valuations['549a04bf-83fc-45b1-8525-2e957f000101']['score'] * $params_2d['multiplier']) + 
									($valuations['549a04d0-b46c-45d5-9597-2dcf7f000101']['score'] * $params_3d['multiplier']) + 
									($valuations['549a04e4-5d54-4773-b863-2f2a7f000101']['score'] * $params_4d['multiplier']);
								$scoreSum = $valuations['549a04a2-0500-4234-845a-2dcf7f000101']['score'] +
									$valuations['549a04bf-83fc-45b1-8525-2e957f000101']['score'] +
									$valuations['549a04d0-b46c-45d5-9597-2dcf7f000101']['score'] +
									$valuations['549a04e4-5d54-4773-b863-2f2a7f000101']['score'];
								if($scoreSum == 0){
									$avg = 0;
								}else{
									$avg = $return['pointCreditRisk'][] = $nilai / $scoreSum;
								}
								
								$number = __("<td rowspan=4 style='vertical-align:middle;'>%s</td>", ++$i);
								$rowSpanTitle = __("<td rowspan=4 style='vertical-align:middle;'>%s</td>", $val1['val1']);
								$rowSpanScore = __("<td rowspan=4 style='vertical-align:middle;'>%s</td>", $avg);
								break;
							case '549a0500-ab68-494b-8b25-2e797f000101':
								$params_1d = json_decode($settings['549a0500-ab68-494b-8b25-2e797f000101']['val2'], true);
								$params_2d = json_decode($settings['549a0511-11fc-4d76-9d05-10a77f000101']['val2'], true);
								$params_3d = json_decode($settings['549a0522-b094-4ff0-a048-35b97f000101']['val2'], true);
								$params_4d = json_decode($settings['549a0536-5e2c-4579-8fe4-2e857f000101']['val2'], true);
								$nilai = ($valuations['549a0500-ab68-494b-8b25-2e797f000101']['score'] * $params_1d['multiplier']) + 
									($valuations['549a0511-11fc-4d76-9d05-10a77f000101']['score'] * $params_2d['multiplier']) + 
									($valuations['549a0522-b094-4ff0-a048-35b97f000101']['score'] * $params_3d['multiplier']) + 
									($valuations['549a0536-5e2c-4579-8fe4-2e857f000101']['score'] * $params_4d['multiplier']);
								$scoreSum = $valuations['549a0500-ab68-494b-8b25-2e797f000101']['score'] +
									$valuations['549a0511-11fc-4d76-9d05-10a77f000101']['score'] +
									$valuations['549a0522-b094-4ff0-a048-35b97f000101']['score'] +
									$valuations['549a0536-5e2c-4579-8fe4-2e857f000101']['score'];
								if($scoreSum == 0){
									$avg = 0;
								}else{
									$avg = $return['pointCreditRisk'][] = $nilai / $scoreSum;
								}
								
								$number = __("<td rowspan=4 style='vertical-align:middle;'>%s</td>", ++$i);
								$rowSpanTitle = __("<td rowspan=4 style='vertical-align:middle;'>%s</td>", $val1['val1']);
								$rowSpanScore = __("<td rowspan=4 style='vertical-align:middle;'>%s</td>", $avg);
								break;
							case '549a0627-127c-4054-8e33-2e8c7f000101':
							case '549a0656-858c-42a4-86a2-2e957f000101':
							case '549a06ca-ad5c-4605-bc7a-2f3e7f000101':
							case '54d228d4-6810-45ef-b046-3a917f000101':
								$params['multiplier'] = '';
								$number = __("<td style='vertical-align:middle;'>%s</td>", ++$i);
								$rowSpanTitle = __("<td style='vertical-align:middle;'>%s</td>", $val1['val1']);
								$rowSpanScore = __("<td style='vertical-align:middle;'>%s</td>", $pointDisplay);
								$return['pointCreditRisk'][] = $pointDisplay;
								break;
						endswitch;
						
						$return['tr'] .= __("<tr>%s%s<td>%s</td><td>%s</td><td>%s</td><td>%s</td>%s</tr>",
							$number,
							$rowSpanTitle,
							nl2br($rule).$val1['val3'],
							(double)$score,
							$params['multiplier'],
							$pointDisplay,
							$rowSpanScore
						);
						
						if(isset($point) && count($point) > 0){
							$return['point'] = array_sum($point)/count($point);
						}else{
							$return['point'] = 0;
						}
						break;
					case '54b4be09-34bc-4074-8b8c-77ee7f000101' /* montlyHCM-AyuKurnia */:
					case '54b707cb-20fc-45a4-b5a7-0e27c0a80535' /* montlyHCM-DodikWirawan */:
					case '54b73d4a-05fc-42a6-b510-124ac0a80535' /* montlyHCM-Yuliaheni */:
					case '54b707a8-e098-437c-adac-0e26c0a80535' /* montlyHCM-DianKrisna */:
					case '54b76b0a-1e30-409f-b26a-1b29c0a80535' /* montlyHCM-CitaRasmini */:
					//case '54b7a70e-1bd4-47a2-a4cf-2aa6c0a80535' /* montlySekretariat-AyuSilviananda */:
					//case '54b855fa-b8c8-4f17-b8bd-3714c0a80535' /* montlySekretariat-IdaAyuPermata */:
						$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
							++$i,
							$val1['val1'],
							nl2br($parameter),//$val1['val3'],
							$params['bobot'],
							$params['target'],
							(double)$score,
							$pointDisplay
						);
						
						if(isset($point) && count($point) > 0){
							$return['point'] = array_sum($point);
						}else{
							$return['point'] = 0;
						}
						break;
					case '54bface4-b9b0-4d58-b19b-238ec0a80535' /* montlyCompliance */:
						$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
							++$i,
							$val1['val1'],
							$val1['val3'],
							(double)$score,
							$pointDisplay
						);
						
						if($key1 == '54d30aaf-4f28-4a70-b0ec-45fd0aa80505'){
							$return['extraordinary'] = 0;
							if((double)$score == 7){
								$return['extraordinary'] = 1;
							}elseif((double)$score > 7){
								$return['extraordinary'] = 2;
							}
						}
						break;
					case '54beeceb-f1d8-4d2c-9812-6503c0a80535' /* montlySKAI-Manager */:
					case '54bf078d-52e8-433a-ba87-08b0c0a80535' /* montlySKAI-Credit */:
					case '54bf70b0-0158-4193-8187-17ffc0a80535' /* montlySKAI-Operational */:
						$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
							++$i,
							$val1['val1'],
							nl2br($rule),//$val1['val3'],
							(double)$score,
							$pointSkai,
							$params['bobot'].'%',
							$pointDisplay
						);
						
						if($key1 == '54d30aaf-4f28-4a70-b0ec-45fd0aa80505'){
							$return['extraordinary'] = 0;
							if((double)$score == 7){
								$return['extraordinary'] = 1;
							}elseif((double)$score > 7){
								$return['extraordinary'] = 2;
							}
						}
						break;
					case '60713fc6-bca1-11e4-9e3f-001517dcfff1' /* montlyAdminCredit */:
						$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
							++$i,
							$val1['val1'],
							nl2br($val1['val3']),
							(double)$score
						);
						
						if($key1 == '54d30aaf-4f28-4a70-b0ec-45fd0aa80505'){
							$return['extraordinary'] = 0;
							if((double)$score == 7){
								$return['extraordinary'] = 1;
							}elseif((double)$score > 7){
								$return['extraordinary'] = 2;
							}
						}
						break;
					default:
						$return['tr'] .= __("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
							++$i,
							$val1['val1'],
							nl2br($rule),//$val1['val3'],
							(double)$score,
							$pointDisplay
						);
						
						if($key1 == '54d30aaf-4f28-4a70-b0ec-45fd0aa80505'){
							$return['extraordinary'] = 0;
							if((double)$score == 7){
								$return['extraordinary'] = 1;
							}elseif((double)$score > 7){
								$return['extraordinary'] = 2;
							}
						}
						break;
				endswitch;
			}
		}
		
		if(isset($point) && count($point) > 0){
			$return['pointSum'] = array_sum($point);
			$return['point'] = array_sum($point)/count($point);
		}else{
			$return['point'] = 0;
		}
		
		if(isset($return['extraordinary'])){
			$return['point'] += $return['extraordinary'];
		}
		
		return $return;
	}
}
