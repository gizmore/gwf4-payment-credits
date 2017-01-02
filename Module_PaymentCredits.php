<?php
require_once GWF_PATH.'module/Payment/GWF_PaymentModule.php';
/**
 * Pay with own credits.
 * Buy own credits.
 * @author gizmore
 * @license MIT
 */
final class Module_PaymentCredits extends GWF_PaymentModule
{
	##################
	### GWF_Module ###
	##################
	public function getVersion() { return 4.00; }
	public function getClasses() { return array('GWF_CreditsOrder'); }
	public function onLoadLanguage() { return $this->loadLanguage('lang/payment_credits'); }

	##############
	### Config ###
	##############
	public function onInstall($dropTable)
	{
		return parent::onInstall($dropTable).
		GWF_ModuleLoader::installVars($this, array(
			'paycreds_min_purchase' => array('5.00', 'float', '0.00', '1000.00'),
			'paycreds_rate' => array('0.01', 'float', '0.001', '0.1'),
		));
	}
	public function cfgMinPurchasePrice() { return $this->getModuleVarFloat('paycreds_min_purchase', 5.00); }
	public function cfgConversionRate() { return $this->getModuleVarFloat('paycreds_rate', 0.01); }
	public function cfgConversionRateToCurrency() { return $this->cfgConversionRate(); }
	public function cfgConversionRateToCredits() { return 1 / $this->cfgConversionRate(); }
	
	###############
	### Convert ###
	###############
	public function priceToCredits($price) { return floor($this->cfgConversionRateToCredits() * $price); }
	public function creditsToPrice($credits) { return round($this->cfgConversionRateToCurrency() * $credits, 2); }
	public function displayPrice($price) { return sprintf('%.02f %s', $price, $this->payment()->cfgCurrency()); }
	public function displayCreditsPrice($credits) { return $this->displayPrice($this->creditsToPrice($credits)); }
	
	###############
	### Startup ###
	###############
	public function onStartup()
	{
		GWF_PaymentModule::registerPaymentModule($this);
	}
	
	public function execute($methodname)
	{
		$payment = GWF_Module::loadModuleDB('Payment');
		$payment->onInclude();
		return parent::execute($methodname);
	}
	
	#########################
	### GWF_PaymentModule ###
	#########################
	public function getSiteName() { return 'GWF'; }
	public function getSiteNameToken() { return 'gwf'; }
	public function getSupportedCurrencies() { return array('EUR', 'USD'); }
	public function canAfford(GWF_User $user, $price) { return $user->getCredits() >= $this->priceToCredits($price); }
	public function canOrder(GWF_User $user, GWF_Orderable $gdo) { return (!($gdo instanceof GWF_CreditsOrder)); }
	public function displayPaysiteButton(GWF_Module $module, GWF_Order $order, GWF_Orderable $gdo, GWF_User $user)
	{
		$tVars = array(
			'form_action' => GWF_WEB_ROOT.'index.php?mo=PaymentCredits&me=Pay',
			'form_hidden' => $this->getHiddenData($module, $order, $gdo, $user),
		);
		return $this->template('paybutton.php', $tVars);
	}
	private function getHiddenData(GWF_Module $module, GWF_Order $order, GWF_Orderable $gdo, GWF_User $user) { return GWF_Form::hidden('gwf_token', $order->getOrderToken()); }
	
	###############
	### Sidebar ###
	###############
	public function sidebarContent($bar)
	{
		if ($bar === 'right')
		{
			return $this->templateSidebar();
		}
	}
	
	private function templateSidebar()
	{
		$user = GWF_User::getStaticOrGuest();
		$this->onLoadLanguage();
		$tVars = array(
			'user' => $user,
			'hrefPurchase' => GWF_WEB_ROOT.'purchase_credits',
			'credits' => $user->getCredits(),
		);
		return $this->template('sidebar.php', $tVars);
	}
}
