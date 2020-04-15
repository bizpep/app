<?php

$apps = array("cfba" => "Cashflow Business", "No" => "No App");
$urls = array("cfba" => "http://bizpep.com/applet/app.html", "No" => "http://bizpep.com/");

$activeDays = 33;
$activeDays = 95;

$emailFrom='David Morcom<davidm@bizpep.com>';
$emailBcc='bizpep@gmail.com';

$dbfFile = 'dbf.xml';
$dbpFile = 'dbp.xml';	
$dbFile = $dbfFile;

$emailSub='';
$textEmail='';
$htmlEmail='';
$sendAs='text';

$e='No';
$i='No';
$a='No';

$action='';
$active='No';
$appId='No';
$status='';
$times=0;

$code='';
$edsum=0;

if(!empty($_POST)||!empty($_GET))
{
  foreach($_GET as $name=>$value)
  {
    if (get_magic_quotes_gpc())
    {
      $value=stripslashes($value);
    }
    $name=trim(rawurldecode($name));
    $value=trim(rawurldecode($value));
		$$name=$value;
    $info.="GET $name=$value\n";    
  }
		
	foreach($_POST as $name=>$value)
  {
    if (get_magic_quotes_gpc())
    {
      $value=stripslashes($value);
    }
    $name=trim(rawurldecode($name));
    $value=trim(rawurldecode($value));
		$$name=$value;
    $info.="$name:\r\n$value\r\n\r\n";        
  }
  
	if(!empty($action)&&$action=='delete'&&!empty($e))
	{
		db();
		echo $action.' '.$e;
		exit();
	}
	
	
	if(emailCk())
	{
	  $active='Free';
		$status=$active;
	}
	
	if((!empty($payer_email)&&$txn_type=='subscr_payment')||(!empty($pp) && $pp=='y'))
  {
		if(!empty($payer_email))
		{		
		  $e=$payer_email;
			//$i="ppid";
		}	
		$active='Plus';
		$status=$active;
	}
	
	$appId=$i;
	 
	db();
		
  if(emailCk())
  {			
		if($times==1 || (!empty($action)&&$action=='resend'))
		{
		  //echo "<p>email is $e</p><p>times is $times</p>";
			
			enc();
			mes();
			email();
		}
	}
	else
	{
	  $active='No';
	}

	echo $active;
		
	if(!empty($test))
	{
		enc();
		echo "<p>email $e ecode to $code decode to ";
		dec();
		echo $e.'</p>';
	}
	
	
	
	//echo "<p>email is $e</p><p>times is $times</p>";
	//echo "<p>email is</p><p>$textEmail</p>";
}	

function emailCk()
{
  global $e;	
	return preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i", $e);
}

function db()
{
	global $e;
  global $appId;
  global $a;
	
	global $active;
	global $activeDays;
	
	global $dbfFile;
  global $dbpFile;	
  global $dbFile;
	
	global $times;
	
	global $action;
	 
	$content = '<?xml version="1.0" encoding="UTF-8"?><Accounts></Accounts>';
	 
	if(@file_get_contents($dbfFile)===false)
	{ 
     @file_put_contents($dbfFile, $content, LOCK_EX);
	}
	if(@file_get_contents($dbpFile)===false)
	{
		 @file_put_contents($dbpFile, $content, LOCK_EX);
	}
		
	$contentf = file_get_contents($dbfFile);
	$contentp = file_get_contents($dbpFile);
	if(stripos($contentp, '<Email>'.$e.'</Email>')!==false || $active=='Plus')
	{
	   $dbFile=$dbpFile;
		 $content=$contentp;
	}
	else
	{
	  $content = $contentf;
	}
	
	$t=time()+(10*60*60);
	$last_time=gmdate("Y-m-d H:i:s",$t);
	 
	if(stripos($content, '<Email>'.$e.'</Email>')===false&& empty($action))
	{
   $first_time=$last_time;
	 $times=1;
	 
	 $active=gmdate("Ymd",$t+$activeDays*24*60*60).' '.$active.' link sent';
	 
	 if($e=='No')
	 {
	  $active='No';
	 }	
	  
	 $addContent = '<Accounts><Account><Email>'.$e.'</Email><appId>'.$appId.'</appId><Active>'.$active.'</Active><First_Time>'.$first_time.'</First_Time><Last_Time>'.$last_time.'</Last_Time><Times>'.$times.'</Times><a>'.$a.'</a></Account>';
		
	 $content=str_replace('<Accounts>',$addContent, $content);
	
	 @file_put_contents($dbFile, $content,LOCK_EX);
	 
  }
	else
	{
		$oa1=explode('<Account><Email>'.$e.'</Email>', $content)[1];
		$oa2=explode('</Account>',$oa1)[0];
		$oa='<Account><Email>'.$e.'</Email>'.$oa2.'</Account>';
		 
		if($action=='delete')
		{
		   $na='';
		}
		else
		{		
			 $na=$oa;
			 $el='Active';
			 $od=explode('</'.$el.'>',explode('<'.$el.'>',$oa)[1])[0];
			 	 
			 if($active=='Plus')
			 {
			   $active=gmdate("Ymd",$t+$activeDays*24*60*60).' '.$active;	
				 $nd=$active;
			 }
			 else if(!empty($action)&&$action=='resend')
			 {
			    $active=$od.' link sent';		 
			 }
			 else
			 { 
				 $active=str_replace(' link sent','',$od);
			 }
			 $nd=$active;
			 $na=str_replace('<'.$el.'>'.$od.'</'.$el.'>','<'.$el.'>'.$nd.'</'.$el.'>',$na);
					 
			 $el='Last_Time';
			 $od=explode('</'.$el.'>',explode('<'.$el.'>',$oa)[1])[0];
			 $nd=$last_time;
			 $na=str_replace('<'.$el.'>'.$od.'</'.$el.'>','<'.$el.'>'.$nd.'</'.$el.'>',$na);
			 
			 //$content=str_replace('<'.$el.'>'.$od.'</'.$el.'>','<'.$el.'>'.$nd.'</'.$el.'>',$content);
			 
			 $el='Times';
			 $od=explode('</'.$el.'>',explode('<'.$el.'>',$oa)[1])[0];
			 $nd=$od+1;
			 //echo('od = '.$od.'nd = '.$nd);
			 $na=str_replace('<'.$el.'>'.$od.'</'.$el.'>','<'.$el.'>'.$nd.'</'.$el.'>',$na);
			 $times=$nd;
			 //echo('times = '.$times);
			 
		}	 
		$content=str_replace($oa,$na,$content);
		@file_put_contents($dbFile, $content, LOCK_EX);
	}
}

