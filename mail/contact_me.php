<?php
// Check for empty fields
if(empty($_POST['name'])  		||
   empty($_POST['email']) 		||
   empty($_POST['phone']) 		||
   empty($_POST['message'])	||
   empty($_POST['captcha'])	||
   !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
   {
	echo json_encode(array("msg"=>"Invalid form. Please enter correct data!"));
    return false;
   }
	
$name = strip_tags(htmlspecialchars($_POST['name']));
$email_address = strip_tags(htmlspecialchars($_POST['email']));
$phone = strip_tags(htmlspecialchars($_POST['phone']));
$message = strip_tags(htmlspecialchars($_POST['message']));
$captcha = strip_tags(htmlspecialchars($_POST['captcha']));

$secret="6LdyRhoUAAAAAFib25PQg1AyW-WNgt1qBsk6lnaT";

$verify=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);

$captcha_success=json_decode($verify);

if ($captcha_success->success==false) {
  //This user was not verified by recaptcha.
  echo json_encode(array("msg"=>"You need to validate captcha."));
  return false;
}
else if ($captcha_success->success==true) {
  //This user is verified by recaptcha
  // save to database and send sms to me
  date_default_timezone_set('Asia/Kolkata');
  $filename="contacted/". date("d-m-Y_H-i") .".log";
  $txt="User Contacted on ". date("d-m-Y_H-i") .
    "\n Name = ". $name .
    "\n email = ". $email_address .
    "\n phone = ". $phone .
    "\n Message = ". $message;
  $myfile = file_put_contents("../".$filename, $txt , FILE_APPEND | LOCK_EX);
  // ---------------------send message to me
  
  // resource url & authentication
  $sid= "AC252efbf7581a36e2ed1f1447b4468988";
  $token="ee96d11d8a76fdabae0fe0b4fedffc0e";
  $to="+918420683555";
  $from="+18584296021";
  $body= $name ." (".$phone.") sent you a message on RONGMZ ".
    "http://www.rongmz.in/". $filename;
  $uri = 'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json';
  $auth = $sid . ':' . $token;
  
  // post string (phone number format= +15554443333 ), case matters
  $fields = array(
    'From' => $from,
    'To' => $to,
    'Body' => $body,
  );
  $post = http_build_query($fields);
  // start cURL
  $res = curl_init($uri);
  
  // set cURL options
  //curl_setopt( $res, CURLOPT_URL, $uri );
  curl_setopt( $res, CURLOPT_POST, true ); // number of fields
  curl_setopt( $res, CURLOPT_POSTFIELDS, $post );  
  curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($res, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt( $res, CURLOPT_USERPWD, $auth ); // authenticate
  curl_setopt( $res, CURLOPT_RETURNTRANSFER, true ); // don't echo
  
  // send cURL
  $result = curl_exec( $res );
  curl_close($res);
  // write log 
  file_put_contents("smslog.log", "--". date("d-m-Y_H-i") ."--\n". $result ."\n\n" , FILE_APPEND | LOCK_EX);
  //---------------------------
  echo json_encode(array("msg"=>"Thank you for contacting us. We will get back to you soon."));
  return true;
}

echo json_encode(array("msg"=>"Thank you for contacting us. We will get back to you soon."));                     
return false;

?>
