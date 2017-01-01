<?php
/**
 * Order own credits and pay with another payment processor.
 * @author gizmore
 */
final class GWF_CreditsOrder extends GDO implements GWF_Orderable
{
	public function paymentCredits() { return GWF_Module::getModule($this->getOrderModuleName()); }
	
	###########
	### GDO ###
	###########
	public function getClassName() { return __CLASS__; }
	public function getTableName() { return GWF_TABLE_PREFIX.'credits_order'; }
	public function getColumnDefines()
	{
		return array(
			'co_id' => array(GDO::AUTO_INCREMENT),
			'co_uid' => array(GDO::UINT|GDO::INDEX, GDO::NOT_NULL),
			'co_old_credits' => array(GDO::UINT, GDO::NOT_NULL),
			'co_credits' => array(GDO::UINT, GDO::NOT_NULL),
				
			'user' => array(GDO::JOIN, GDO::NULL, array('GWF_User', 'user_id', 'co_uid')),
		);
	}
	
	##############
	### Static ###
	##############
	public static function getByID($id) { return self::table(__CLASS__)->selectFirstObject('*', 'co_id='.intval($id)); }

	##############
	### Getter ###
	##############
	public function getID() { return $this->getVar('co_id'); }
	public function getUserID() { return $this->getVar('co_uid'); }
	public function getOldCredits() { return $this->getVar('co_old_credits'); }
	public function getCredits() { return $this->getVar('co_credits'); }
	
	###############
	### Display ###
	###############
	public function displayPrice() { return $this->paymentCredits()->displayCreditsPrice($this->getCredits()); }
	
	#####################
	### GWF_Orderable ###
	#####################
	public function canOrder(GWF_User $user) { return true; }
	public function canRefund(GWF_User $user) { return false; }
	public function canPayWithGWF(GWF_User $user) { return false; }
	public function canAutomizeExec(GWF_User $user) { return true; }
	public function needsShipping(GWF_User $user) { return false; }
	
	public function getOrderWidth() { return 0.0; }
	public function getOrderHeight() { return 0.0; }
	public function getOrderDepth() { return 0.0; }
	public function getOrderWeight() { return 0.0; }
	
	public function getOrderModuleName() { return 'PaymentCredits'; }
	public function getOrderPrice(GWF_User $user) { return $this->paymentCredits()->creditsToPrice($this->getCredits()); }
	public function getOrderItemName(GWF_Module $module, $lang_iso) { return $module->langISO($lang_iso, 'order_title', array($this->getCredits())); }
	public function getOrderDescr(GWF_Module $module, $lang_iso) { return $module->langISO($lang_iso, 'oder_description', array($this->getCredits(), $this->displayPrice())); }
	public function getOrderStock(GWF_User $user) { return 1; }
	public function getOrderCancelURL(GWF_User $user) { return GWF_WEB_ROOT.'purchase_credits'; }
	public function getOrderSuccessURL(GWF_User $user) { return GWF_WEB_ROOT.'purchased_credits/'.$this->getID(); }
	
	public function displayOrder(GWF_Module $module)
	{
		$tVars = array(
			'order' => $this,
		);
		return $module->template('order.php', $tVars);
	}
	
	public function executeOrder(GWF_Module $module, GWF_User $user)
	{
		if (!$user->increase('user_credits', $this->getCredits()))
		{
			return false;
		}
		$module->message('msg_purchased', array($this->getCredits(), $user->getCredits(), $this->displayPrice()));
		return true;
	}
	
}