function edc()
{
   global $appId;
	 global $edsum;
	 
	 $edsum=0;
	 $edcode=tocode(strtoupper($appId));
	 for ( $i=0;$i < strlen($edcode);$i++)
   {
      $edsum=$edsum+substr($edcode, $i, 1);
   }
}		

function enc()
{
			global $e;
			
			edc();
			$ecode = tocode($e);
			divcode($ecode);
}
	
function tocode($from)
{	
			$ecode='';
			$from=strtoupper($from);
			for ( $i=0; $i < strlen($from); $i ++ )
			{ 
				 $ch = substr($from, $i, 1);
				 $chcode=ord($ch);
				 $ecode=$ecode.$chcode;
			}
			return $ecode;
}

function divcode($from)
{
	global $edsum;
	global $code;
	
	$divs=str_split($from, 6);
	
	$code=$divs[0]*$edsum;
	for ($i=1;$i < count($divs);$i++)
  {
		$code=$code.'-'.$divs[$i]*$edsum;
	}
	//echo $code;
}

function dec()
{
 	edc();
	$getjcode=jcode();
	codeto($getjcode);
}

function jcode()
{
    global $edsum;
		global $code;
		
		$jcode='';
		$divs = explode('-', $code);
		for ($i=0;$i < count($divs);$i++)
    {
		 $jcode=$jcode.$divs[$i]/$edsum;
		}
    return $jcode;
}

function codeto($from)
{
      global $e;
			
			$e='';
			for ( $i=0; $i < strlen($from); $i=$i+2 )
			{ 
				 $ch = substr($from, $i, 2);
				 $char=chr($ch);
				 $e=$e.$char;
		  }	
			$e=strtolower($e);
			
			//echo $e;
}	

function mes()
{
     global $i;
		 global $code;
		 global $status;
		 
		 global $apps;
		 global $urls;
		 
		 global $textEmail;
		 global $htmlEmail;
		 global $emailSub;
				
		 $appItem=$apps[$i];
		 $appUrl=$urls[$i];
		 
		 $emailSub="Welcome to $appItem $status";
		 
		 $htmlH="<!DOCTYPE html><html lang='en'><head><title>Welcome to $appItem $status</title><meta charset='UTF-8'><meta name='viewport' content='width=device-width' />";
     $htmlH.="</head><body style='background-color:#ffffff; color:#336666; font-family:arial,sans-serif; font-size: 100%; line-height:2em;text-align: justify;'><div style='border-style:solid;border-width:1px;border-color:#f0f0f0; border-radius:1em;padding:1em;margin:1em 1em 1em 1em;'>";
    
		 $htmlTag="<p style='text-align: right;'><span style='color:#ff6600;'>+</span><a href='http://bizpep.com/' target='_blank'>bizpep.com</a> business and investment insight</p>";
     $htmlSign="<div style='line-height:1.2em;border-bottom-style:solid;border-bottom-width:3px;border-bottom-color:#ff9900'><p>If we can assist in any way please <a href='http://bizpep.com/contactus.html'>Contact Us</a>.</p><p><b>Regards</b><br />David<br /><a href='http://bizpep.com/'>bizpep.com</a></p></div>";
     $htmlSign.=$htmlTag;
		 
		 $htmlMes="<p>Welcome to $appItem $status</p>";
		 $htmlMes.="<p><a href='$appUrl?c=$code'>Click here to activate your $appItem $status application</a>.</p>";
   
	   $htmlEmail=$htmlH;
		 $htmlEmail.=$htmlTag;
     $htmlEmail.=$htmlMes;
		 $htmlEmail.=$htmlSign;
		
		 $textEmail="Welcome to $appItem $status\n\n";  
		 $textEmail.="Your $appItem $status Activation link is $appUrl?c=$code\n\n";
		 $textEmail.="Use this link to activate your $appItem $status application.\n\n";
		 $textEmail.="Regards\n\n";
     $textEmail.="David\n";
     $textEmail.='http://bizpep.com'; 
}

function email()
{
    global $e;
		global $sendAs;
		
    global $textEmail;
		global $htmlEmail;
		global $emailSub;
	
    global $emailFrom;
		global $emailBcc;
		
			
		$xheaders="Content-Type: text/plain;charset='iso-8859-1'\r\n";
		$emailCont= $textEmail;
		if($sendAs=='html')
		{
		  $xheaders = "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$emailCont=$htmlEmail;
		}
		$xheaders.="MIME-Version:1.0\r\n";
    $xheaders.="From:$emailFrom\r\n";
    $xheaders.="Bcc:$emailBcc\r\n";
    $xheaders.="Reply-To:$emailFrom\nReturn-path:$emailFrom\nX-Mailer: PHP/" . phpversion();
	
    @mail($e,$emailSub,$emailCont,$xheaders);
}

?>


