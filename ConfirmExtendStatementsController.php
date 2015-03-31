<?php
App::uses('AppController', 'Controller');
class ConfirmExtendStatementsController extends AppController {

	public $components = array('Paginator');
	var $uses = array('ConfirmExtendStatement', 'WcActpf');

	public function index() {
		$conditions = array();
		if ($this->request->is('get')) {
			$srcs = $this->request->query;
			$this->request->data['ConfirmExtendStatement'] = $srcs;
			
			foreach($srcs as $key=>$val){
				if($val != ''){
					switch ($key) :
						case 'actdlrf' :
							$conditions['ConfirmExtendStatement.'.$key.' LIKE'] = '%'.$val.'%';
							break;
						case 'email' :
							$conditions['OR']['ConfirmExtendStatement.email_to LIKE'] = '%'.$val.'%';
							$conditions['OR']['ConfirmExtendStatement.email_cc LIKE'] = '%'.$val.'%';
							break;
						case 'name':
							$conditions['WcActpf.ACTNAME LIKE'] = '%'.$val.'%';
							break;
						case 'auth_status':
							switch ($val):
								case 'v':
									$conditions['ConfirmExtendStatement.auth_status'] = 1;
									break;
								case 'n':
									$conditions['ConfirmExtendStatement.auth_status NOT'] = 1;
									break;
							endswitch;
							break;
					endswitch;
				}
			}
		}
		$this->ConfirmExtendStatement->recursive = 0;
		$confirmExtendStatements = $this->Paginator->paginate('ConfirmExtendStatement', $conditions);
		
		$aro = array('User'=>array('id'=>$this->Auth->user('id')));
		$aco = 'ConfirmExtendStatements/authentication';
		$allowAuthentication = $this->Acl->check($aro, $aco);
		
		$this->set(compact('confirmExtendStatements', 'allowAuthentication'));
	}

	public function authentication($id = null) {
		if (!$this->ConfirmExtendStatement->exists($id)) {
			throw new NotFoundException(__('Invalid account statement deposit'));
		}
		
		$data['ConfirmExtendStatement']['id'] = $id;
		$data['ConfirmExtendStatement']['auth_status'] = 1;
		$data['ConfirmExtendStatement']['auth_id'] = $this->Session->read('Auth.User.id');
		$data['ConfirmExtendStatement']['auth_on'] = date('Y-m-d H:i:s');
		$data['ConfirmExtendStatement']['dontUpdateModifiedandEditor'] = true;
		if ($this->ConfirmExtendStatement->save($data)) {
			$this->Session->setFlash(__('The account statement deposit has been authenticated.'));
			return $this->redirect(array('action' => 'index'));
		} else {
			$this->Session->setFlash(__('The account statement deposit could not be authenticated. Please, try again.'));
		}
	}
	
	public function view($id = null) {
		if (!$this->ConfirmExtendStatement->exists($id)) {
			throw new NotFoundException(__('Invalid account statement deposit'));
		}
		$options = array('conditions' => array('ConfirmExtendStatement.' . $this->ConfirmExtendStatement->primaryKey => $id));
		$this->set('confirmExtendStatement', $this->ConfirmExtendStatement->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->WcActpf->recursive = -1;
			$act = $this->WcActpf->findByActdlrf($this->request->data['ConfirmExtendStatement']['actdlrf']);
			$this->request->data['ConfirmExtendStatement']['actacno'] = null;
			if(!empty($act) && in_array($act['WcActpf']['ACTAPPL'], array('DP'))){
				$this->request->data['ConfirmExtendStatement']['actacno'] = $act['WcActpf']['ACTACNO'];
			}
			
			$this->ConfirmExtendStatement->create();
			if ($this->ConfirmExtendStatement->save($this->request->data)) {
				$this->Session->setFlash(__('The account statement deposit has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The account statement deposit could not be saved. Please, try again.'));
			}
		}
	}

	public function edit($id = null) {
		if (!$this->ConfirmExtendStatement->exists($id)) {
			throw new NotFoundException(__('Invalid account statement deposit'));
		}
		if ($this->request->is(array('post', 'put'))) {
			$this->WcActpf->recursive = -1;
			$act = $this->WcActpf->findByActdlrf($this->request->data['ConfirmExtendStatement']['actdlrf']);
			$this->request->data['ConfirmExtendStatement']['actacno'] = null;
			if(!empty($act) && in_array($act['WcActpf']['ACTAPPL'], array('DP'))){
				$this->request->data['ConfirmExtendStatement']['actacno'] = $act['WcActpf']['ACTACNO'];
			}
			
			$this->request->data['ConfirmExtendStatement']['auth_status'] = 0;
			
			if ($this->ConfirmExtendStatement->save($this->request->data)) {
				$this->Session->setFlash(__('The account statement deposit has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The account statement deposit could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ConfirmExtendStatement.' . $this->ConfirmExtendStatement->primaryKey => $id));
			$this->request->data = $this->ConfirmExtendStatement->find('first', $options);
		}
	}

	public function delete($id = null) {
		$this->ConfirmExtendStatement->id = $id;
		if (!$this->ConfirmExtendStatement->exists()) {
			throw new NotFoundException(__('Invalid account statement deposit'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->ConfirmExtendStatement->delete()) {
			$this->Session->setFlash(__('The account statement deposit has been deleted.'));
		} else {
			$this->Session->setFlash(__('The account statement deposit could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function update($id = null) {
		if (!$this->ConfirmExtendStatement->exists($id)) {
			throw new NotFoundException(__('Invalid account statement deposit'));
		}
		
		$options = array('conditions' => array('ConfirmExtendStatement.' . $this->ConfirmExtendStatement->primaryKey => $id));
		$statement = $this->ConfirmExtendStatement->find('first', $options);
		
		if(empty($statement['ConfirmExtendStatement']['actacno'])){
			$this->WcActpf->recursive = -1;
			$act = $this->WcActpf->findByActdlrf($statement['ConfirmExtendStatement']['actdlrf']);
			if(!empty($act) && in_array($act['WcActpf']['ACTAPPL'], array('DP'))){
				$this->request->data['ConfirmExtendStatement']['id'] = $id;
				$this->request->data['ConfirmExtendStatement']['send_email'] = $statement['ConfirmExtendStatement']['send_email'];
				$this->request->data['ConfirmExtendStatement']['actacno'] = $act['WcActpf']['ACTACNO'];
			}
			
			$this->ConfirmExtendStatement->create();
			if ($this->ConfirmExtendStatement->save($this->request->data)) {
				$this->Session->setFlash(__('The account statement deposit has been saved.'));
			} else {
				$this->Session->setFlash(__('The account statement deposit could not be saved. Please, try again.'));
			}
			return $this->redirect(array('action' => 'index?actdlrf=' . $statement['ConfirmExtendStatement']['actdlrf']));
		}
	}
}
