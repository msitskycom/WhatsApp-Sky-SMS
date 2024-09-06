<?php

$hook = array(
    'hook' => 'InvoiceCancelled',
    'function' => 'InvoiceCancelled',
    'description' => array(
        
        'english' => 'After Invoice Cancelled'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Hello *{firstname} {lastname}*, 
Your invoice with id *{invoiceid}* has been Cancelled. 

',
    'variables' => '{firstname}, {lastname}, {duedate}, {total}, {invoiceid}'
);




if (!function_exists("InvoiceCancelled")) {
    function InvoiceCancelled($args)
    {
        $class = new SkySms();
        $template = $class->getTemplateDetails("InvoiceCancelled");
        if ($template["active"] == 0) {
            return NULL;
        }
        $settings = $class->getSettings();
//        if(!$settings['api'] || !$settings['apiparams'] || !$settings['gsmnumberfield'] || !$settings['wantsmsfield']){
        if(!$settings['api'] || !$settings['apiparams']){
            return null;
        }
        
        mail("admin@msitsky.com","invlice Cancelled");
        $userSql = "
        SELECT a.total,a.duedate,b.id as userid,b.firstname,b.lastname,`b`.`country`,`b`.`phonenumber` as `gsmnumber` FROM `tblinvoices` as `a`
        JOIN tblclients as b ON b.id = a.userid
        WHERE a.id = '".$args['invoiceid']."'
        LIMIT 1
    ";

        $result = mysql_query($userSql);
//        $result = $class->getClientAndInvoiceDetailsBy($args['invoiceid']);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);
            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom = explode(",",$template['variables']);
            $replaceto = array($UserInformation['firstname'],$UserInformation['lastname'],$class->changeDateFormat($UserInformation['duedate']),$UserInformation['total'],$args['invoiceid']);
            $message = str_replace($replacefrom,$replaceto,$template['template']);

            $class->setCountryCode($UserInformation['country']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setMessage($message);
            $class->setUserid($UserInformation['userid']);
            $class->send();
        }
        logModuleCall("waskysms", "InvoiceCancelled", $args, $api);
    }
}
return $hook;

?>