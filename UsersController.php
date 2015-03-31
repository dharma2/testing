<?php
App::uses('AppController', 'Controller');

class UsersController extends AppController {
	
	public $uses = array('User', 'WcTblpf', 'WcBrcpf', 'Setting');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('logout', 'change_password');
		if(Cakesession::read('Auth.User.group_id') == '50f7a59c-9d84-490f-a4c7-72327f000101'){
			$this->Auth->allow('login', 'logout', 'change_password', 'initDB');
		}
	}
	
	private function conditions(){
		$conditions['user'] = $conditions['group'] = array();
		if($this->Session->read('Auth.User.group_id') != '50f7a59c-9d84-490f-a4c7-72327f000101'){
			$conditions['user'] = array('User.group_id !=' => '50f7a59c-9d84-490f-a4c7-72327f000101');
			$conditions['group'] = array('Group.id !=' => '50f7a59c-9d84-490f-a4c7-72327f000101');
		}
		return $conditions;
	}

	public function login() {
		if ($this->Session->read('Auth.User')) {
			$this->Session->setFlash('You are logged in!');
			$this->redirect('/', null, false);
		}
		
		$this->User->unbindModel(array('belongsTo'=>array('Creator', 'Editor')));
		
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				
				unset($this->request->data['User']['username'], $this->request->data['User']['password']);
				$this->request->data['User']['id'] = $this->Auth->user('id');
				$this->request->data['User']['last_login'] = date('Y-m-d H:i:s');
				$this->request->data['User']['last_ip'] = CakeRequest::clientIp();
				$this->request->data['User']['last_browser'] = CakeRequest::header('User-Agent');
				$this->request->data['User']['dontUpdateModifiedandEditor'] = true;
				
				$this->User->save($this->request->data);
				
				if($this->Session->read('Auth.redirect') != '/' && $this->Session->read('Auth.redirect') != ''){
					$redirect = $this->Session->read('Auth.redirect');
					$base = (!is_null(Configure::read('App.base'))) ? Configure::read('App.base') : Configure::read('App.baseUrl');
					if(substr($redirect, 0, strlen($base)) == $base){
						$redirectUrl = '/'.substr($redirect, strlen($base));
					}else{
						$redirectUrl = $redirect;
					}
					$this->redirect($redirectUrl);
				}else{
					if($this->Session->check('Auth.User.Group.default_page')){
						$aro = array('User'=>array('id'=>$this->Auth->user('id')));
						
						$aco = $this->Session->read('Auth.User.Group.default_page');
						if($this->Acl->check($aro, $aco)){
							$this->redirect('/'.$aco);
						}
					}
				}
				
				$this->redirect($this->Auth->loginRedirect);
			} else {
				$this->Session->setFlash(__('Invalid username or password, try again'));
			}
		}
	}
	
	public function logout() {
		$this->Session->destroy();
		$this->Session->setFlash('Good-Bye');
		$this->redirect($this->Auth->logout());
	}

	public function change_password() {
		if (!$this->Session->check('Auth.User.id')) {
			$this->redirect(array('controller'=>'users', 'action'=>'login'));
		}
		
		$id = $this->Auth->user('id');
		
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				
				if($this->Session->check('Auth.User.Group.default_page')){
					$aro = array('User'=>array('id'=>$this->Auth->user('id')));
					$aco = $this->Session->read('Auth.User.Group.default_page');
					if($this->Acl->check($aro, $aco)){
						$this->redirect('/'.$aco);
					}
				}else{
					$this->redirect('/');
				}
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
			
			unset($this->request->data['User']['password']);
		}
	}

	public function index() {
		$this->User->recursive = 1;
		$conditions = $this->conditions();
		
		if ($this->request->is('get')) {
			$srcs = $this->request->query;
			$this->request->data['User'] = $srcs;
			
			foreach($srcs as $key=>$val){
				if(in_array($key, array('active', 'employee_id', 'group_id', 'ao_id')) && !empty($val)){
					$conditions['user']['User.'.$key] = $val;
				}
			}
			if(!empty($srcs['username'])){
				$conditions['user']['User.username LIKE'] = '%'.$srcs['username'].'%';
			}
		}
		$this->set('users', $this->paginate('User', $conditions['user']));
		
		$groups = $this->User->Group->find('list', array('conditions' => $conditions['group']));
		$aos = $this->WcTblpf->find('list', array(
			'fields' => array('TBLITEM', 'TBLDESC'),
			'conditions' => array('TBLSTAT'=>'A', 'TBLTBCO' => 'AOCO'),
			'order' => array('TBLDESC' => 'ASC')
		));
		$processings = $this->WcTblpf->find('list', array(
			'fields' => array('TBLITEM', 'TBLDESC'),
			'conditions' => array('TBLSTAT'=>'A', 'TBLTBCO' => 'AOAN'),
			'order' => array('TBLDESC' => 'ASC')
		));
		$employees = $this->User->Employee->find('list');
		
		$this->set(compact('groups', 'aos', 'processings', 'employees'));
	}

	public function view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
		$this->set('user', $this->User->find('first', $options));
	}

	public function add($id = null) {
		if ($this->request->is('post')) {
			if(empty($this->request->data['User']['employee_id'])){ unset ($this->request->data['User']['employee_id']); }
			$this->User->create();
			if ($this->User->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
				unset($this->request->data['User']['password'], $this->request->data['User']['verify_password']);
			}
		}
		
		$this->request->data['User']['username'] = $id;
		if(isset($this->request->params['named']['e'])){
			$this->request->data['User']['employee_id'] = $this->request->params['named']['e'];
		}
		
		$conditions = $this->conditions();
		$groups = $this->User->Group->find('list', array('conditions' => $conditions['group']));
		$aos = $this->WcTblpf->find('list', array(
			'fields' => array('TBLITEM', 'TBLDESC'),
			'conditions' => array('TBLSTAT'=>'A', 'TBLTBCO' => 'AOCO'),
			'order' => array('TBLDESC' => 'ASC')
		));
		$processings = $this->WcTblpf->find('list', array(
			'fields' => array('TBLITEM', 'TBLDESC'),
			'conditions' => array('TBLSTAT'=>'A', 'TBLTBCO' => 'AOAN'),
			'order' => array('TBLDESC' => 'ASC')
		));
		$branches = $this->WcBrcpf->find('list', array(
			'fields' => array('BRCBRCO', 'BRCDESC'),
			'conditions' => array('BRCSTAT'=>'A'),
			'order' => array('BRCDESC' => 'ASC')
		));
		$userEmployees = $this->User->find('list', array('fields' => array('employee_id', 'employee_id'), 'conditions' => array('employee_id !=' => '')));
		$employees = $this->User->Employee->find('list', array('conditions'=>array('NOT' => array('Employee.id' => $userEmployees)), 'order'=>'Employee.name ASC'));
		
		$lUserwcs = $this->User->UsersUserwc->find('list', array('fields' => array('userwc_id', 'userwc_id')));
		$userwcs = $this->User->Userwc->find('list', array('conditions'=>array('NOT' => array('Userwc.id' => $lUserwcs))));
		
		$settings = $this->Setting->lists(array('USER-SPECIAL-MENU'));
		$setting['userMenus'] = Set::combine($settings['USER-SPECIAL-MENU'], '{s}.id', '{s}.key');
		
		$this->set(compact('groups', 'aos', 'processings', 'branches', 'employees', 'userwcs', 'setting'));
	}

	public function edit($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if(empty($this->request->data['User']['employee_id'])){
				unset ($this->request->data['User']['employee_id']);
			}
			if(empty($this->request->data['User']['password']) && empty($this->request->data['User']['verify_password'])){
				unset ($this->request->data['User']['password']);
				unset ($this->request->data['User']['verify_password']);
			}
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
			unset($this->request->data['User']['password']);
		}
		
		$conditions = $this->conditions();
		$groups = $this->User->Group->find('list', array('conditions' => $conditions['group']));
		$aos = $this->WcTblpf->find('list', array(
			'fields' => array('TBLITEM', 'TBLDESC'),
			'conditions' => array('TBLSTAT'=>'A', 'TBLTBCO' => 'AOCO'),
			'order' => array('TBLDESC' => 'ASC')
		));
		$processings = $this->WcTblpf->find('list', array(
			'fields' => array('TBLITEM', 'TBLDESC'),
			'conditions' => array('TBLSTAT'=>'A', 'TBLTBCO' => 'AOAN'),
			'order' => array('TBLDESC' => 'ASC')
		));
		$branches = $this->WcBrcpf->find('list', array(
			'fields' => array('BRCBRCO', 'BRCDESC'),
			'conditions' => array('BRCSTAT'=>'A'),
			'order' => array('BRCDESC' => 'ASC')
		));
		$userEmployees = $this->User->find('list', array('fields' => array('employee_id', 'employee_id'), 'conditions' => array('employee_id !=' => '', 'employee_id !=' => $this->request->data['User']['employee_id'])));
		$employees = $this->User->Employee->find('list', array('conditions'=>array('NOT' => array('Employee.id' => $userEmployees)), 'order'=>'Employee.name ASC'));
		
		$lUserwcs = $this->User->UsersUserwc->find('list', array('fields' => array('userwc_id', 'userwc_id'), 'conditions' => array('user_id !=' => $this->request->data['User']['id'])));
		$userwcs = $this->User->Userwc->find('list', array('conditions'=>array('NOT' => array('Userwc.id' => $lUserwcs)), 'order'=>array('Userwc.WUSNAME' => 'ASC')));
		//$userwcs = $this->User->Userwc->find('list');
		
		$settings = $this->Setting->lists(array('USER-SPECIAL-MENU'));
		$setting['userMenus'] = Set::combine($settings['USER-SPECIAL-MENU'], '{s}.id', '{s}.key');
		
		$this->set(compact('groups', 'aos', 'processings', 'branches', 'employees', 'userwcs', 'setting'));
	}

	public function delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->User->delete()) {
			$this->Session->setFlash(__('User deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
public function initDB() {
	
	App::uses('ConnectionManager', 'Model');
	$dbConfig = ConnectionManager::getDataSource('default');
	$dbPrefix = $dbConfig->config['prefix'];
	
	if(!$this->User->query("TRUNCATE TABLE ".$dbPrefix."aros_acos;")){
		exit('Failed initDB');
	}

    $group = $this->User->Group;

    $group->id = '50f7a59c-9d84-490f-a4c7-72327f000101'; // ROOT Group
    $this->Acl->allow($group, 'controllers');

    $group->id = '519db4b1-670c-4ded-9b99-5c010aa80505'; // Admin Kantor Kas
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/add');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/edit');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Complains/index');
    $this->Acl->allow($group, 'controllers/Complains/add');
    $this->Acl->allow($group, 'controllers/Complains/edit');
    $this->Acl->allow($group, 'controllers/Complains/view');
    $this->Acl->allow($group, 'controllers/Complains/preview');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Helpdesks/add');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '50f7a7e6-f844-4581-a19d-76d87f000101'; // Administrasi Kredit
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/view');
    $this->Acl->allow($group, 'controllers/SipAgunans/view');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/AgingLoanComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '51676daa-398c-4fdb-ad4d-0f687f000101'; // AO
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/view');
    $this->Acl->allow($group, 'controllers/SipAgunans/view');
    $this->Acl->allow($group, 'controllers/WcCstpfs/debitur_lunas');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/BgCeks/index');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Arrears/index');
    $this->Acl->allow($group, 'controllers/ApkApplications/index');
    $this->Acl->allow($group, 'controllers/ApkApplications/view');
    $this->Acl->allow($group, 'controllers/ApkApplications/add');
    $this->Acl->allow($group, 'controllers/ApkApplications/edit');
    $this->Acl->allow($group, 'controllers/ApkApplications/next');
    $this->Acl->allow($group, 'controllers/ApkFiles/view');
    $this->Acl->allow($group, 'controllers/ApkFiles/add');
    $this->Acl->allow($group, 'controllers/ApkFiles/delete');
    $this->Acl->allow($group, 'controllers/ApkFiles/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    $this->Acl->allow($group, 'controllers/Dots/index');
    $this->Acl->allow($group, 'controllers/Dots/view');
    
    $group->id = '519db3b2-72c4-49ff-9360-5c010aa80505'; // Audit
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts');
    $this->Acl->allow($group, 'controllers/TransactionUsers');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/AgingLoanComments/add');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    $this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/add');
    $this->Acl->allow($group, 'controllers/Findings/edit');
    $this->Acl->allow($group, 'controllers/Findings/remove_employee');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/add');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/delete');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Complains/index');
    $this->Acl->allow($group, 'controllers/Complains/view');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '538ff15a-eb18-4c29-93c7-04120aa80505'; // Back Office - KABAG 
    $this->Acl->deny($group, 'controllers');
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/BgCeks');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/authentication');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/index');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/view');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/authentication');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/index');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/view');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '511b323b-5578-44e2-b195-18e87f000101'; // Back Office Kredit
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/BgCeks');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '51eca45f-c2d4-472b-bcc7-03bc0aa80505'; // Back Office Dana
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/BgCeks');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Signatures/add');
    $this->Acl->allow($group, 'controllers/Signatures/step3');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/rekap');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/index');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/view');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/add');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/edit');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/index');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/view');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '50f7a7f3-31ac-4856-9208-72ca7f000101'; // BM
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/view');
    $this->Acl->allow($group, 'controllers/SipAgunans/view');
    $this->Acl->allow($group, 'controllers/WcCstpfs/debitur_lunas');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Arrears/index');
    $this->Acl->allow($group, 'controllers/Appraisals/index');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '54d1ea09-4620-4d1d-86f5-9c440aa80505'; // Compliance
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Inventories/index');
    $this->Acl->allow($group, 'controllers/Inventories/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    
    $group->id = '519db4db-1aa0-43d8-bbeb-5c010aa80505'; // CS
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/add');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/edit');
    $this->Acl->allow($group, 'controllers/TransactionUsers');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Complains/index');
    $this->Acl->allow($group, 'controllers/Complains/add');
    $this->Acl->allow($group, 'controllers/Complains/edit');
    $this->Acl->allow($group, 'controllers/Complains/view');
    $this->Acl->allow($group, 'controllers/Complains/preview');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '52e1ca2f-6938-4863-b2ea-44c90aa80505'; // Ekspedisi
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '51ba9d8d-0a20-4a38-8df8-30eb7f000101'; // HRD
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Employees/view');
    $this->Acl->allow($group, 'controllers/Employees/add');
    $this->Acl->allow($group, 'controllers/Employees/edit');
    $this->Acl->allow($group, 'controllers/Employees/birthday');
    $this->Acl->allow($group, 'controllers/Employees/rekap');
    $this->Acl->allow($group, 'controllers/Employees/compare');
    $this->Acl->allow($group, 'controllers/Employees/document_download');
    $this->Acl->allow($group, 'controllers/Employees/document_remove');
    $this->Acl->allow($group, 'controllers/Employees/education_file_view');
    $this->Acl->allow($group, 'controllers/Employees/education_file_download');
    $this->Acl->allow($group, 'controllers/Employees/education_file_remove');
    $this->Acl->allow($group, 'controllers/EmployeePositions/tree');
    $this->Acl->allow($group, 'controllers/EmployeePositions/index');
    $this->Acl->allow($group, 'controllers/EmployeePositions/add');
    $this->Acl->allow($group, 'controllers/EmployeePositions/edit');
    $this->Acl->allow($group, 'controllers/EmployeeEducations');
    $this->Acl->allow($group, 'controllers/EmployeeEducations/delete');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '541102e9-3038-40d6-9236-73330aa80505'; // Payrool
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Employees/birthday');
    $this->Acl->allow($group, 'controllers/Employees/rekap');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '51d50fc5-ece8-4291-9da6-06300aa80505'; // IMPLEMENTATOR GROUP 1 - 
    $this->Acl->deny($group, 'controllers');

    $group->id = '51d52cad-5014-469f-bb6d-062f0aa80505'; // IMPLEMENTATOR GROUP 2 - pbo
    $this->Acl->deny($group, 'controllers');

    $group->id = '538e6e26-3cd8-4780-a49b-04120aa80505'; // IMPLEMENTATOR GROUP 3 - 
    $this->Acl->deny($group, 'controllers');

    $group->id = '51be8e58-c1bc-47c9-a364-47c80aa80505'; // IT
    $this->Acl->allow($group, 'controllers');
    $this->Acl->deny($group, 'controllers/Groups');
    $this->Acl->deny($group, 'controllers/Findings/add');
    $this->Acl->deny($group, 'controllers/Findings/edit');
    $this->Acl->deny($group, 'controllers/Findings/delete');
    $this->Acl->deny($group, 'controllers/Findings/remove_employee');
    $this->Acl->deny($group, 'controllers/FindingFiles/add');
    $this->Acl->deny($group, 'controllers/FindingFiles/delete');
    $this->Acl->deny($group, 'controllers/BgCeks/add');
    $this->Acl->deny($group, 'controllers/BgCeks/edit');
    $this->Acl->deny($group, 'controllers/Employees/compare');
    $this->Acl->deny($group, 'controllers/EmployeeValuationPeriods');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->deny($group, 'controllers/QuizQuestions');
    $this->Acl->deny($group, 'controllers/Helpdesks/delete');
    
    $group->id = '53678359-124c-4d60-9f5c-c4f40aa80505'; // HELPDESK
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Helpdesks');
    $this->Acl->allow($group, 'controllers/HelpdeskProgresses');
    $this->Acl->allow($group, 'controllers/Inventories/index');
    $this->Acl->allow($group, 'controllers/Inventories/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '517a11f3-c09c-4bbc-8295-f5200aa80505'; // Legal
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Pks');
    $this->Acl->allow($group, 'controllers/Settings/convert_number_to_words');
    $this->Acl->allow($group, 'controllers/Settings/convert_number_to_roma');
    $this->Acl->allow($group, 'controllers/Settings/month_id');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/AgingLoanComments/add');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '541791d4-78d4-48ca-b04e-a4cb0aa80505'; // Lestari First Manager
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '514c0898-df18-4ae2-a22e-77f00aa80505'; // Lestari First
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '5208a065-6d60-47f9-82c7-4b590aa80505'; // Lestari Institute
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Employees/birthday');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '54bc6a1f-6664-4679-b306-9b8c0aa80505'; // MT
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Employees/birthday');
    $this->Acl->allow($group, 'controllers/Employees/rekap');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '50f7a86a-8c34-4329-a1b6-76717f000101'; // Direksi
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Arrears/index');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Employees/birthday');
    $this->Acl->allow($group, 'controllers/Employees/rekap');
    $this->Acl->allow($group, 'controllers/RkNasabahs/index');
    $this->Acl->allow($group, 'controllers/RkNasabahs/search');
    $this->Acl->allow($group, 'controllers/RkNasabahs/view');
    $this->Acl->allow($group, 'controllers/RkRekenings/index');
    //$this->Acl->allow($group, 'controllers/RkRekenings/search');
    $this->Acl->allow($group, 'controllers/RkRekenings/view');
    $this->Acl->allow($group, 'controllers/SipPinjamans/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/search');
    $this->Acl->allow($group, 'controllers/SipPinjamans/view');
    $this->Acl->allow($group, 'controllers/WcCstpfs/debitur_lunas');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/SipPinjamans/portofolio_ao');
    $this->Acl->allow($group, 'controllers/SipAgunans/view');
    $this->Acl->allow($group, 'controllers/BgCeks/index');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    $this->Acl->allow($group, 'controllers/TransactionUsers');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/AgingLoanComments/add');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Inventories/index');
    $this->Acl->allow($group, 'controllers/Inventories/view');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Helpdesks/index');
    $this->Acl->allow($group, 'controllers/Helpdesks/view');
    $this->Acl->allow($group, 'controllers/Complains/index');
    $this->Acl->allow($group, 'controllers/Complains/view');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/rekap');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '5305989d-e8ac-49a9-9503-87b70aa80505'; // Kadiv Business
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '530a9d04-a354-4a08-bf37-87b70aa80505'; // Kadiv Operational
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/RkNasabahs/index');
    $this->Acl->allow($group, 'controllers/RkNasabahs/search');
    $this->Acl->allow($group, 'controllers/RkNasabahs/view');
    $this->Acl->allow($group, 'controllers/RkRekenings/index');
    //$this->Acl->allow($group, 'controllers/RkRekenings/search');
    $this->Acl->allow($group, 'controllers/RkRekenings/view');
    $this->Acl->allow($group, 'controllers/SipPinjamans/index');
    $this->Acl->allow($group, 'controllers/SipPinjamans/search');
    $this->Acl->allow($group, 'controllers/SipPinjamans/view');
    $this->Acl->allow($group, 'controllers/WcCstpfs/debitur_lunas');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/SipPinjamans/portofolio_ao');
    $this->Acl->allow($group, 'controllers/SipAgunans/view');
    $this->Acl->allow($group, 'controllers/BgCeks/index');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    $this->Acl->allow($group, 'controllers/TransactionUsers');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/AgingLoanComments/add');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Complains/index');
    $this->Acl->allow($group, 'controllers/Complains/view');
    $this->Acl->allow($group, 'controllers/Complains/add');
    $this->Acl->allow($group, 'controllers/Complains/edit');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/rekap');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/authentication');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '5315462b-9bd8-4e58-a1d9-42110aa80505'; // Kadiv Retail Banking
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Complains/index');
    $this->Acl->allow($group, 'controllers/Complains/view');
    $this->Acl->allow($group, 'controllers/Complains/add');
    $this->Acl->allow($group, 'controllers/Complains/edit');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '520adf4f-f1f8-4838-9170-4d4a0aa80505'; // Operator
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '50f7a7f8-3774-4dd1-bd8a-72ca7f000101'; // PBO
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/BgCeks/index');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/RkRekenings');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/index');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/view');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/sending');
    $this->Acl->allow($group, 'controllers/ConfirmExtendDeposits/resend');
    $this->Acl->allow($group, 'controllers/ConfirmExtendStatements/index');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '5417d23a-dc64-4bbc-b428-773b0aa80505'; // Kadiv Credit Risk & Special Asset Management
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/ApkApplications/index');
    $this->Acl->allow($group, 'controllers/ApkApplications/view');
    $this->Acl->allow($group, 'controllers/ApkApplications/next');
    $this->Acl->allow($group, 'controllers/ApkFiles/view');
    $this->Acl->allow($group, 'controllers/ApkFiles/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '520052e3-0f0c-4efe-a7ac-d99f0aa80505'; // Pengawasan Kredit
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/ApkApplications/index');
    $this->Acl->allow($group, 'controllers/ApkApplications/view');
    $this->Acl->allow($group, 'controllers/ApkApplications/next');
    $this->Acl->allow($group, 'controllers/ApkFiles/view');
    $this->Acl->allow($group, 'controllers/ApkFiles/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    $this->Acl->allow($group, 'controllers/Dots/index');
    $this->Acl->allow($group, 'controllers/Dots/view');
    $this->Acl->allow($group, 'controllers/Dots/add');
    $this->Acl->allow($group, 'controllers/Dots/edit');
    
    $group->id = '51381938-c32c-404d-bb58-3be70aa80505'; // Petugas APU & PPT
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Tkms/index');
    $this->Acl->allow($group, 'controllers/Tkms/add');
    $this->Acl->allow($group, 'controllers/Tkms/edit');
    $this->Acl->allow($group, 'controllers/TkmDetails/index');
    $this->Acl->allow($group, 'controllers/TkmDetails/add');
    $this->Acl->allow($group, 'controllers/TkmDetails/edit');
    $this->Acl->allow($group, 'controllers/TkmProfiles/index');
    $this->Acl->allow($group, 'controllers/TkmProfiles/add');
    $this->Acl->allow($group, 'controllers/TkmProfiles/edit');
    $this->Acl->allow($group, 'controllers/Csvs/upload');
    $this->Acl->allow($group, 'controllers/Csvs/fetch');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '51f7a949-3d20-4fc7-9f94-5ef30aa80505'; // Processing
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/Appraisals/index');
    $this->Acl->allow($group, 'controllers/Appraisals/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Arrears/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    $this->Acl->allow($group, 'controllers/Dots/index');
    $this->Acl->allow($group, 'controllers/Dots/view');
    $this->Acl->allow($group, 'controllers/Dots/add');
    $this->Acl->allow($group, 'controllers/Dots/edit');
    
    $group->id = '53180033-2be8-4122-a638-47710aa80505'; // Processing - CLP Manager
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/AgingLoans/index');
    $this->Acl->allow($group, 'controllers/AgingLoans/view');
    $this->Acl->allow($group, 'controllers/Appraisals/index');
    $this->Acl->allow($group, 'controllers/Appraisals/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/ApkApplications/index');
    $this->Acl->allow($group, 'controllers/ApkApplications/view');
    $this->Acl->allow($group, 'controllers/ApkApplications/next');
    $this->Acl->allow($group, 'controllers/ApkFiles/view');
    $this->Acl->allow($group, 'controllers/ApkFiles/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    $this->Acl->allow($group, 'controllers/Dots/index');
    $this->Acl->allow($group, 'controllers/Dots/view');
    $this->Acl->allow($group, 'controllers/Dots/add');
    $this->Acl->allow($group, 'controllers/Dots/edit');
    
    $group->id = '5188b8f9-418c-48f4-9149-248e7f000101'; // Public Relations
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Arisans');
    $this->Acl->allow($group, 'controllers/WcRekarisans');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '512f2c0b-cce0-4bef-994d-03bc0aa80505'; // Quality Service
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/WcCstpfs/debitur_lunas');
    $this->Acl->allow($group, 'controllers/WcCstpfs/nasabah_funding');
    $this->Acl->allow($group, 'controllers/SipPinjamans/portofolio_ao');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/rekap');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/PinjamanBungaNaiks');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/Complains');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Helpdesks');
    $this->Acl->allow($group, 'controllers/HelpdeskProgresses');
    $this->Acl->allow($group, 'controllers/Sms');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '52088c7e-4960-4db0-b2ee-4b160aa80505'; // Sekretariat
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/Employees/birthday');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/add');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '51e8eb5c-05f0-45d6-a31c-1b3b0aa80505'; // BI Checking
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/SipPinjamans/index');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/ApkApplications/index');
    $this->Acl->allow($group, 'controllers/ApkApplications/view');
    $this->Acl->allow($group, 'controllers/ApkApplications/next');
    $this->Acl->allow($group, 'controllers/ApkFiles/add');
    $this->Acl->allow($group, 'controllers/ApkFiles/view');
    $this->Acl->allow($group, 'controllers/ApkFiles/download');
    $this->Acl->allow($group, 'controllers/ApkFiles/delete');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
    
    $group->id = '519db4bc-61a0-4b7b-8c79-5c010aa80505'; // Teller
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/index');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/add');
    $this->Acl->allow($group, 'controllers/VirtualAccounts/edit');
    $this->Acl->allow($group, 'controllers/TransactionUsers');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/SignatureSpeciments/index');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Tbos/add');
    $this->Acl->allow($group, 'controllers/Tbos/edit');
    $this->Acl->allow($group, 'controllers/Tbos/rekap');
    //$this->Acl->allow($group, 'controllers/TboComments/add');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Helpdesks/add');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '51ba8826-0eb0-47bc-8aaf-2c117f000101'; // Treasury
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/TransactionUsers');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Tbos/index');
    $this->Acl->allow($group, 'controllers/Tbos/view');
    $this->Acl->allow($group, 'controllers/Tbos/preview');
    $this->Acl->allow($group, 'controllers/Csvs/upload');
    $this->Acl->allow($group, 'controllers/Csvs/fetch');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '520c4038-22e8-4e19-b571-4d5f0aa80505'; // Umum
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/Findings/index');
    $this->Acl->allow($group, 'controllers/Findings/view');
    $this->Acl->allow($group, 'controllers/Findings/rekap');
    $this->Acl->allow($group, 'controllers/FindingFiles/view');
    $this->Acl->allow($group, 'controllers/FindingFiles/download');
    $this->Acl->allow($group, 'controllers/FindingComments/add');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');
 
    $group->id = '54083b24-5f88-4baa-ab86-c1c80aa80505'; // Security
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '5408456d-3898-4cb5-a8ab-c1c80aa80505'; // OB/OG
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');

    $group->id = '540847e1-db5c-42d7-938f-4aaf0aa80505'; // Driver
    $this->Acl->deny($group, 'controllers');
    $this->Acl->allow($group, 'controllers/Books/index');
    $this->Acl->allow($group, 'controllers/Books/view');
    $this->Acl->allow($group, 'controllers/Employees/index');
    $this->Acl->allow($group, 'controllers/QuizClasses/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/index');
    $this->Acl->allow($group, 'controllers/QuizParticipants/rekap');
    $this->Acl->allow($group, 'controllers/QuizParticipants/report');
    $this->Acl->allow($group, 'controllers/QuizParticipants/resume');
    $this->Acl->allow($group, 'controllers/QuizParticipants/take');
    $this->Acl->allow($group, 'controllers/QuizParticipantQuestions');
    $this->Acl->allow($group, 'controllers/Letters/index');
    $this->Acl->allow($group, 'controllers/Letters/view');
    $this->Acl->allow($group, 'controllers/Letters/download');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/index');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/view');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/self_performance');
    $this->Acl->allow($group, 'controllers/EmployeeValuationPeriods/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/edit');
    $this->Acl->allow($group, 'controllers/Biodatas/image_crop');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/edit');
    $this->Acl->allow($group, 'controllers/BiodataChildrens/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/edit');
    $this->Acl->allow($group, 'controllers/BiodataEducations/delete');
    $this->Acl->allow($group, 'controllers/BiodataEducations/file_view');
    $this->Acl->allow($group, 'controllers/BiodataMutations/edit');
    $this->Acl->allow($group, 'controllers/BiodataMutations/delete');
    $this->Acl->allow($group, 'controllers/Lotteries/winner');


	$user = $this->User;

	$user->id = '519db3cf-4258-4842-9df3-5c010aa80505'; // mahendrayuda
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');
	$user->id = '51ba89e9-6158-43e3-945b-2ac57f000101'; // joselyn
	$this->Acl->allow($user, 'controllers/Sms');
	$user->id = '530d5baf-5cc0-4f83-a67c-427d0aa80505'; // ayu.kurnia
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');
	$user->id = '51ba8a53-47e4-4b68-a9de-29897f000101'; // dodik.wirawan
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');
	$user->id = '51e91746-8648-4c3a-91c6-1b3b0aa80505'; // wisnu.merthayoga
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');
	$user->id = '520041be-9e10-4fd5-b3ca-d99f0aa80505'; // putri
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');
	$user->id = '51fb34a9-9c04-4105-8d46-ca490aa80505'; // dwijayanti
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');
	$user->id = '53fc49ae-f050-4912-afdd-4a500aa80505'; // putu.fitriani
	$this->Acl->allow($user, 'controllers/Sms');
	$this->Acl->allow($user, 'controllers/Csvs/upload');
	$this->Acl->allow($user, 'controllers/Csvs/fetch');

    echo "all done";
    exit;
}
}
