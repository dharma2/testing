<?php
App::uses('Controller', 'Controller');
App::uses('CakeTime', 'Utility');

class AppController extends Controller {
	public $components = array(
		'Acl',
		'Auth' => array(
			'authenticate' => array(
				'Form' => array(
//					'fields' => array('username' => 'email')
					'scope' => array(
						'User.active' => 1,
						'OR' => array(
							'Employee.resign_date' => NULL,
							'Employee.resign_date < CURDATE()',
						)
					)
				)
			),
			'authorize' => array(
				'Actions' => array('actionPath' => 'controllers')
			),
		),
		'Session',
		'Cookie',
		'RequestHandler',
		'DebugKit.Toolbar',
	);
	
	public $helpers = array(
		'Html', 'Form', 'Session', 'Js'
	);
	
	public $uses = array('UsersSetting', 'UserLog', 'EmployeeValuationPeriod', 'Setting');
	
	public function beforeFilter() {
		$this->Auth->loginAction = array('admin'=>false, 'controller' => 'users', 'action' => 'login');
		$this->Auth->logoutRedirect = array('controller' => 'users', 'action' => 'login');
		$this->Auth->loginRedirect = array('controller' => 'pages', 'action' => 'display', 'welcome');
		$this->Auth->allow('display');
		
		$this->_setLanguage();
		
		if(!$this->Session->check('Menus')){
			$this->_setMenu();
		}
		
		$selfPerformanceStatus = false;
		$selfPerformance = $this->EmployeeValuationPeriod->find('first', array(
			'conditions' => array(
				'EmployeeValuationPeriod.valuation_type' => '536c45f3-ad0c-4d9c-9953-0c8b7f000101',
				'EmployeeValuationPeriod.employee_id' =>  $this->Session->read('Auth.User.employee_id')
			)
		));
		if(count($selfPerformance) > 0){
			$params = json_decode($selfPerformance['EmployeeValuationPeriod']['params'], true);
			$now = time();
			if((strtotime($params['supervisorInputStart']) < $now) && (strtotime($params['supervisorInputEnd']) > $now)):
				$selfPerformanceStatus = true;
			endif;
			if((strtotime($params['employeeInputStart']) < $now) && (strtotime($params['employeeInputEnd']) > $now)):
				$selfPerformanceStatus = true;
			endif;
		}
		$this->set(compact('selfPerformanceStatus'));
		
		$this->layout = 'unsemantic';
	}
	
	public function afterFilter() {
		if($this->Session->check('Auth.User.id')){
			$userLogs['user_id'] = $this->Session->read('Auth.User.id');
			$userLogs['employee_id'] = $this->Session->read('Auth.User.employee_id');
			if($this->Session->read('Auth.User.id') == '51be9451-3d94-480e-8e48-4ac90aa80505'){ /* username: dodo */
				$userLogs['employee_id'] = '83887893-da62-11e2-94c5-001517dcfff1'; /* username: made.diasta */
			}
			if($this->Session->read('Auth.User.id') == '550f6a75-a958-4513-a3b2-7fef0aa80505'){ /* username: dharma.gunawan */
				$userLogs['employee_id'] = '53aa50d8-abcc-45fa-a2ca-41ac0aa80505'; /* username: nyoman.gunawan */
			}
			$userLogs['controller'] = $this->request->controller;
			$userLogs['action'] = $this->request->action;
			$userLogs['url'] = $this->request->url;
			$userLogs['referer'] = $this->request->referer();
			$userLogs['ip'] = $this->request->clientIp();
			$userLogs['user_agent'] = $this->request->header('User-Agent');
			$this->UserLog->save($userLogs);
		}
	}
	
	/*public function isAuthorized($user) {
		// ROOT can access everything
		if (isset($user['group']) && $user['group'] === 'ROOT') {
			return true;
		}
		
		// Default deny
		return false;
	}*/
	
