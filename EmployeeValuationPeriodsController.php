<?php
App::uses('AppController', 'Controller');
class EmployeeValuationPeriodsController extends AppController {

	public $components = array('Paginator');
	public $uses = array('EmployeeValuationPeriod', 'EmployeePosition', 'Employee', 'Setting', 'User', 'Sm');

	public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('edit', 'view');
		CakeSession::delete('EmployeeValuationPeriod.beforeFind.action');
	}

	public function index() {
		$this->EmployeeValuationPeriod->recursive = 0;
		$conditions = array();
		
		if ($this->request->is('get')) {
			$srcs = $this->request->query;
			$this->request->data['EmployeeValuationPeriod'] = $srcs;
			
			foreach($srcs as $key=>$val){
				if(!empty($val)){
					if($key == 'valuation_type'){
						$conditions['EmployeeValuationPeriod.'.$key] = $val;
					}elseif($key == 'emp'){
						$conditions['Employee.name LIKE'] = '%'.$val.'%';
					}elseif($key == 'spv'){
						$conditions['Supervisor.name LIKE'] = '%'.$val.'%';
					}elseif(in_array($key, array('period'))){
						$conditions['EmployeeValuationPeriod.'.$key.' LIKE'] = '%'.$val.'%';
					}
				}
			}
		}
		
		$this->paginate = array(
			'EmployeeValuationPeriod' => array(
				'limit' => 25,
				'conditions' => $conditions,
			)
		);

		$employeeValuationPeriods = $this->Paginator->paginate();
		
		$employees = $this->EmployeeValuationPeriod->Supervisor->find('list', array('order'=>array('name'=>'ASC')));
		
		$settings = $this->Setting->lists(array('Employee-Valuation-Type'));
		$setting['valuationType'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}.key');
		
		$this->set(compact('employeeValuationPeriods', 'employees', 'supervisors', 'setting'));
	}

	public function view($id = null) {
		if (!$this->EmployeeValuationPeriod->exists($id)) {
			throw new NotFoundException(__('Invalid employee valuation period'));
		}
		$options = array('conditions' => array('EmployeeValuationPeriod.' . $this->EmployeeValuationPeriod->primaryKey => $id));
		if ($this->EmployeeValuationPeriod->find('count', $options) < 1) {
			throw new NotFoundException(__('Invalid employee valuation period!'));
		}
		
		$act = '';
		if(isset($this->request->params['named']['act']) && $this->request->params['named']['act'] == 'preview'){
			$this->layout = false;
			$act = 'preview';
		}
		
		$employeeValuationPeriod = $this->EmployeeValuationPeriod->find('first', $options);
		$employeeValuationPeriod['EmployeeValuation'] = Set::combine($employeeValuationPeriod['EmployeeValuation'], '{n}.setting_id', '{n}');
		
		$this->User->recursive = -1;
		$empUser = $this->User->find('first', array('conditions'=>array('User.employee_id'=>$employeeValuationPeriod['EmployeeValuationPeriod']['employee_id'])));
		
		$this->EmployeePosition->recursive = -1;
		$positions = $this->EmployeePosition->find('list');
		
		switch($employeeValuationPeriod['EmployeeValuationPeriod']['valuation_type']):
			case '5476a636-d50c-4eec-ac14-3e947f000101' /* montlyPBO */:
			case '5487bca6-1150-4884-a6a5-2fd07f000101' /* montlyAO */:
			case '54e32d2a-7d40-46a2-8828-4ca50aa80505' /* montlyBM */:
			case '5499fe3a-2de4-4c6a-b9a3-2e857f000101' /* monthlyCreditRisk */:
			case '5499fe6e-cfac-4bab-9c6c-35b97f000101' /* monthlyCreditRiskAdmin */:
			case '5499fe85-16a0-4b99-8636-2f2a7f000101' /* monthlySID */:
			case '54b37663-80a8-409b-81bd-3eddc0a80535' /* montlyCustomerService */:
			case '54aa115d-a2b0-4043-b6ce-08927f000101' /* montlyTeller */:
			case '54ff8000-15d0-424b-b704-8c0e0aa80505' /* montlyTellerHead */:
			case '54aa117e-3224-4315-88c5-12727f000101' /* montlyAdminBranch */:
			case '60713fc6-bca1-11e4-9e3f-001517dcfff1' /* montlyAdminCredit */:
			case '54b47cd8-3470-4f26-b221-0e27c0a80535' /* montlyTreasury */:
			case '54b49061-2244-4574-8bfa-682ec0a80535' /* montlyBOKredit */:
			case '54b4908d-a000-4a08-bdc7-7372c0a80535' /* montlyBODana */:
			case '54b4be09-34bc-4074-8b8c-77ee7f000101' /* montlyHCM-AyuKurnia */:
			case '54b707cb-20fc-45a4-b5a7-0e27c0a80535' /* montlyHCM-DodikWirawan */:
			case '54b73d4a-05fc-42a6-b510-124ac0a80535' /* montlyHCM-Yuliaheni */:
			case '54b707a8-e098-437c-adac-0e26c0a80535' /* montlyHCM-DianKrisna */:
			case '54b76b0a-1e30-409f-b26a-1b29c0a80535' /* montlyHCM-CitaRasmini */:
			case '54b7a70e-1bd4-47a2-a4cf-2aa6c0a80535' /* montlySekretariat-AyuSilviananda */:
			case '54b855fa-b8c8-4f17-b8bd-3714c0a80535' /* montlySekretariat-IdaAyuPermata */:
			case '54beeceb-f1d8-4d2c-9812-6503c0a80535' /* montlySKAI-Manager */:
			case '54bf078d-52e8-433a-ba87-08b0c0a80535' /* montlySKAI-Credit */:
			case '54bf70b0-0158-4193-8187-17ffc0a80535' /* montlySKAI-Operational */:
			case '54f01b20-c958-4c5a-8702-d6f20aa80505' /* montlySysRep */:
			case '54c72a5d-df5c-4e5d-95ba-1843c0a80535' /* montlyAudit-It */:
			case '54bf1e52-0934-4756-b194-08b0c0a80535' /* montlyQS-Echa */:
			case '54bf268f-7438-464e-9764-0904c0a80535' /* montlyQS-Anya */:
			case '54bf26ba-dbe0-4e95-aa78-0a39c0a80535' /* montlyQS-Sri */:
			case '54bf26f1-52f4-4591-9725-0cdac0a80535' /* montlyQS-Indry */:
			case '54bface4-b9b0-4d58-b19b-238ec0a80535' /* montlyCompliance */:
			case '54bfcfef-0828-4a8d-9532-2a25c0a80535' /* montlyKabagBO */:
			//case '54bfd882-a484-4246-836b-2cdbc0a80535' /* montlyKadivOperasional */:
			case '54c0e3be-d594-4eb4-8ffd-066cc0a80535' /* montlyPengawasan */:
			case '54c0fe1d-e034-4cff-8e8d-1e52c0a80535' /* montlyMarcomm-YogaSugama */:
			case '54c0e046-a194-4814-865d-1e52c0a80535' /* montlyMarcomm-DayuMas */:
			case '54c644c5-a9ac-418f-bf82-368dc0a80535' /* montlyMarcomm-CokDewi */:
			case '54c74032-3bd4-4e5c-8c1d-0e50c0a80535' /* montlyMarcomm-Wisnu */:
			case '54ea98f5-b228-4db0-9ec1-1ee40aa80505' /* montlyLegal */:
			case '54bfc786-e13c-4e92-a534-17ffc0a80535' /* montlyCoorporateLegal */:
			case '54f03956-01f0-4d7f-96e5-b26e0aa80505' /* montlyRetailAM */:
			case '54f03945-9de0-496a-8ddb-b26e0aa80505' /* montlyRetailRM */:
			case '54f033ea-7f1c-4c49-964c-4eb90aa80505' /* montlyRetailLestariFirst */:
			case '54bfd882-a484-4246-836b-2cdbc0a80535' /* montlyKabagUmum */:
			case '54c9e05c-718c-419e-b735-43b8c0a80535' /* montlyKasKecil */:
			case '54c9de3e-c608-4e42-b9b2-4363c0a80535' /* montlyBagianUmum */:
			case '54c9e23e-1cf4-4050-a90f-43a6c0a80535' /* montlySecurity */:
			case '54c9e34d-4474-43e0-804a-4435c0a80535' /* montlyDriver */:
			case '54c9e465-9dc8-41da-a6f7-4305c0a80535' /* montlyOB */:
			case '54f0260a-130c-4821-bfe3-d6f20aa80505' /* montlyOperator */:
			case '54f02622-483c-4c5f-ad80-d6f20aa80505' /* montlyEkspedisi */:
			case '550fc5ed-59a4-4b12-a674-514a0aa80505' /* montlyKabagLegal */:
			case '54ff9850-56c4-44e6-8e3b-416a0aa80505' /* montlyITCore */:
			case '54ff982e-7ec8-4a5a-a162-4c4f0aa80505' /* monthlyITDevelopment */:
			case '5514168e-553c-4616-8624-fed90aa80505' /* monthlyKabagAdminCredit */:
			case '54adcad7-3318-4b82-95c1-49600aa80505' /* monthlyOnlyValue */:
				$this->view = 'view_monthly';
				
				$settings = $this->Setting->lists(array('Employee-Valuation-Type', 'Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Kegiatan', 'Employee-Valuation-Monthly-Test', 'Employee-Valuation-Daily-Activity'));
				$setting['Valuation']['type'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}');
				$setting['Valuation']['absensi'] = Set::combine($settings['Employee-Valuation-Monthly-Absensi'], '{s}.id', '{s}');
				$setting['Valuation']['peringatan'] = Set::combine($settings['Employee-Valuation-Monthly-Peringatan'], '{s}.id', '{s}');
				$setting['Valuation']['kegiatan'] = Set::combine($settings['Employee-Valuation-Monthly-Kegiatan'], '{s}.id', '{s}');
				$setting['Valuation']['test'] = Set::combine($settings['Employee-Valuation-Monthly-Test'], '{s}.id', '{s}');
				$setting['Valuation']['daily'] = Set::combine($settings['Employee-Valuation-Daily-Activity'], '{s}.id', '{s}');
				
				$value['absensi'] = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['absensi'], $employeeValuationPeriod['EmployeeValuation']);
				//$value['peringatan'] = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['peringatan'], $employeeValuationPeriod['EmployeeValuation']);
				$value['kegiatan'] = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['kegiatan'], $employeeValuationPeriod['EmployeeValuation']);
				$value['test'] = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['test'], $employeeValuationPeriod['EmployeeValuation']);
				$value['daily'] = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['daily'], $employeeValuationPeriod['EmployeeValuation']);
				
				if($employeeValuationPeriod['EmployeeValuationPeriod']['valuation_type'] != '54adcad7-3318-4b82-95c1-49600aa80505' /* monthlyOnlyValue */){
					$monthlyValuation = $setting['Valuation']['type'][$employeeValuationPeriod['EmployeeValuationPeriod']['valuation_type']]['val4'];
					$settings = $this->Setting->lists(array($monthlyValuation));
					$setting['Valuation']['performance'] = Set::combine($settings[$monthlyValuation], '{s}.id', '{s}');
					//$performance = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['performance'], $employeeValuationPeriod['EmployeeValuation']);
					$performance = $this->EmployeeValuationPeriod->monthly($setting['Valuation']['performance'], $employeeValuationPeriod['EmployeeValuation'], $employeeValuationPeriod['EmployeeValuationPeriod']['valuation_type']);
				}
				
				$arraySumValue = $tot = 0;
				foreach($value as $key=>$val){
					if($val['tr'] == ''){
						unset($value[$key]);
					}else{
						$tot++;
						$arraySumValue += $val['point'];
					}
				}
				
				$pointFinal['value'] = $arraySumValue/$tot;
				$matrix['value'] = $matrix['performance'] = false;
				if($pointFinal['value'] >= 3){
					$matrix['value'] = true;
				}
				
				switch($employeeValuationPeriod['EmployeeValuationPeriod']['valuation_type']) :
					case '5499fe3a-2de4-4c6a-b9a3-2e857f000101' /* monthlyCreditRisk */:
						$pointFinal['performance'] = array_sum($performance['pointCreditRisk']);
						if($pointFinal['performance'] >= 90){
							$matrix['performance'] = true;
						}
						break;
					case '5514168e-553c-4616-8624-fed90aa80505' /* monthlyKabagAdminCredit */:
						$pointFinal['performance'] = $performance['pointSum'];
						if($pointFinal['performance'] >= 75){
							$matrix['performance'] = true;
						}
						break;
					case '5476a636-d50c-4eec-ac14-3e947f000101' /* montlyPBO */:
						$pointFinal['performance'] = $performance['pointPbo'];
						if($pointFinal['performance'] >= 3){
							$matrix['performance'] = true;
						}
						break;
					case '54b4be09-34bc-4074-8b8c-77ee7f000101' /* montlyHCM-AyuKurnia */:
					case '54b707cb-20fc-45a4-b5a7-0e27c0a80535' /* montlyHCM-DodikWirawan */:
					case '54b73d4a-05fc-42a6-b510-124ac0a80535' /* montlyHCM-Yuliaheni */:
					case '54b707a8-e098-437c-adac-0e26c0a80535' /* montlyHCM-DianKrisna */:
					case '54b76b0a-1e30-409f-b26a-1b29c0a80535' /* montlyHCM-CitaRasmini */:
					//case '54b7a70e-1bd4-47a2-a4cf-2aa6c0a80535' /* montlySekretariat-AyuSilviananda */:
					//case '54b855fa-b8c8-4f17-b8bd-3714c0a80535' /* montlySekretariat-IdaAyuPermata */:
						$pointFinal['performance'] = $performance['pointSum'];
						if($pointFinal['performance'] >= 75){
							$matrix['performance'] = true;
						}
						break;
					case '54beeceb-f1d8-4d2c-9812-6503c0a80535' /* montlySKAI-Manager */:
					case '54bf078d-52e8-433a-ba87-08b0c0a80535' /* montlySKAI-Credit */:
					case '54bf70b0-0158-4193-8187-17ffc0a80535' /* montlySKAI-Operational */:
						$pointFinal['performance'] = $performance['pointSum'];
						if($pointFinal['performance'] >= 3){
							$matrix['performance'] = true;
						}
						break;
					case '54adcad7-3318-4b82-95c1-49600aa80505' /* monthlyOnlyValue */:
						$pointFinal['performance'] = 0;
						break;
					case '54ea98f5-b228-4db0-9ec1-1ee40aa80505' /* montlyLegal */:
						$pointFinal['performance'] = $performance['point'];
						if($pointFinal['performance'] >= 7 && $employeeValuationPeriod['EmployeeValuationPeriod']['period'] == 201501){
							$matrix['performance'] = true;
						}elseif($pointFinal['performance'] >= 3){
							$matrix['performance'] = true;
						}
						break;
					case '60713fc6-bca1-11e4-9e3f-001517dcfff1' /* montlyAdminCredit */:
						$pointFinal['performance'] = $performance['pointSum'];
						if($pointFinal['performance'] >= 86){
							$matrix['performance'] = true;
						}
						break;
					default:
						$pointFinal['performance'] = $performance['point'];
						if($pointFinal['performance'] >= 3){
							$matrix['performance'] = true;
						}
						break;
				endswitch;
				
				//if($employeeValuationPeriod['EmployeeValuation']['54782a7a-baac-4d07-bcf2-0d187f000101']['score']){ /* Surat Teguran */
				//	$pointFinal['value'] = 2;
				//}
				//if($employeeValuationPeriod['EmployeeValuation']['54856a95-e500-4c9a-8aec-3d787f000101']['score']){ /* Surat Peringatan */
				//	$pointFinal['value'] = 1;
				//}
				
				if($matrix['value'] && $matrix['performance']):
					$finalResult = 'A';
				elseif($matrix['value'] && !$matrix['performance']):
					$finalResult = 'B';
				elseif(!$matrix['value'] && $matrix['performance']):
					$finalResult = 'C';
				elseif(!$matrix['value'] && !$matrix['performance']):
					$finalResult = 'D';
				endif;
				break;
			case '536c4592-a484-4877-95bd-0c8c7f000101' /* contract */:
				$finalResult = $this->EmployeeValuationPeriod->finalResult($employeeValuationPeriod['EmployeeValuation'], $empUser['User']['group_id'], $employeeValuationPeriod['Employee']['join_date']);
				
				$settings = $this->Setting->lists(array('Employee-Valuation-Type', 'Employee-Valuation-Value', 'Employee-Valuation-Value-Supervisor', 'Employee-Valuation-Discipline', 'Employee-Valuation-Extracurricular', 'Employee-Valuation-Contract-Essay'), 'id', false);
				$setting['Valuation']['type'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}');
				$setting['Valuation']['value'] = Set::combine($settings['Employee-Valuation-Value'], '{s}.id', '{s}.val1');
				$setting['Valuation']['valueSupervisor'] = Set::combine($settings['Employee-Valuation-Value-Supervisor'], '{s}.id', '{s}.val1');
				$setting['Valuation']['discipline'] = Set::combine($settings['Employee-Valuation-Discipline'], '{s}.id', '{s}.val1');
				$setting['Valuation']['extracurricular'] = Set::combine($settings['Employee-Valuation-Extracurricular'], '{s}.id', '{s}.val1');
				
				$setting['Valuation']['contractEssay'] = Set::combine($settings['Employee-Valuation-Contract-Essay'], '{s}.id', '{s}.val1');
				break;
			case '536c45f3-ad0c-4d9c-9953-0c8b7f000101' /* selfPerformance*/:
				$finalResult = $this->EmployeeValuationPeriod->finalResult($employeeValuationPeriod['EmployeeValuation'], $empUser['User']['group_id'], $employeeValuationPeriod['Employee']['join_date']);
				
				$settings = $this->Setting->lists(array('Employee-Valuation-Type', 'Employee-Valuation-Value', 'Employee-Valuation-Value-Supervisor', 'Employee-Valuation-Discipline', 'Employee-Valuation-Extracurricular', 'Employee-Valuation-Self-Performance', 'Employee-Valuation-Self-Performance-Setting'));
				$setting['Valuation']['type'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}');
				$setting['Valuation']['value'] = Set::combine($settings['Employee-Valuation-Value'], '{s}.id', '{s}.val1');
				$setting['Valuation']['valueSupervisor'] = Set::combine($settings['Employee-Valuation-Value-Supervisor'], '{s}.id', '{s}.val1');
				$setting['Valuation']['discipline'] = Set::combine($settings['Employee-Valuation-Discipline'], '{s}.id', '{s}.val1');
				$setting['Valuation']['extracurricular'] = Set::combine($settings['Employee-Valuation-Extracurricular'], '{s}.id', '{s}.val1');
				
				$setting['Valuation']['selfPerformance'] = Set::combine($settings['Employee-Valuation-Self-Performance'], '{s}.id', '{s}');
				$setting['Valuation']['selfPerformanceSetting'] = Set::combine($settings['Employee-Valuation-Self-Performance-Setting'], '{s}.key', '{s}');
				break;
			case '536c45cb-8270-4388-b967-052d7f000101' /* star */:
				$finalResult = $this->EmployeeValuationPeriod->finalResult($employeeValuationPeriod['EmployeeValuation'], $empUser['User']['group_id'], $employeeValuationPeriod['Employee']['join_date']);
				
				$settings = $this->Setting->lists(array('Employee-Valuation-Type', 'Employee-Valuation-Value', 'Employee-Valuation-Value-Supervisor', 'Employee-Valuation-Discipline', 'Employee-Valuation-Extracurricular', 'Employee-Valuation-Star-Essay', 'Employee-Valuation-Star-Operational'));
				$setting['Valuation']['type'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}');
				$setting['Valuation']['value'] = Set::combine($settings['Employee-Valuation-Value'], '{s}.id', '{s}.val1');
				$setting['Valuation']['valueSupervisor'] = Set::combine($settings['Employee-Valuation-Value-Supervisor'], '{s}.id', '{s}.val1');
				$setting['Valuation']['discipline'] = Set::combine($settings['Employee-Valuation-Discipline'], '{s}.id', '{s}.val1');
				$setting['Valuation']['extracurricular'] = Set::combine($settings['Employee-Valuation-Extracurricular'], '{s}.id', '{s}.val1');
				
				$setting['Valuation']['starEssay'] = Set::combine($settings['Employee-Valuation-Star-Essay'], '{s}.id', '{s}');
				$setting['Valuation']['starTargetOperational'] = Set::combine($settings['Employee-Valuation-Star-Operational'], '{s}.id', '{s}');
				break;
			default:
				throw new NotFoundException(__('Invalid employee valuation type.'));
				break;
		endswitch;
		
		if($employeeValuationPeriod['EmployeeValuationPeriod']['valuation_type'] == '536c45cb-8270-4388-b967-052d7f000101'){
			unset($setting['Valuation']['value']['532f89cd-07f4-4c7e-a830-12c334c901bb']);
			unset($setting['Valuation']['value']['532f89d6-55b4-4cb5-866b-1aab34c901bb']);
		}
		
		$this->set(compact('value', 'performance', 'matrix', 'pointFinal', 'act', 'employeeValuationPeriod', 'empUser', 'positions', 'finalResult', 'setting'));
	}

	public function self_performance() {
		$this->EmployeeValuationPeriod->recursive = 0;
		
		$this->paginate = array(
			'EmployeeValuationPeriod' => array(
				'limit' => 25,
				'conditions' => array('EmployeeValuationPeriod.employee_id' => CakeSession::read('Auth.User.Employee.id')),
			)
		);
		
		$employeeValuationPeriods = $this->Paginator->paginate();
		
		$employees = $this->EmployeeValuationPeriod->Supervisor->find('list', array('order'=>array('name'=>'ASC')));
		
		$settings = $this->Setting->lists(array('Employee-Valuation-Type'));
		$setting['valuationType'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}.key');
		
		$this->set(compact('employeeValuationPeriods', 'employees', 'supervisors', 'setting'));
		
		$this->view = 'index';
	}

	public function add($valuationType = null) {
		if ($this->request->is('post')) {
			$this->EmployeeValuationPeriod->create();
			if(isset($this->request->data['EmployeeValuation'])){
				foreach($this->request->data['EmployeeValuation'] as $key=>$val){
					if(isset($val['followed']) && $val['followed'] == 0){
						unset($this->request->data['EmployeeValuation'][$key]);
						if(isset($val['id'])){
							$this->EmployeeValuationPeriod->EmployeeValuation->id = $val['id'];
							$this->EmployeeValuationPeriod->EmployeeValuation->delete();
						}
					}
				}
			}
			
			if ($this->EmployeeValuationPeriod->saveAll($this->request->data)) {
				$this->Employee->recursive = -1;
				$employeeSupervisor = $this->Employee->find('first', array('conditions'=>array('Employee.' . $this->Employee->primaryKey => $this->request->data['EmployeeValuationPeriod']['supervisor_id'])));
				
				if(!empty($employeeSupervisor['Employee']['phone_sms'])){
					switch($valuationType){
						case 'contract' :
							$employeeValuation = $this->Employee->find('first', array('conditions'=>array('Employee.' . $this->Employee->primaryKey => $this->request->data['EmployeeValuationPeriod']['employee_id'])));
							
							$settings['SMSBody-employeeValuation'] = $this->Setting->findById('5368a1e5-ced4-4cb8-9781-c4f50aa80505');
							
							switch($employeeSupervisor['Employee']['sex']){
								case 'L':
									$smsVar['sex'] = 'Bapak';
									break;
								case 'P':
									$smsVar['sex'] = 'Ibu';
									break;
								default :
									$smsVar['sex'] = 'Bapak/Ibu';
									break;
							}
							
							$MIS['phone'] = $employeeSupervisor['Employee']['phone_sms'];
							$MIS['tag'] = 'MIS-employees-valuation';
							$MIS['lis_aplikasi_kredit_id'] = $this->EmployeeValuationPeriod->getInsertID();
							$MIS['status'] = 'NEW';
							$MIS['creator'] = 'mis-system';
							$MIS['message'] = $LIS['sms_body'] = __($settings['SMSBody-employeeValuation']['Setting']['val1'], $smsVar['sex'], $employeeSupervisor['Employee']['name'], $employeeValuation['Employee']['name'], date('j M Y', strtotime('+3 days')));
							$LIS['query'] = __("INSERT INTO bprlestari_lis._sms_out (phone, message, tag, id_aplikasi_kredit, status, user_input, date_input)
								VALUES ('%s', '%s', 'MIS-employees-valuation', '%s', 'NEW', 'mis-system', NOW());", $employeeSupervisor['Employee']['phone_sms'], $LIS['sms_body'], $this->EmployeeValuationPeriod->getInsertID());
							break;
						case 'star' :
							$settings['SMSBody-employeeValuation'] = $this->Setting->findById('53799875-00d8-424c-9810-0b4c0aa80505');
							
							switch($employeeSupervisor['Employee']['sex']){
								case 'L':
									$smsVar['sex'] = 'Bapak';
									break;
								case 'P':
									$smsVar['sex'] = 'Ibu';
									break;
								default :
									$smsVar['sex'] = 'Bapak/Ibu';
									break;
							}
							
							$MIS['phone'] = $employeeSupervisor['Employee']['phone_sms'];
							$MIS['tag'] = 'MIS-employees-valuation';
							$MIS['lis_aplikasi_kredit_id'] = $this->EmployeeValuationPeriod->getInsertID();
							$MIS['status'] = 'NEW';
							$MIS['creator'] = 'mis-system';
							$MIS['message'] = $LIS['sms_body'] = __($settings['SMSBody-employeeValuation']['Setting']['val1'], $smsVar['sex'], $employeeSupervisor['Employee']['name'], date('j M Y', strtotime('+3 days')));
							$LIS['query'] = __("INSERT INTO bprlestari_lis._sms_out (phone, message, tag, id_aplikasi_kredit, status, user_input, date_input)
								VALUES ('%s', '%s', 'MIS-employees-valuation', '%s', 'NEW', 'mis-system', NOW());", $employeeSupervisor['Employee']['phone_sms'], $LIS['sms_body'], $this->EmployeeValuationPeriod->getInsertID());
							break;
					}
					//$this->EmployeeValuationPeriod->useDbConfig = 'LIS';
					//$this->EmployeeValuationPeriod->query($LIS['query']);
					//$this->EmployeeValuationPeriod->useDbConfig = 'default';
					
					$this->Sm->create();
					$this->Sm->save($MIS);
				}
				
				$this->Session->setFlash(__('The employee valuation period has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The employee valuation period could not be saved. Please, try again.'));
			}
		}
		$employees = $this->EmployeeValuationPeriod->Employee->find('list', array('conditions'=>array('join_date > DATE_SUB( CURDATE( ) , INTERVAL 1 YEAR )')));
		$supervisors = $this->EmployeeValuationPeriod->Supervisor->find('list');
		
		$settings = $this->Setting->lists(array('EMPLOYEE-POSITIONS', 'Employee-Valuation-Value', 'Employee-Valuation-Discipline', 'Employee-Valuation-Contract-Essay', 'Employee-Valuation-Extracurricular', 'Employee-Valuation-Self-Performance', 'Employee-Valuation-Self-Performance-Setting'));
		$setting['empPositions'] = Set::combine($settings['EMPLOYEE-POSITIONS'], '{s}.id', '{s}.val1');
		$setting['Valuation']['value'] = Set::combine($settings['Employee-Valuation-Value'], '{s}.id', '{s}.val1');
		$setting['Valuation']['discipline'] = Set::combine($settings['Employee-Valuation-Discipline'], '{s}.id', '{s}.val1');
		$setting['Valuation']['contractEssay'] = Set::combine($settings['Employee-Valuation-Contract-Essay'], '{s}.id', '{s}.val1');
		$setting['Valuation']['extracurricular'] = Set::combine($settings['Employee-Valuation-Extracurricular'], '{s}.id', '{s}.val1');
		$setting['Valuation']['selfPerformance'] = Set::combine($settings['Employee-Valuation-Self-Performance'], '{s}.id', '{s}');
		$setting['Valuation']['selfPerformanceSetting'] = Set::combine($settings['Employee-Valuation-Self-Performance-Setting'], '{s}.key', '{s}');
		
		$this->set(compact('valuationType', 'employees', 'supervisors', 'setting'));
	}

	public function edit($id = null) {
		CakeSession::write('EmployeeValuationPeriod.beforeFind.action', 'edit');
		
		if (!$this->EmployeeValuationPeriod->exists($id)) {
			throw new NotFoundException(__('Invalid employee valuation period'));
		}
		//if ($this->EmployeeValuationPeriod->find('count', array('conditions'=>array('EmployeeValuationPeriod.' . $this->EmployeeValuationPeriod->primaryKey => $id))) < 1) {
		//	throw new NotFoundException(__('Invalid employee valuation period!'));
		//}
		if ($this->request->is(array('post', 'put'))) {
			if(isset($this->request->data['EmployeeValuation'])){
				foreach($this->request->data['EmployeeValuation'] as $key=>$val){
					if(isset($val['followed']) && $val['followed'] == 0){
						unset($this->request->data['EmployeeValuation'][$key]);
						if(isset($val['id'])){
							$this->EmployeeValuationPeriod->EmployeeValuation->id = $val['id'];
							$this->EmployeeValuationPeriod->EmployeeValuation->delete();
						}
					}
				}
				if($this->request->data['EmployeeValuationPeriod']['valuation_type'] == '536c45cb-8270-4388-b967-052d7f000101'){
					if(in_array($this->request->data['EmployeeValuationPeriod']['employee_position'], array('51c7923f-41c4-4fbd-8edd-14df0aa80505', '51c79250-5a00-44d4-9e2e-14df0aa80505'))){
						unset($this->request->data['EmployeeValuation']['53735d52-4700-4920-8a94-2d3e7f000101']);
						unset($this->request->data['EmployeeValuation']['53735d65-513c-44c6-af1c-2d5c7f000101']);
						unset($this->request->data['EmployeeValuation']['53735d77-fbac-4d43-8b7f-2d1c7f000101']);
						unset($this->request->data['EmployeeValuation']['53735d96-6b2c-4c09-b70a-2db37f000101']);
						unset($this->request->data['EmployeeValuation']['53735dac-8f3c-4e6f-90c4-2d3e7f000101']);
						unset($this->request->data['EmployeeValuation']['53735dc1-3b44-4aaa-89b3-28b27f000101']);
						unset($this->request->data['EmployeeValuation']['53735dc8-357c-4e91-a6b6-28637f000101']);
					}else{
						unset($this->request->data['EmployeeValuation']['5371d3d4-855c-4e46-ae35-403e7f000101']);
					}
				}
			}
			
			if(in_array($this->request->data['EmployeeValuationPeriod']['valuation_type'], array(
				'536c4592-a484-4877-95bd-0c8c7f000101', /* contract */
				'536c45cb-8270-4388-b967-052d7f000101' /* star */
			))){
				$this->request->data['EmployeeValuationPeriod']['done'] = 1;
			}
			
			$this->EmployeeValuationPeriod->unbindModel(array(
				'belongsTo' => array('Employee', 'Supervisor', 'Editor')
			));
			if ($this->EmployeeValuationPeriod->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The employee valuation period has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The employee valuation period could not be saved. Please, try again.'));
			}
		}else{
			$options = array('conditions' => array('EmployeeValuationPeriod.' . $this->EmployeeValuationPeriod->primaryKey => $id));
			$this->request->data = $this->EmployeeValuationPeriod->find('first', $options);
			$this->request->data['EmployeeValuation'] = Set::combine($this->request->data['EmployeeValuation'], '{n}.setting_id', '{n}');
		}
		
		$this->User->recursive = -1;
		$empUser = $this->User->find('first', array('conditions'=>array('User.employee_id'=>$this->request->data['EmployeeValuationPeriod']['employee_id'])));
		$employees = $this->EmployeeValuationPeriod->Employee->find('list', array('conditions'=>array('join_date > DATE_SUB( CURDATE( ) , INTERVAL 1 YEAR )')));
		$supervisors = $this->EmployeeValuationPeriod->Supervisor->find('list');
		
		$settings = $this->Setting->lists(array('EMPLOYEE-POSITIONS', 'Employee-Valuation-Type', 'Employee-Valuation-Value-Supervisor', 'Employee-Valuation-Value', 'Employee-Valuation-Discipline', 'Employee-Valuation-Contract-Essay', 'Employee-Valuation-Star-Essay', 'Employee-Valuation-Star-Operational', 'Employee-Valuation-Extracurricular', 'Employee-Valuation-Self-Performance'));
		$setting['empPositions'] = Set::combine($settings['EMPLOYEE-POSITIONS'], '{s}.id', '{s}.val1');
		$setting['Valuation']['type'] = Set::combine($settings['Employee-Valuation-Type'], '{s}.id', '{s}.key');
		$setting['Valuation']['value'] = Set::combine($settings['Employee-Valuation-Value'], '{s}.id', '{s}.val1');
		$setting['Valuation']['valueSupervisor'] = Set::combine($settings['Employee-Valuation-Value-Supervisor'], '{s}.id', '{s}.val1');
		$setting['Valuation']['discipline'] = Set::combine($settings['Employee-Valuation-Discipline'], '{s}.id', '{s}.val1');
		$setting['Valuation']['contractEssay'] = Set::combine($settings['Employee-Valuation-Contract-Essay'], '{s}.id', '{s}.val1');
		$setting['Valuation']['starEssay'] = Set::combine($settings['Employee-Valuation-Star-Essay'], '{s}.id', '{s}');
		$setting['Valuation']['starTargetOperational'] = Set::combine($settings['Employee-Valuation-Star-Operational'], '{s}.id', '{s}');
		$setting['Valuation']['extracurricular'] = Set::combine($settings['Employee-Valuation-Extracurricular'], '{s}.id', '{s}.val1');
		$setting['Valuation']['selfPerformance'] = Set::combine($settings['Employee-Valuation-Self-Performance'], '{s}.id', '{s}');
		
		if($this->request->data['EmployeeValuationPeriod']['valuation_type'] == '536c45cb-8270-4388-b967-052d7f000101'){
			unset($setting['Valuation']['value']['532f89cd-07f4-4c7e-a830-12c334c901bb']);
			unset($setting['Valuation']['value']['532f89d6-55b4-4cb5-866b-1aab34c901bb']);
		}
		
		$this->set(compact('employees', 'empUser', 'supervisors', 'setting'));
	}

	public function rekap(){
		if ($this->request->is('post')) {
			$conditions['EmployeeValuationPeriod.done'] = 1;
			$conditions['EmployeeValuationPeriod.valuation_type'] = '536c45f3-ad0c-4d9c-9953-0c8b7f000101';
			
			foreach($this->request->data['EmployeeValuationPeriod'] as $key=>$val){
				if(!empty($val)){
					if(in_array($key, array('period'))){
						$conditions['EmployeeValuationPeriod.'.$key] = $val;
					}
				}
			}
			
			$this->EmployeeValuationPeriod->recursive = 1;
			$this->EmployeeValuationPeriod->unbindModel(array(
				'belongsTo' => array('Editor')
			));
			
			$employeeValuationPeriods = $this->EmployeeValuationPeriod->find('all', array('conditions'=>$conditions, 'order'=>'Supervisor.name ASC'));
			$this->set(compact('employeeValuationPeriods'));
		}
		
		$settings = $this->Setting->lists(array('Employee-Valuation-Self-Performance-Setting'));
		$setting['Valuation']['selfPerformanceSetting'] = Set::combine($settings['Employee-Valuation-Self-Performance-Setting'], '{s}.key', '{s}.key');
		
		$this->User->recursive = -1;
		$users = $this->User->find('list', array('fields' => array('employee_id', 'group_id')));
		
		$this->EmployeePosition->recursive = -1;
		$positions = $this->EmployeePosition->find('list');
		
		$this->set(compact('setting', 'users', 'positions'));
	}

	public function delete($id = null) {
		$this->EmployeeValuationPeriod->id = $id;
		if (!$this->EmployeeValuationPeriod->exists()) {
			throw new NotFoundException(__('Invalid employee valuation period'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->EmployeeValuationPeriod->delete()) {
			$this->Session->setFlash(__('The employee valuation period has been deleted.'));
		} else {
			$this->Session->setFlash(__('The employee valuation period could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
		//return $this->redirect(array('action' => 'index', '?'=>'valuation_type=54e32d2a-7d40-46a2-8828-4ca50aa80505&period=201502&spv=&emp='));
	}
}
