<?php
App::uses('AppController', 'Controller');
class RkStatementNasabahsController extends AppController {
	
	var $uses = array('RkStatementNasabah', 'RkRekening', 'WcTblpf');
	
	public function index() {
		$conditions = array();
		if ($this->request->is('get')) {
			$srcs = $this->request->query;
			$this->request->data['RkStatementNasabah'] = $srcs;
			
			foreach($srcs as $key=>$val){
				if($val != ''){
					switch ($key) :
						case 'account_no' :
						case 'dlrf_no' :
						case 'email_to' :
						case 'email_cc' :
							$conditions['RkStatementNasabah.'.$key.' LIKE'] = '%'.$val.'%';
							break;
						case 'ACTNAME':
							$conditions['RkRekening.'.$key.' LIKE'] = '%'.$val.'%';
							break;
						case 'ACTAOCO':
							$conditions['RkRekening.'.$key] = $val;
							break;
						case 'send_email':
							$conditions['RkStatementNasabah.'.$key] = $val;
							break;
						case 'CSTEMAL':
							$conditions['RkNasabah.'.$key.' LIKE'] = '%'.$val.'%';
							break;
					endswitch;
				}
			}
		}
		
		$this->RkRekening->unbindModel(array(
			'hasOne' => array('RkStatementNasabah'),
			'hasMany' => array('HACPF', 'TRNPF')
		));
		$this->RkStatementNasabah->Editor->unbindModel(array(
			'belongsTo' => array('Group', 'Creator', 'Editor', 'Employee'),
			'hasAndBelongsToMany' => array('Userwc', 'Setting'),
		));
		
		$this->RkStatementNasabah->recursive = 2;
		$this->set('rkStatementNasabahs', $this->paginate('RkStatementNasabah', $conditions));
		
		$wcTblpf = $this->WcTblpf->lists('AOCO');
		$tblpf['AOCO'] = Set::combine($wcTblpf['AOCO'], '{n}.TBLITEM', '{n}.TBLDESC');
		
		$this->set(compact('tblpf'));
	}

	public function view($id = null) {
		$this->RkStatementNasabah->id = $id;
		if (!$this->RkStatementNasabah->exists()) {
			throw new NotFoundException(__('Invalid rk statement nasabah'));
		}
		$this->set('rkStatementNasabah', $this->RkStatementNasabah->read(null, $id));
	}

	public function add() {
		if ($this->request->is('post')) {
			if(empty($this->request->data['RkStatementNasabah']['account_no'])){
				unset($this->request->data['RkStatementNasabah']['account_no']);
			}
			
			if(empty($this->request->data['RkStatementNasabah']['dlrf_no'])){
				unset($this->request->data['RkStatementNasabah']['dlrf_no']);
			}else{
				$this->RkRekening->recursive = -1;
				$act = $this->RkRekening->findByActdlrf($this->request->data['RkStatementNasabah']['dlrf_no']);
				if(!empty($act) && in_array($act['RkRekening']['ACTAPPL'], array('DP', 'LN'))){
					$this->request->data['RkStatementNasabah']['account_no'] = $act['RkRekening']['ACTACNO'];
				}
			}
			
			$this->request->data['RkStatementNasabah']['id'] = String::UUID();
			$this->RkStatementNasabah->create();
			if ($this->RkStatementNasabah->save($this->request->data)) {
				$this->Session->setFlash(__('Statement nasabah has been saved'));
				$this->redirect(array('action' => 'view', $this->request->data['RkStatementNasabah']['id']));
			} else {
				$this->Session->setFlash(__('The rk statement nasabah could not be saved. Please, try again.'));
			}
		}
	}

