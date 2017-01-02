<?php
/**
 * Pay with own gwf credits.
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
		
		# Get order
		if (false === ($gwf_token = Common::getPostString('gwf_token', false)))
		{
			return $this->payment->error('err_token');
		}
		
		if (false === ($order = GWF_Order::getByToken($gwf_token)))
		{
			return $this->payment->error('err_order');
		}
		$module = $order->getOrderModule();
		$module->onLoadLanguage();
		
		
		if ($order->isProcessed())
		{
			return $this->payment->message('err_already_done');
		}
		
		if (!$order->isCreated())
		{
			return $this->payment->error('err_order');
		}
		
		$cost = $this->module->priceToCredits($order->getOrderPriceTotal());
		$have = $this->user->getCredits();
		if ($have < $cost)
		{
			$need = $cost - $have;
			return $this->module->error('err_not_enough_credits', array($need, $order->displayOrderPriceTotal()));
		}
		
		if (!$this->user->increase('user_credits', -$cost))
		{
			return GWF_HTML::err('ERR_DATABASE', array(__FILE__, __LINE__));
		}

		return $this->payment->onExecuteOrder($module, $order);
	}

}
