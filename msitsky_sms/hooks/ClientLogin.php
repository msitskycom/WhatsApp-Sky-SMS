<?php

$hook = array(
    'hook' => 'UserLogin',
    'function' => 'UserLogin',
    'description' => array(
        'english' => 'After User Login'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Hi *{firstname} {lastname}*,
We would like to inform you that a login has been detected for your account. For security purposes, we are providing you with the details of this login: Email:*{email}*
Phone number:{phone}
IP Address:*{userip}*
',
    'variables' => '{userid}, {firstname}, {lastname}, {email}, {phone}, {userip}'
);

if (!function_exists("UserLogin")) {
    function UserLogin()
    {
        $class = new SkySms();
        $template = $class->getTemplateDetails("UserLogin");
        if ($template["active"] == 0) {
            return NULL;
        }
        $settings = $class->getSettings();
//        if(!$settings['api'] || !$settings['apiparams'] || !$settings['gsmnumberfield'] || !$settings['wantsmsfield']){
        if(!$settings['api'] || !$settings['apiparams']){
            return null;
        }
        $userid = $_SESSION["uid"];
        $user = WHMCS\Database\Capsule::table("tblclients")->where("id", $userid)->first();
        $firstname = $user->firstname;
        $lastname = $user->lastname;
        $email = $user->email;
        $phone = $user->phonenumber;
        $userip = $_SERVER["REMOTE_ADDR"];
        $country = $user->country;
        if ($user) {
            $template["variables"] = str_replace(" ", "", $template["variables"]);
            $replacefrom = explode(",", $template["variables"]);
            $replaceto = [$userid, $firstname, $lastname, $email, $phone, $userip];
            $message = str_replace($replacefrom, $replaceto, $template["template"]);
            if (0 < $settings["gsmnumberfield"]) {
                $phone = $api->customfieldsvalues($userid, $settings["gsmnumberfield"]);
            }
            $class->setCountryCode($country);
            $class->setGsmnumber($phone);
            $class->setMessage($message);
            $class->setUserid($userid);
            $class->send();
        }
    }
}
return $hook;

?>