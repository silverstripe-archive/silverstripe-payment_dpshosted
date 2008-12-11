<?php
// Example configuration:
/*
if(Director::isLive()){
	Email::setAdminEmail(<my_live_email>);
	DPSHostedPayment::set_px_pay_userid(<my_live_id>);
	DPSHostedPayment::set_px_pay_key(<my_live_key>);
}else{
	Email::setAdminEmail(<my_test_email>);
	DPSHostedPayment::set_px_pay_userid(<my_test_id>);
	DPSHostedPayment::set_px_pay_key(<my_test_key>);
}
*/

Director::addRules(50, array(
	'DPSHostedPayment/$Action/$ID' => 'DPSHostedPayment_Controller'
));
?>