	private function _setMenu() {
		$this->Session->delete('Menus');
		

/**/		
		$this->UsersSetting->recursive = 1;
		$usersSettings = $this->UsersSetting->find('all', array(
			'conditions'=>array(
				'user_id' => $this->Session->read('Auth.User.id')
			),
			'fields' => array('Setting.val1')
		));
		$userSpecialMenus = array();
		foreach($usersSettings as $usersSetting){
			$userSpecialMenus = array_merge($userSpecialMenus, explode('::', $usersSetting['Setting']['val1']));
		}
		$this->Session->write('userSpecialMenus', $userSpecialMenus);
		//debug($this->Session->read('userSpecialMenus'));
/**/	

		$menus = array(
			array(
				'title' => __('System'),
				'url' => '',
				'children' => array(
					/*array(
						'title' => 'Home',
						'url' => array('controller' => '/'),
						'permission' => true
					),*/
					array(
						'title' => '<i class="icon-cogs"></i> ' . __('Setting'),
						'url' => array('controller' => 'Settings', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-cog"></i> ' . __('Icons'),
						'url' => array('controller' => 'Pages', 'action' => 'display', 'icons'),
					),
					array(
						'title' => '<i class="icon-help"></i> ' . __('Helpdesk'),
						'url' => array('controller' => 'Helpdesks', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-basket"></i> ' . __('Inventory'),
						'url' => array('controller' => 'Inventories', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-help"></i> ' . __('FAQ'),
						'url' => array('controller' => 'Faqs', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-flag"></i> ' . __('Appraisal'),
						'url' => array('controller' => 'Appraisals', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-list"></i> ' . __('Todo Lists'),
						'url' => array('controller' => 'Todos', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('CSV File'),
						'url' => array('controller' => 'Csvs', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-mail"></i> ' . __('SMS'),
						'url' => array('controller' => 'sms', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-puzzle"></i> ' . __('Checklists File'),
						'url' => array('controller' => 'SipBerkasLists', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-database"></i> ' . __('WinCore Database'),
						'url' => '',
						'children' => array(
							array(
								'title' => '<i class="icon-database"></i> ' . __('BRCPF - WinCore Branch Code'),
								'url' => array('controller' => 'WcBrcpfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> CS1PF - WinCore Customer Address',
								'url' => array('controller' => 'WcCs1pfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('CSTPF - WinCore Basic Customer'),
								'url' => array('controller' => 'WcCstpfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('LNYPF - WinCore Loan Type'),
								'url' => array('controller' => 'WcLnypfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('MC1PF - WinCore Sub Collateral'),
								'url' => array('controller' => 'WcMc1pfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('MCOPF - WinCore Collateral'),
								'url' => array('controller' => 'WcMcopfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('MDLPF - WinCore Deposito and Loan Master File'),
								'url' => array('controller' => 'WcMdlpfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('MFCPF - WinCore Facility Master'),
								'url' => array('controller' => 'WcMfcpfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('ML2PF - [Lokasi Agunan]'),
								'url' => array('controller' => 'WcMl2pfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('OTYPF - WinCore Collateral Type'),
								'url' => array('controller' => 'WcOtypfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('RMCPF - WinCore Register Collateral & Facilities'),
								'url' => array('controller' => 'WcRmcpfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('SC1PF - WinCore Administration Collateral New Field'),
								'url' => array('controller' => 'WcSc1pfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('SCOPF - WinCore Administration Collateral'),
								'url' => array('controller' => 'WcScopfs', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-database"></i> ' . __('TBLPF - WinCore Table Code'),
								'url' => array('controller' => 'WcTblpfs', 'action' => 'index'),
							)
						)
					),
				)
			),
			array(
				'title' => __('Users'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<i class="icon-user"></i> ' . __('User'),
						'url' => array('controller' => 'Users', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('User Log'),
						'url' => array('controller' => 'UserLogs', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-users"></i> ' . __('Group'),
						'url' => array('controller' => 'Groups', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-vcard"></i> ' . __('Employee'),
						'url' => array('controller' => 'Employees', 'action' => 'index'),
						'separator' => true,
						'children' => array(
							array(
								'title' => '<i class="icon-vcard"></i> ' . __('List'),
								'url' => array('controller' => 'Employees', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-users"></i> ' . __('Position'),
								'url' => array('controller' => 'EmployeePositions', 'action' => 'tree'),
							),
							array(
								'title' => '<i class="icon-health"></i> ' . __('Valuation'),
								'url' => array('controller' => 'EmployeeValuationPeriods', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-calendar"></i> ' . __('Birhtday'),
								'url' => array('controller' => 'Employees', 'action' => 'birthday'),
							),
							array(
								'title' => '<i class="icon-cogs"></i> ' . __('Compare'),
								'url' => array('controller' => 'Biodatas', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-pie"></i> ' . __('Rekap'),
								'url' => array('controller' => 'Employees', 'action' => 'rekap', 'contract'),
							)
						)
					),
					array(
						'title' => '<i class="icon-chart"></i> ' . __('Transaction'),
						'url' => array('controller' => 'TransactionUsers', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-pending"></i> ' . __('TBO'),
						'url' => array('controller' => 'Tbos', 'action' => 'index', '?'=>array('complete'=>'incomplete')),
					),
				)
			),
			array(
				'title' => __('Library'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<i class="icon-archive"></i> ' . __('Book'),
						'url' => array('controller' => 'Books', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('Borrower'),
						'url' => array('controller' => 'BookBorrowers', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('Author'),
						'url' => array('controller' => 'Authors', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-print"></i> ' . __('Publisher'),
						'url' => array('controller' => 'Publishers', 'action' => 'index'),
					),
				)
			),
			array(
				'title' => __('Customers'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<span class="icon-thumbs-down"></span> ' . __('Complain'),
						'url' => array('controller' => 'Complains', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<span class="icon-apply"></span> ' . __('Specimen Signature'),
						'url' => array('controller' => 'SignatureSpeciments', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<span class="icon-address"></span> ' . __('Authorization Letter'),
						'url' => array('controller' => 'AuthorizationLetters', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<span class="icon-user"></span> ' . __('Debitur Lunas'),
						'url' => array('controller' => 'WcCstpfs', 'action' => 'debitur_lunas'),
					),
					array(
						'title' => '<span class="icon-user"></span> ' . __('Nasabah Funding'),
						'url' => array('controller' => 'WcCstpfs', 'action' => 'nasabah_funding'),
					),
					array(
						'title' => '<span class="icon-user"></span> ' . __('Pinjaman Bunga Naik'),
						'url' => array('controller' => 'PinjamanBungaNaiks', 'action' => 'index'),
					),
				)
			),
			array(
				'title' => __('Account'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<span class="icon-address"></span> ' . __('Pemenang Jumbo2014 III'),
						'url' => array('controller' => 'Lotteries', 'action' => 'winner'),
						'separator' => true,
					),
					array(
						'title' => '<span class="icon-user"></span> ' . __('Customer Data'),
						'url' => array('controller' => 'RkNasabahs', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-puzzle"></span> ' . __('Account Number New - Old'),
						'url' => array('controller' => 'WcAczpfs', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-puzzle"></span> ' . __('BG/Cek'),
						'url' => array('controller' => 'BgCeks', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-puzzle"></span> ' . __('Amortization'),
						'url' => array('controller' => 'Amortisasis', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-puzzle"></span> ' . __('Executing'),
						'url' => array('controller' => 'Executings', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<span class="icon-cart"></span> ' . __('Rekening Regular Social Gathering'),
						'url' => array('controller' => 'WcRekarisans', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-cart"></span> ' . __('Mapping Regular Social Gathering'),
						'url' => array('controller' => 'Arisans', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-vcard"></i> ' . __('Customer Statement'),
						'url' => array('controller' => 'RkStatementNasabahs', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-mail"></i> ' . __('Account Mutation'),
						'url' => array('controller' => 'RkRekenings', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-warning"></i> ' . __('TKM'),
						'url' => array('controller' => 'Tkms', 'action' => 'index')
					),
					array(
						'title' => '<i class="icon-warning"></i> ' . __('TKM Detail'),
						'url' => array('controller' => 'TkmDetails', 'action' => 'index')
					),
					array(
						'title' => '<i class="icon-warning"></i> ' . __('TKM Profile'),
						'url' => array('controller' => 'TkmProfiles', 'action' => 'index'),
						'separator' => true
					),
					array(
						'title' => '<i class="icon-mail"></i> ' . __('Virtual Account'),
						'url' => array('controller' => 'VirtualAccounts', 'action' => 'index'),
					),
				)
			),
			array(
				'title' => __('Deposit'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<span class="icon-mail"></span> ' . __('Confirmation & Extension Deposit'),
						'url' => array('controller' => 'ConfirmExtendDeposits', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-vcard"></span> ' . __('Account Statement Deposit'),
						'url' => array('controller' => 'ConfirmExtendStatements', 'action' => 'index'),
						'separator' => true,
					),
				)
			),
			array(
				'title' => __('Audit'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<span class="icon-user"></span> ' . __('Letters'),
						'url' => array('controller' => 'Letters', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-user"></span> ' . __('Finding'),
						'url' => array('controller' => 'Findings', 'action' => 'index'),
					),
					array(
						'title' => '<span class="icon-user"></span> ' . __('Pemeriksaan - New'),
						'url' => array('controller' => 'AuditExaminations', 'action' => 'index'),
					),
				)
			),
			array(
				'title' => __('Loan'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<i class="icon-puzzle"></i> ' . __('Loan File'),
						'url' => array('controller' => 'SipPinjamans', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-calendar"></i> ' . __('Aging Loan'),
						'url' => array('controller' => 'AgingLoans', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('Potofolio AO'),
						'url' => array('controller' => 'SipPinjamans', 'action' => 'portofolio_ao'),
					),
					array(
						'title' => '<i class="icon-warning"></i> ' . __('Laporan Tunggakan'),
						'url' => array('controller' => 'Arrears', 'action' => 'index'),
					),
				)
			),
			array(
				'title' => __('Legal'),
				'url' => '',
				'children' => array(
					/*array(
						'title' => '<i class="icon-file"></i> ' . __('PK PT'),
						'url' => array('controller' => 'Pks', 'action' => 'index', '51412b84-e990-4f04-ae89-247d7f000101'),
					),*/
					array(
						'title' => '<i class="icon-file"></i> ' . __('PK IL'),
						'url' => array('controller' => 'Pks', 'action' => 'index', '51412bb7-58d8-4c5b-a1c5-23657f000101'),
					),
					/*array(
						'title' => '<i class="icon-file"></i> ' . __('PK PT IL'),
						'url' => array('controller' => 'Pks', 'action' => 'index', '51412bfd-ab40-4ae3-8c8d-0f747f000101'),
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('PK Perpanjangan PT'),
						'url' => array('controller' => 'Pks', 'action' => 'index', '51412c27-b6c4-412a-982f-22ce7f000101'),
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('PK Perpanjangan PT IL'),
						'url' => array('controller' => 'Pks', 'action' => 'index', '51412c49-de7c-48be-bc4d-1fe77f000101'),
					),*/
				)
			),
			array(
				'title' => __('Credit'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<i class="icon-warning"></i> ' . __('DOT'),
						'url' => array('controller' => 'Dots', 'action' => 'index'),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('Credit Application - NEW'),
						'url' => array('controller' => 'ApkApplications', 'action' => 'index'),
						'children' => array(
							array(
								'title' => '<i class="icon-address"></i> ' . __('Cek DOT'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a55f4b-3568-48a7-a6df-0c5c34c901bb'),
							),
							array(
								'title' => '<i class="icon-address"></i> ' . __('List Application Credit'),
								'url' => array('controller' => 'ApkApplications', 'action' => 'index', '51a55f4b-3568-48a7-a6df-0c5c34c901bb'),
							),
							array(
								'title' => '<i class="icon-file-add"></i> ' . __('Registration'),
								'url' => array('controller' => 'ApkApplications', 'action' => 'add'),
							),
							array(
								'title' => '<i class="icon-flag"></i> ' . __('Prekomite'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a56062-2624-4ff6-9e3c-066834c901bb'),
							),
							array(
								'title' => '<i class="icon-cogs"></i> ' . __('Analysis'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a56092-4294-4c9d-9cf4-0c5a34c901bb'),
							),
							array(
								'title' => '<i class="icon-thumbs-up"></i> ' . __('Approved'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a5609f-13c8-49a8-b261-0c5b34c901bb'),
							),
							array(
								'title' => '<i class="icon-thumbs-down"></i> ' . __('Rejected'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a560a9-a848-4ff9-9350-066934c901bb'),
							),
							array(
								'title' => '<i class="icon-list"></i> ' . __('All'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index'),
							),
						),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('Credit Application'),
						'url' => array('controller' => 'CreditApplications', 'action' => 'index'),
						'children' => array(
							array(
								'title' => '<i class="icon-file-add"></i> ' . __('Registration'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a55f4b-3568-48a7-a6df-0c5c34c901bb'),
							),
							array(
								'title' => '<i class="icon-flag"></i> ' . __('Prekomite'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a56062-2624-4ff6-9e3c-066834c901bb'),
							),
							array(
								'title' => '<i class="icon-cogs"></i> ' . __('Analysis'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a56092-4294-4c9d-9cf4-0c5a34c901bb'),
							),
							array(
								'title' => '<i class="icon-thumbs-up"></i> ' . __('Approved'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a5609f-13c8-49a8-b261-0c5b34c901bb'),
							),
							array(
								'title' => '<i class="icon-thumbs-down"></i> ' . __('Rejected'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index', '51a560a9-a848-4ff9-9350-066934c901bb'),
							),
							array(
								'title' => '<i class="icon-list"></i> ' . __('All'),
								'url' => array('controller' => 'CreditApplications', 'action' => 'index'),
							),
						),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('Roya'),
						'url' => array('controller' => 'Royas', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-file"></i> ' . __('Polis Cancellation'),
						'url' => array('controller' => 'PolisCancellations', 'action' => 'index'),
					),
					/*array(
						'title' => '<i class="icon-file"></i> ' . __(''),
						'url' => array('controller' => '', 'action' => 'index'),
					),*/
				)
			),
			array(
				'title' => __('Quiz'),
				'url' => '',
				'children' => array(
					array(
						'title' => '<i class="icon-puzzle"></i> ' . __('Class'),
						'url' => array('controller' => 'QuizClasses', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-calendar"></i> ' . __('Topic'),
						'url' => array('controller' => 'QuizTopics', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('Question'),
						'url' => array('controller' => 'QuizQuestions', 'action' => 'index'),
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('Participant'),
						'url' => array('controller' => 'QuizParticipants', 'action' => 'index'),
						'children' => array(
							array(
								'title' => '<i class="icon-file"></i> ' . __('List'),
								'url' => array('controller' => 'QuizParticipants', 'action' => 'index'),
							),
							array(
								'title' => '<i class="icon-chart"></i> ' . __('Rekap'),
								'url' => array('controller' => 'QuizParticipants', 'action' => 'rekap'),
							),
						),
						'separator' => true,
					),
					array(
						'title' => '<i class="icon-user"></i> ' . __('Personality Test'),
						'url' => array('controller' => 'Personalities', 'action' => 'index'),
					),
				)
			),
		);
		
		if(!$this->Auth->user('id')){
			$menus[] =
				array(
					'title' =>  __('Login'),
					'url' => array('controller' => 'users', 'action'=>'login'),
					'permission' => true
				);
		}else{
			/*$menus[] =
				array(
					'title' => 'Logout - ' . $this->Auth->user('username'),
					'url' => array('controller' => 'users', 'action'=>'logout'),
					'permission' => true
				);*/
			$aro = array('User'=>array('id'=>$this->Auth->user('id')));
			foreach($menus as $kMenu => $vMenu){
				if(isset($vMenu['children'])){
					if(!isset($menus[$kMenu]['permission']))$menus[$kMenu]['permission'] = false;
					foreach($vMenu['children'] as $kChild1 => $vChild1){
						//if(!isset($vChild1['separator']) && $vChild1['separator'] == false){
							if(isset($vChild1['children'])){
								if(!isset($menus[$kMenu]['children'][$kChild1]['permission']))$menus[$kMenu]['children'][$kChild1]['permission'] = false;
								foreach($vChild1['children'] as $kChild2 => $vChild2){
									//if(!isset($vChild2['separator']) && $vChild2['separator'] == false){
										$pChild2 = $this->Acl->check($aro, $vChild2['url']['controller'].'/'.$vChild2['url']['action']);
										if($pChild2 == false){
											if(in_array($vChild2['url']['controller'].'/'.$vChild2['url']['action'], $this->Session->read('userSpecialMenus'))){
												$pChild2 = true;
											}
										}
										if(!isset($menus[$kMenu]['children'][$kChild1]['children'][$kChild2]['permission']))$menus[$kMenu]['children'][$kChild1]['children'][$kChild2]['permission'] = $pChild2;
										if($pChild2 == true){
											$menus[$kMenu]['children'][$kChild1]['permission'] = true;
											$menus[$kMenu]['permission'] = true;
										}
									//}
								}
							}
							$aco = (isset($vChild1['url']['controller'])) ? $vChild1['url']['controller'] : '';
							$aco .= (isset($vChild1['url']['action'])) ? '/'.$vChild1['url']['action'] : '';
							$pChild1 = $this->Acl->check($aro, $aco);
							if($pChild1 == false){
								if(in_array($aco, $this->Session->read('userSpecialMenus'))){
									$pChild1 = true;
								}
							}
							if(!isset($menus[$kMenu]['children'][$kChild1]['permission']))$menus[$kMenu]['children'][$kChild1]['permission'] = $pChild1;
							if($pChild1 == true){
								$menus[$kMenu]['permission'] = true;
							}
						//}
						if(!isset($vChild1['permission'])) $vChild1['permission'] = false;
						if($vChild1['permission'] == true){
							$menus[$kMenu]['permission'] = true;
						}
					}
				}else{
					$acoMenu = (isset($vMenu['url']['controller'])) ? $vMenu['url']['controller'] : '';
					$acoMenu .= (isset($vMenu['url']['action'])) ? '/'.$vMenu['url']['action'] : '';
					
					if($acoMenu != '' && !isset($vMenu['permission'])){
						$menus[$kMenu]['permission'] = $this->Acl->check($aro, $acoMenu);
						if($menus[$kMenu]['permission'] == false){
							if(in_array($acoMenu, $this->Session->read('userSpecialMenus'))){
								$menus[$kMenu]['permission'] = true;
							}
						}
					}
				}
			}
			$this->Session->write('Menus', $menus);
		}
	}
	
	private function _setLanguage() {
		//if the cookie was previously set, and Config.language has not been set
		//write the Config.language with the value from the Cookie
		if ($this->Cookie->read('lang') && !$this->Session->check('Config.language')) {
			$this->Session->write('Config.language', $this->Cookie->read('lang'));
		}
		//if the user clicked the language URL
		else if ( isset($this->params['language']) && ($this->params['language'] !=  $this->Session->read('Config.language'))) {
			//then update the value in Session and the one in Cookie
			$this->Session->write('Config.language', $this->params['language']);
			$this->Cookie->write('lang', $this->params['language'], false, '20 days');
			
			$this->_setMenu();
		}
	}
}
