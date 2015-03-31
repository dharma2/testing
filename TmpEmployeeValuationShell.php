<?php
App::uses('File', 'Utility');
class TmpEmployeeValuationShell extends AppShell {
	public $uses = array(
		'EmployeeValuationPeriod', 'EmployeeValuation', 'Setting',
		'TmpEmployeeValuationPbo',
		'TmpEmployeeValuationAo',
		'TmpEmployeeValuationBm',
		'TmpEmployeeValuationCreditRisk',
		'TmpEmployeeValuationCreditRiskAdmin',
		'TmpEmployeeValuationSid',
		'TmpEmployeeValuationCustomerService',
		'TmpEmployeeValuationTeller',
		'TmpEmployeeValuationTellerHead',
		'TmpEmployeeValuationAdminBranch',
		'TmpEmployeeValuationAdminCredit',
		'TmpEmployeeValuationTreasury',
		'TmpEmployeeValuationBOKredit',
		'TmpEmployeeValuationBODana',
		'TmpEmployeeValuationBOKabag',
		'TmpEmployeeValuationHcmAyuKurnia',
		'TmpEmployeeValuationHcmDodikWirawan',
		'TmpEmployeeValuationHcmYuliaheni',
		'TmpEmployeeValuationHcmDianKrisna',
		'TmpEmployeeValuationHcmCitaRasmini',
		'TmpEmployeeValuationSekretariatAyuSilviananda',
		'TmpEmployeeValuationSekretariatIdaAyuPermata',
		'TmpEmployeeValuationSkaiManager',
		'TmpEmployeeValuationSkaiCredit',
		'TmpEmployeeValuationSkaiOperational',
		'TmpEmployeeValuationSysRep',
		'TmpEmployeeValuationQsEcha',
		'TmpEmployeeValuationQsAnya',
		'TmpEmployeeValuationQsSri',
		'TmpEmployeeValuationQsIndry',
		'TmpEmployeeValuationCompliance',
		'TmpEmployeeValuationKadivOperasional',
		'TmpEmployeeValuationPengawasan',
		'TmpEmployeeValuationMarcommYogaSugama',
		'TmpEmployeeValuationMarcommDayuMas',
		'TmpEmployeeValuationMarcommCokDewi',
		'TmpEmployeeValuationAuditIt',
		'TmpEmployeeValuationMarcommWisnu',
		'TmpEmployeeValuationLegal',
		'TmpEmployeeValuationCoorporateLegal',
		'TmpEmployeeValuationRetailAm',
		'TmpEmployeeValuationRetailRm',
		'TmpEmployeeValuationRetailLestariFirst',
		'TmpEmployeeValuationUmumKabag',
		'TmpEmployeeValuationUmumKasKecil',
		'TmpEmployeeValuationUmumStaff',
		'TmpEmployeeValuationUmumSecurity',
		'TmpEmployeeValuationUmumDriver',
		'TmpEmployeeValuationUmumOb',
		'TmpEmployeeValuationUmumOperator',
		'TmpEmployeeValuationUmumEkspedisi',
		'TmpEmployeeValuationLegalKabag',
		'TmpEmployeeValuationItCore',
		'TmpEmployeeValuationItDev',
		'TmpEmployeeValuationAdminCreditKabag',
		'TmpEmployeeValuationOnlyValue'
	);
	
