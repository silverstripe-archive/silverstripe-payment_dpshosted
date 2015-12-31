<?php
/**
 * @package payment_dpshosted
 */
class DPSHostedPaymentPage extends Page
{
    
    public static $db = array(
        'SuccessContent' => 'HTMLText',
        'ErrorContent' => 'HTMLText',
    );
    
    public static $defaults = array(
        'SuccessContent' => 'Thank you',
        'ErrorContent' => 'There has been an error.',
    );
    
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        $fields->removeFieldFromTab('Root.Main', 'Content');
        
        $fields->addFieldToTab(
            'Root.Main',
            new HtmlEditorField('SuccessContent', 'Success message')
        );
        $fields->addFieldToTab(
            'Root.Main',
            new HtmlEditorField('ErrorContent', 'Error message')
        );
        
        return $fields;
    }
    
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        
        if (!DataObject::get_one('DPSHostedPaymentPage')) {
            $page = new DPSHostedPaymentPage();
            $page->Title = "Payment Status";
            $page->URLSegment = "paymentstatus";
            $page->ShowInMenus = 0;
            $page->ShowInSearch = 0;
            $page->write();
            
            SS_Database::alterationMessage("DPSHostedPaymentPage page created", "created");
        }
    }
}

/**
 * @package payment_dpshosted
 */
class DPSHostedPaymentPage_Controller extends Page_Controller
{
    
    public function Form()
    {
        $formClass = DPSHostedPayment::$payment_form_class;
        return new $formClass(
            $this,
            'Form'
        );
    }
    
    public function success()
    {
        return $this->customise(array(
            'Content' => $this->SuccessContent,
            'Form' => ' '
        ))->renderWith('Page');
    }
    
    public function error()
    {
        return $this->customise(array(
            'Content' => $this->ErrorContent,
            'Form' => ' '
        ))->renderWith('Page');
    }
}
