<?php
/**
 * Order more gwf credits.
 * @author gizmore
 */
final class PaymentCredits_Order extends GWF_Method
{
	private $user;
	private $credits;
	
	public function getHTAccess()
	{
		return
			'RewriteRule ^purchase_credits/?$ index.php?mo=PaymentCredits&me=Order [QSA]'.PHP_EOL.
			'RewriteRule ^purchased_credits/(\d+)/?$ index.php?mo=PaymentCredits&me=Order&id=$1 [QSA]'.PHP_EOL;
	}
	
	public function execute()
	{
		$this->user = GWF_User::getStaticOrGuest();
		
		if (false !== ($id = Common::getGetInt('id', false)))
		{
			return $this->templateOrderCompleted($id);
		}

		else if (isset($_POST['order']))
		{
			return $this->onOrder();
		}
		
		else if (isset($_POST['on_order_2_x']) || isset($_POST['on_order_2']))
		{
			return $this->onOrder2();
		}
		else
		{
			return $this->templateOrder();
		}
	}
	
	############
	### Form ###
	############
	public function validate_amount(Module_PaymentCredits $m, $arg)
	{
		$min = $m->cfgMinPurchasePrice();
		$this->credits = $m->priceToCredits($arg);
		if ($arg < $min)
		{
			return $m->lang('err_min_purchase', array($m->displayPrice($min)));
		}
		return false;
	}
	
	private function form()
	{
		$m = $this->module;
		$data = array();
		$data['amount'] = array(GWF_Form::FLOAT, Common::getRequestFloat('amount', $m->cfgMinPurchasePrice()), $m->lang('th_purchase_amount'));
		$data['credits'] = array(GWF_Form::SSTRING, '', $m->lang('th_purchase_credits'));
		$data['order'] = array(GWF_Form::SUBMIT, $m->lang('btn_order'));
		return new GWF_Form($this, $data);
	}
	
	##############################
	### Step 1 - Choose amount ###
	##############################
	private function templateOrder()
	{
		$form = $this->form();
		$tVars = array(
			'form' => $form->templateY($this->module->lang('form_title_order')),
		);
		return $this->module->template('order_start.php', $tVars);
	}
	
	###############################
	### Step 2 - Choose payment ###
	###############################
	private function onOrder()
	{
		$form = $this->form();
		if (false !== ($error = $form->validate($this->module)))
		{
			return $error.$this->templateOrder();
		}
		$order = new GWF_CreditsOrder(array(
			'co_id' => '0',
			'co_uid' => $this->user->getID(),
			'co_old_credits' => $this->user->getCredits(),
			'co_credits' => $this->credits,
		));
		
		Module_Payment::saveTempOrder($order);
		
		return $this->templateChoosePayment($order);
	}

	private function templateChoosePayment(GWF_CreditsOrder $order)
	{
		$mod_pay = $this->module->payment();
		$tVars = array(
			'form' => '', #$form->templateX($this->module->lang('form_title_order_choose_payment')),
			'order' => $mod_pay->displayOrder($this->module, $order, $this->user),
		);
		return $this->module->template('order_payment.php', $tVars);
	}
	
	##############################
	### Step 3 - Start payment ###
	##############################
	private function onOrder2()
	{
		if (false === ($order = Module_Payment::getTempOrder()))
		{
			return $this->module->error('err_order').$this->templateOrder();
		}
		return $this->templateCompleteOrder($order);
	}
	
	private function templateCompleteOrder(GWF_CreditsOrder $order)
	{
		$mod_pay = $this->module->payment();
		$paysite = Common::getPost('paysite', 'xx');
		return $mod_pay->displayOrder2($this->module, $order, $this->user, $paysite);
	}
	
	##########################
	### Step 4 - Completed ###
	##########################
	private function templateOrderCompleted($id)
	{
		if (!($order = GWF_CreditsOrder::getByID($id)))
		{
			return $this->module->error('err_order').$this->templateOrder();
		}
		if ($oder->getUserID() !== $this->user->getID())
		{
			return $this->module->error('err_order').$this->templateOrder();
		}
		$tVars = array(
			'order' => $order,
			'user' => $this->user,
		);
		return $this->module->template('order_completed.php', $tVars);
	}
	
}
