<?php
/**
 * Order more gwf credits.
* @author gizmore
*/
final class PaymentCredits_Pay extends GWF_Method
{
	private $user;
	private $payment;

	public function execute()
	{
		$this->user = GWF_User::getStaticOrGuest();
		$this->payment = Module_Payment::instance();
		
		if (false === ($gwf_token = Common::getPostString('gwf_token', false)))
		{
			return $this->payment->error('err_token');
		}
		
		if (false === ($order = GWF_Order::getByToken($gwf_token)))
		{
			return $this->payment->error('err_order');
		}
		
		if ($order->isProcessed())
		{
			return $this->payment->message('err_already_done');
		}
		
		if (!$order->isCreated())
		{
			return $this->payment->error('err_order');
		}
		
		$module = $order->getOrderModule();
		$module->onLoadLanguage();
		return $this->payment->onExecuteOrder($module, $order);
	}

}
