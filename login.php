<?php 
session_start();
require_once("database.php");
require_once('DBsettings.php');

date_default_timezone_set("Asia/Taipei");

function get_client_ip(){
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function counter() {
	if (file_exists("counter.txt")){
		$n=8;
		$counterfile=fopen("counter.txt","r");
		 //讀取4位數字 
		$counter_num=fgets($counterfile,$n+1);
		 //瀏覽次數加一
		$counter_num++; 
		fclose($counterfile);

		$myfile=fopen("counter.txt","w");
		fwrite($myfile, $counter_num);
		 //關閉文件
		fclose($myfile);
	}
	else
	{
		$counter_num=1;
		$myfile=fopen("counter.txt","w");
		fwrite($myfile, $counter_num);
		 //關閉文件
		fclose($myfile);
	}
}

$db = new db();
$db->DBConnect();

$Account = $db->escapeString($_POST['Account']);
$Password = $db->escapeString($_POST['Password']);

$sql = "Select * From Accounts where Account = '$Account'";

//echo(var_dump($sql));
$result = $db->query($sql);
//echo($sql);
//result to array
$ResultArray = array();
while($line = $db -> fetchArray($result, MYSQLI_ASSOC)){
	$ResultArray[] = $line;
}
$db->close();

if(hash('sha256',$_POST['Password'])==$ResultArray[0]['Password']){
	try {
		// Load settings from parent class
		$settings = DatabaseSettings::getSettings();
		// Get the main settings from the array we just loaded
		$host = $settings['dbhost'];
		$name = $settings['dbname'];
		$user = $settings['dbusername'];
		$pass = $settings['dbpassword'];
	    $conn = new PDO("mysql:host=$host;dbname=$name", $user, $pass);

	    // set the PDO error mode to exception
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$UploadDate=date('Y-m-d H:i:s');
		$ips = get_client_ip();
	    $sql="INSERT INTO Log (	Account , User , IP , Time , Event) VALUES ('$Account','$User','$ips','$UploadDate','Log in Successfully!')";
	    // use exec() because no results are returned
	    $conn->exec($sql);	
		counter();
	    $_SESSION['Login'] = 'True';
		$_SESSION['Permission'] = $ResultArray[0]['Permission'];
		$_SESSION['Account'] = $Account;
		$_SESSION['User'] = $ResultArray[0]['User'];
    }
	catch(PDOException $e)
    {
    }

}
else{try {
		// Load settings from parent class
		$settings = DatabaseSettings::getSettings();
		// Get the main settings from the array we just loaded
		$host = $settings['dbhost'];
		$name = $settings['dbname'];
		$user = $settings['dbusername'];
		$pass = $settings['dbpassword'];
	    $conn = new PDO("mysql:host=$host;dbname=$name", $user, $pass);

	    // set the PDO error mode to exception
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$UploadDate=date('Y-m-d H:i:s');
		$ips = get_client_ip();
	    $sql="INSERT INTO Log (	Account , User , IP , Time , Event) VALUES ('$Account','$User','$ips','$UploadDate','Log in Failed!')";
	    // use exec() because no results are returned
	    $conn->exec($sql);
		$_SESSION['Msg'] = "Wrong Password!";
    }
	catch(PDOException $e)
    {
    }
}
header('Location:https://digital-espacio.com/BackUpMining/');
?>
