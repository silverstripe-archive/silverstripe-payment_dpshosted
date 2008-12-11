<?php
class DPSHostedPaymentForm extends Form{
	
	/**
	 * @var string $payment_class Subclass of DPSHostedPayment for custom processing
	 */
	static $payment_class = 'DPSHostedPayment';
	
	function __construct($controller, $name){
		$fields = new FieldSet(
			$donationAmount = new CurrencyField("Amount", "Amount"),
			new TextField("FirstName", "First Name"),
			new TextField("Surname", "Surname"),
			$email = new EmailField("Email", "Email")
		);

		$actions = new FieldSet(
			new FormAction("doDonate", "Pay")
		);
		
		$validator = new RequiredFields(array(
			"Amount",
			"FirstName",
			"Surname",
			"Email",
		));
		
		parent::__construct($controller, $name, $fields, $actions, $validator);
		
	}
	
	function doDonate($data, $form){
		$paymentClass = self::$payment_class;
		$payment = new $paymentClass();
		$payment->update($data);
		$payment->setClientIP();
		$payment->write();
		$payment->processPayment($data, $form);
	}
}
?>