	public function pbo() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationPbo->recursive = 1;
		$tmps = $this->TmpEmployeeValuationPbo->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_PBO'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationPbo', '5476a636-d50c-4eec-ac14-3e947f000101', $tmps, $settings);
	}
	
	public function ao() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationAo->recursive = 1;
		$tmps = $this->TmpEmployeeValuationAo->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_AO'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationAo', '5487bca6-1150-4884-a6a5-2fd07f000101', $tmps, $settings);
	}
	
	public function bm() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationBm->recursive = 1;
		$tmps = $this->TmpEmployeeValuationBm->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Bm', 'Employee-Valuation-Monthly_BM-Target'
				)
			)
		));
		$this->tmpToValuation('TmpEmployeeValuationBm', '54e32d2a-7d40-46a2-8828-4ca50aa80505', $tmps, $settings);
	}
	
	public function credit_risk() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationCreditRisk->recursive = 1;
		$tmps = $this->TmpEmployeeValuationCreditRisk->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Monthly_Credit-Risk'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationCreditRisk', '5499fe3a-2de4-4c6a-b9a3-2e857f000101', $tmps, $settings);
	}
	
	public function credit_risk_admin() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationCreditRiskAdmin->recursive = 1;
		$tmps = $this->TmpEmployeeValuationCreditRiskAdmin->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Credit-Risk-Admin'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationCreditRiskAdmin', '5499fe6e-cfac-4bab-9c6c-35b97f000101', $tmps, $settings);
	}
	
	public function sid() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSid->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSid->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_SID'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSid', '5499fe85-16a0-4b99-8636-2f2a7f000101', $tmps, $settings);
	}
	
	public function customer_service() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationCustomerService->recursive = 1;
		$tmps = $this->TmpEmployeeValuationCustomerService->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Customer-Service'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationCustomerService', '54b37663-80a8-409b-81bd-3eddc0a80535', $tmps, $settings);
	}
	
	public function teller() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationTeller->recursive = 1;
		$tmps = $this->TmpEmployeeValuationTeller->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Teller'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationTeller', '54aa115d-a2b0-4043-b6ce-08927f000101', $tmps, $settings);
	}
	
	public function teller_head() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationTellerHead->recursive = 1;
		$tmps = $this->TmpEmployeeValuationTellerHead->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_TellerHead'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationTellerHead', '54ff8000-15d0-424b-b704-8c0e0aa80505', $tmps, $settings);
	}

	public function admin_branch() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationAdminBranch->recursive = 1;
		$tmps = $this->TmpEmployeeValuationAdminBranch->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Admin-Branch'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationAdminBranch', '54aa117e-3224-4315-88c5-12727f000101', $tmps, $settings);
	}
	
	public function admin_credit() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationAdminCredit->recursive = 1;
		$tmps = $this->TmpEmployeeValuationAdminCredit->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Admin-Credit'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationAdminCredit', '60713fc6-bca1-11e4-9e3f-001517dcfff1', $tmps, $settings);
	}
	
	public function treasury() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationTreasury->recursive = 1;
		$tmps = $this->TmpEmployeeValuationTreasury->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Monthly_Treasury'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationTreasury', '54b47cd8-3470-4f26-b221-0e27c0a80535', $tmps, $settings);
	}
	
	public function bo_kredit() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationBOKredit->recursive = 1;
		$tmps = $this->TmpEmployeeValuationBOKredit->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_BO-Kredit'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationBOKredit', '54b49061-2244-4574-8bfa-682ec0a80535', $tmps, $settings);
	}
	
	public function bo_dana() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationBODana->recursive = 1;
		$tmps = $this->TmpEmployeeValuationBODana->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_BO-Dana'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationBODana', '54b4908d-a000-4a08-bdc7-7372c0a80535', $tmps, $settings);
	}
	
	public function bo_kabag() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationBOKabag->recursive = 1;
		$tmps = $this->TmpEmployeeValuationBOKabag->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_BO-Kabag'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationBOKabag', '54bfcfef-0828-4a8d-9532-2a25c0a80535', $tmps, $settings);
	}
	
	public function hcm_ayukurnia() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationHcmAyuKurnia->recursive = 1;
		$tmps = $this->TmpEmployeeValuationHcmAyuKurnia->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_HCM-AyuKurnia'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationHcmAyuKurnia', '54b4be09-34bc-4074-8b8c-77ee7f000101', $tmps, $settings);
	}

	public function hcm_dodikwirawan() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationHcmDodikWirawan->recursive = 1;
		$tmps = $this->TmpEmployeeValuationHcmDodikWirawan->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_HCM-DodikWirawan'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationHcmDodikWirawan', '54b707cb-20fc-45a4-b5a7-0e27c0a80535', $tmps, $settings);
	}
	
	public function hcm_yuliaheni() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationHcmYuliaheni->recursive = 1;
		$tmps = $this->TmpEmployeeValuationHcmYuliaheni->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_HCM-Yuliaheni'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationHcmYuliaheni', '54b73d4a-05fc-42a6-b510-124ac0a80535', $tmps, $settings);
	}
	
	public function hcm_diankrisna() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationHcmDianKrisna->recursive = 1;
		$tmps = $this->TmpEmployeeValuationHcmDianKrisna->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_HCM-DianKrisna'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationHcmDianKrisna', '54b707a8-e098-437c-adac-0e26c0a80535', $tmps, $settings);
	}
	
	public function hcm_citarasmini() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationHcmCitaRasmini->recursive = 1;
		$tmps = $this->TmpEmployeeValuationHcmCitaRasmini->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_HCM-CitaRasmini'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationHcmCitaRasmini', '54b76b0a-1e30-409f-b26a-1b29c0a80535', $tmps, $settings);
	}
	
	public function sekretariat_ayusilviananda() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSekretariatAyuSilviananda->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSekretariatAyuSilviananda->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Sekretariat-AyuSilviananda'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSekretariatAyuSilviananda', '54b7a70e-1bd4-47a2-a4cf-2aa6c0a80535', $tmps, $settings);
	}
	
	public function sekretariat_idaayupermata() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSekretariatIdaAyuPermata->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSekretariatIdaAyuPermata->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Sekretariat-IdaAyuPermata'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSekretariatIdaAyuPermata', '54b855fa-b8c8-4f17-b8bd-3714c0a80535', $tmps, $settings);
	}
	
	public function skai_manager() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSkaiManager->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSkaiManager->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_SKAI-Manager'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSkaiManager', '54beeceb-f1d8-4d2c-9812-6503c0a80535', $tmps, $settings);
	}
	
	public function skai_credit() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSkaiCredit->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSkaiCredit->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_SKAI-Credit'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSkaiCredit', '54bf078d-52e8-433a-ba87-08b0c0a80535', $tmps, $settings);
	}
	
	public function skai_operational() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSkaiOperational->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSkaiOperational->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_SKAI-Operational'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSkaiOperational', '54bf70b0-0158-4193-8187-17ffc0a80535', $tmps, $settings);
	}
	
	public function sys_rep() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSysRep->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSysRep->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_SysRep'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSysRep', '54f01b20-c958-4c5a-8702-d6f20aa80505', $tmps, $settings);
	}
	
	public function qs_echa() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationQsEcha->recursive = 1;
		$tmps = $this->TmpEmployeeValuationQsEcha->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_QS-Echa'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationQsEcha', '54bf1e52-0934-4756-b194-08b0c0a80535', $tmps, $settings);
	}
	
	public function qs_anya() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationQsAnya->recursive = 1;
		$tmps = $this->TmpEmployeeValuationQsAnya->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_QS-Anya'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationQsAnya', '54bf268f-7438-464e-9764-0904c0a80535', $tmps, $settings);
	}
	
	public function qs_sri() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationQsSri->recursive = 1;
		$tmps = $this->TmpEmployeeValuationQsSri->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_QS-Sri'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationQsSri', '54bf26ba-dbe0-4e95-aa78-0a39c0a80535', $tmps, $settings);
	}
	
	public function qs_indry() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationQsIndry->recursive = 1;
		$tmps = $this->TmpEmployeeValuationQsIndry->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_QS-Indry'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationQsIndry', '54bf26f1-52f4-4591-9725-0cdac0a80535', $tmps, $settings);
	}
	
	public function skai_operasional() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationSkaiOperasional->recursive = 1;
		$tmps = $this->TmpEmployeeValuationSkaiOperasional->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Monthly_SKAI-Operasional'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationSkaiOperasional', '54bf70b0-0158-4193-8187-17ffc0a80535', $tmps, $settings);
	}
	
	public function compliance() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationCompliance->recursive = 1;
		$tmps = $this->TmpEmployeeValuationCompliance->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Compliance'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationCompliance', '54bface4-b9b0-4d58-b19b-238ec0a80535', $tmps, $settings);
	}
	
	public function kadiv_operasional() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationKadivOperasional->recursive = 1;
		$tmps = $this->TmpEmployeeValuationKadivOperasional->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Monthly_Kadiv-Operasional'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationKadivOperasional', '54bfd882-a484-4246-836b-2cdbc0a80535', $tmps, $settings);
	}
	
	public function pengawasan() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationPengawasan->recursive = 1;
		$tmps = $this->TmpEmployeeValuationPengawasan->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Pengawasan'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationPengawasan', '54c0e3be-d594-4eb4-8ffd-066cc0a80535', $tmps, $settings);
	}
	
	public function marcomm_yogasugama() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationMarcommYogaSugama->recursive = 1;
		$tmps = $this->TmpEmployeeValuationMarcommYogaSugama->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Marcomm-YogaSugama'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationMarcommYogaSugama', '54c0fe1d-e034-4cff-8e8d-1e52c0a80535', $tmps, $settings);
	}
	
	public function marcomm_dayumas() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationMarcommDayuMas->recursive = 1;
		$tmps = $this->TmpEmployeeValuationMarcommDayuMas->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Marcomm-DayuMas'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationMarcommDayuMas', '54c0e046-a194-4814-865d-1e52c0a80535', $tmps, $settings);
	}
	
	public function marcomm_cokdewi() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationMarcommCokDewi->recursive = 1;
		$tmps = $this->TmpEmployeeValuationMarcommCokDewi->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Marcomm-CokDewi'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationMarcommCokDewi', '54c644c5-a9ac-418f-bf82-368dc0a80535', $tmps, $settings);
	}
	
	public function marcomm_wisnu() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationMarcommWisnu->recursive = 1;
		$tmps = $this->TmpEmployeeValuationMarcommWisnu->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Marcomm-Wisnu'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationMarcommWisnu', '54c74032-3bd4-4e5c-8c1d-0e50c0a80535', $tmps, $settings);
	}
	
	public function audit_it() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationAuditIt->recursive = 1;
		$tmps = $this->TmpEmployeeValuationAuditIt->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Audit-It'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationAuditIt', '54c72a5d-df5c-4e5d-95ba-1843c0a80535', $tmps, $settings);
	}
	
	public function legal() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationLegal->recursive = 1;
		$tmps = $this->TmpEmployeeValuationLegal->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Legal'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationLegal', '54ea98f5-b228-4db0-9ec1-1ee40aa80505', $tmps, $settings);
	}
	
	public function coorporate_legal() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationCoorporateLegal->recursive = 1;
		$tmps = $this->TmpEmployeeValuationCoorporateLegal->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Coorporate-Legal'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationCoorporateLegal', '54bfc786-e13c-4e92-a534-17ffc0a80535', $tmps, $settings);
	}
	
	public function umum_kabag() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumKabag->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumKabag->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Kabag'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumKabag', '54bfd882-a484-4246-836b-2cdbc0a80535', $tmps, $settings);
	}

	public function retail_am() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationRetailAm->recursive = 1;
		$tmps = $this->TmpEmployeeValuationRetailAm->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Retail-AM'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationRetailAm', '54f03956-01f0-4d7f-96e5-b26e0aa80505', $tmps, $settings);
	}
	
	public function retail_rm() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationRetailRm->recursive = 1;
		$tmps = $this->TmpEmployeeValuationRetailRm->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Retail-RM'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationRetailRm', '54f03945-9de0-496a-8ddb-b26e0aa80505', $tmps, $settings);
	}
	
	public function retail_lestari_first() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationRetailLestariFirst->recursive = 1;
		$tmps = $this->TmpEmployeeValuationRetailLestariFirst->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Retail-LestariFirst'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationRetailLestariFirst', '54f033ea-7f1c-4c49-964c-4eb90aa80505', $tmps, $settings);
	}
	
	public function umum_kas_kecil() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumKasKecil->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumKasKecil->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Kas-Kecil'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumKasKecil', '54c9e05c-718c-419e-b735-43b8c0a80535', $tmps, $settings);
	}
	
	public function umum_staff() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumStaff->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumStaff->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Staff'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumStaff', '54c9de3e-c608-4e42-b9b2-4363c0a80535', $tmps, $settings);
	}
	
	public function umum_security() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumSecurity->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumSecurity->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Security'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumSecurity', '54c9e23e-1cf4-4050-a90f-43a6c0a80535', $tmps, $settings);
	}
	
	public function umum_ob() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumOb->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumOb->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Ob'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumOb', '54c9e465-9dc8-41da-a6f7-4305c0a80535', $tmps, $settings);
	}
	
	public function umum_operator() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumOperator->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumOperator->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Operator'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumOperator', '54f0260a-130c-4821-bfe3-d6f20aa80505', $tmps, $settings);
	}
	
	public function umum_ekspedisi() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumEkspedisi->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumEkspedisi->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Ekspedisi'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumEkspedisi', '54f02622-483c-4c5f-ad80-d6f20aa80505', $tmps, $settings);
	}
	
	public function umum_driver() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationUmumDriver->recursive = 1;
		$tmps = $this->TmpEmployeeValuationUmumDriver->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Umum-Driver'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationUmumDriver', '54c9e34d-4474-43e0-804a-4435c0a80535', $tmps, $settings);
	}
	
	public function legal_kabag() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationLegalKabag->recursive = 1;
		$tmps = $this->TmpEmployeeValuationLegalKabag->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Legal-Kabag'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationLegalKabag', '550fc5ed-59a4-4b12-a674-514a0aa80505', $tmps, $settings);
	}
	
	public function it_core() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationItCore->recursive = 1;
		$tmps = $this->TmpEmployeeValuationItCore->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_IT-Core'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationItCore', '54ff9850-56c4-44e6-8e3b-416a0aa80505', $tmps, $settings);
	}
	
	public function it_dev() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationItDev->recursive = 1;
		$tmps = $this->TmpEmployeeValuationItDev->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_IT-Dev'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationItDev', '54ff982e-7ec8-4a5a-a162-4c4f0aa80505', $tmps, $settings);
	}
	
	public function admin_credit_kabag() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationAdminCreditKabag->recursive = 1;
		$tmps = $this->TmpEmployeeValuationAdminCreditKabag->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test',
					'Employee-Valuation-Daily-Activity',
					'Employee-Valuation-Monthly_Admin-Credit-Kabag'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationAdminCreditKabag', '5514168e-553c-4616-8624-fed90aa80505', $tmps, $settings);
	}
	
	public function only_values() {
		$period = 0;
		if(isset($this->args[0])){
			$period = $this->args[0];
		}
		
		$this->TmpEmployeeValuationOnlyValue->recursive = 1;
		$tmps = $this->TmpEmployeeValuationOnlyValue->find('all', array(
			'conditions' => array(
				'period' => $period
			)
		));
		
		$this->Setting->recursive = 1;
		$settings = $this->Setting->find('all', array(
			'conditions' => array(
				'item'=>array('Employee-Valuation-Monthly-Absensi', 'Employee-Valuation-Monthly-Kegiatan',
					'Employee-Valuation-Monthly-Peringatan', 'Employee-Valuation-Monthly-Test'
				)
			)
		));
		
		$this->tmpToValuation('TmpEmployeeValuationOnlyValue', '54adcad7-3318-4b82-95c1-49600aa80505', $tmps, $settings);
	}
	
	public function tmpToValuation($tmp, $valuationType, $valuations, $settings){
		foreach($valuations as $key=>$valuation) :
			if(!empty($valuation['Employee'])):
				$id = String::UUID();;
				$data['EmployeeValuationPeriod']['id'] = $id;
				$data['EmployeeValuationPeriod']['valuation_type'] = $valuationType;
				$data['EmployeeValuationPeriod']['employee_id'] = $valuation['Employee']['id'];
				$data['EmployeeValuationPeriod']['period'] = $valuation[$tmp]['period'];
				$data['EmployeeValuationPeriod']['done'] = 1;
				$data['EmployeeValuationPeriod']['creator'] = 'cakeShell';
				
				if(!empty($settings)):
					foreach($settings as $keySetting=>$setting):
						unset($data['EmployeeValuation'][$keySetting]);
						if(isset($valuation[$tmp][$setting['Setting']['val4']]) && !is_null($valuation[$tmp][$setting['Setting']['val4']])){
							$data['EmployeeValuation'][$keySetting]['valuation_period_id'] = $id;
							$data['EmployeeValuation'][$keySetting]['setting_id'] = $setting['Setting']['id'];
							$data['EmployeeValuation'][$keySetting]['score'] = $valuation[$tmp][$setting['Setting']['val4']];
							$data['EmployeeValuation'][$keySetting]['creator'] = 'cakeShell';
						}
					endforeach;
				endif;
				
				$this->EmployeeValuationPeriod->create();
				if ($this->EmployeeValuationPeriod->saveAll($data)) {
					$this->out(__("%s - OK", $valuation[$tmp]['id']));
				}else{
					$this->out(__("%s - NOK", $valuation[$tmp]['id']));
				}
			endif;
		endforeach;
	}
}