	public function edit($id = null) {
		$this->RkStatementNasabah->id = $id;
		if (!$this->RkStatementNasabah->exists()) {
			throw new NotFoundException(__('Invalid rk statement nasabah'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if(empty($this->request->data['RkStatementNasabah']['account_no'])){
				unset($this->request->data['RkStatementNasabah']['account_no']);
			}
			
			if(empty($this->request->data['RkStatementNasabah']['dlrf_no'])){
				unset($this->request->data['RkStatementNasabah']['dlrf_no']);
			}else{
				$this->RkRekening->recursive = -1;
				$act = $this->RkRekening->findByActdlrf($this->request->data['RkStatementNasabah']['dlrf_no']);
				if(!empty($act) && in_array($act['RkRekening']['ACTAPPL'], array('DP', 'LN'))){
					$this->request->data['RkStatementNasabah']['account_no'] = $act['RkRekening']['ACTACNO'];
				}
			}
			
			if ($this->RkStatementNasabah->save($this->request->data)) {
				$this->Session->setFlash(__('The rk statement nasabah has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The rk statement nasabah could not be saved. Please, try again.'));
			}
		} else {
			$this->RkRekening->unbindModel(array(
				'hasOne' => array('RkStatementNasabah'),
				'hasMany' => array('HACPF', 'TRNPF')
			));
			$this->RkStatementNasabah->unbindModel(array(
				'belongsTo' => array('Editor'),
			));
			
			$this->RkStatementNasabah->recursive = 2;
			$this->request->data = $this->RkStatementNasabah->read(null, $id);
		}
	}

	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->RkStatementNasabah->id = $id;
		if (!$this->RkStatementNasabah->exists()) {
			throw new NotFoundException(__('Invalid rk statement nasabah'));
		}
		if ($this->RkStatementNasabah->delete()) {
			$this->Session->setFlash(__('Rk statement nasabah deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Rk statement nasabah was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

	public function send_email($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->RkStatementNasabah->id = $id;
		if ($this->request->is('post')) {
			$this->RkStatementNasabah->recursive = -1;
			$statement = $this->RkStatementNasabah->read(null, $id);
			
			$send_email = ($this->passedArgs['send_email'] == 1) ? 1 : 0 ;
			
			$this->request->data['RkStatementNasabah']['id'] = $id;
			$this->request->data['RkStatementNasabah']['send_email'] = $send_email;
			
			$this->RkStatementNasabah->create();
			if ($this->RkStatementNasabah->save($this->request->data)) {
				$this->Session->setFlash(__('Statement nasabah has been saved'));
				$this->redirect(array('controller'=>'RkStatementNasabahs','action' => 'index?account_no='.$statement['RkStatementNasabah']['account_no']));
			} else {
				$this->Session->setFlash(__('The rk statement nasabah could not be saved. Please, try again.'));
			}
		}
	}
	
	public function update($id = null) {
		$this->RkStatementNasabah->id = $id;
		if (!$this->RkStatementNasabah->exists()) {
			throw new NotFoundException(__('Invalid rk statement nasabah'));
		}
		
		$statement = $this->RkStatementNasabah->read(null, $id);
		
		if(!empty($statement['RkStatementNasabah']['dlrf_no'])){
			$this->request->data['RkStatementNasabah']['id'] = $id;
			
			$this->RkRekening->recursive = -1;
			$act = $this->RkRekening->findByActdlrf($statement['RkStatementNasabah']['dlrf_no']);
			if(!empty($act) && in_array($act['RkRekening']['ACTAPPL'], array('DP', 'LN'))){
				$this->request->data['RkStatementNasabah']['account_no'] = $act['RkRekening']['ACTACNO'];
			}
			
			$this->RkStatementNasabah->create();
			if ($this->RkStatementNasabah->save($this->request->data)) {
				$this->Session->setFlash(__('Statement nasabah has been saved'));
			} else {
				$this->Session->setFlash(__('The rk statement nasabah could not be saved. Please, try again.'));
			}
			$this->redirect(array('action' => 'index?ACTDLRF=' . $statement['RkStatementNasabah']['dlrf_no']));
		}
	}

}
