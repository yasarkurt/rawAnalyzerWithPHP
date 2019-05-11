<?php session_start();?>
<?php $realm = 'Restricted area';

//user => password
$users = array('' => '');

function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}
if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
    die('İşlem İptal Edildi');
}
// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
    !isset($users[$data['username']]))
	{
		 header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
        die('Wrong Credentials!');
	}


// generate the valid response
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

if ($data['response'] != $valid_response){
    header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
        die('Wrong Credentials!');
}?>


<!doctype html>
<html>
<head>
<title>Raw Analyzer</title>
</head>
<body >
<link rel="stylesheet" href="css/font-awesome.min.css"/>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'vendor/autoload.php';
ini_set('memory_limit', '-1');
$server = '';
$user = '';
$pass = '';
$db = '';
$dbName = '';
//$collectionName = '';
$str = "mongodb://" . $user . ":" . $pass . "@" . $server . "/" . $db;
$mongo = new MongoDB\Client($str);
$db = $mongo->$dbName;
$collections = $db->listCollections();
echo "<font face='times new roman bold' size='2.5'>";
echo "<style type='text/css'>
	table{
		cellspacing:1px;
		cellpadding:2px;
		table-layout:fixed
		border-spacing:5px;
	}
	th{
		border:0px;
	}
	tr,td{
		border:1px solid silver;
	}
	tr{
		text-align:char;
	}
	td{
		height:5px;
		width:auto;
		overflow:hidden; white-space:nowrap;
	}
	.kutu{
		width:140px;
	}
	#size{
		width:80px;
	}
	.tooltip {
    position: relative;
    display: inline-block;
}


.tooltip .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: black;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;

    /* Position the tooltip */
    position: absolute;
    z-index: 1;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
}
	</style>";
?>
<div>
<script>
function confirmdelete()
{
		if(confirm('Emin misiniz')){
		var non = document.getElementById('collectionsd');
		<?php
		//$result = $db->dropCollection(non.value);
		//console.log(non.value)
		?>
		}
}
</script>
<form>
       <?php
	echo "<span style='float: left'>Delete Collection: <select id='collectionsd' name='collections1' >";
	foreach ($collections as $col) {
	?>
    <option value= "<?php echo $col['name']; ?>" > <?php echo $col['name'];?></option>
  <?php
}
	echo "</select>
	</span>";		
	?>
	<span onclick="confirmdelete()" style="display: block; border: 1px #000 solid; border-radius: 3px; width: 75px; line-height: 20px; text-align: center; background-color: #eee; float: left; margin: 0px 10px; cursor: pointer">Delete</span>
	</form>
	<div style='clear: both'></div>
	<br>
	<br>
    <form action="main.php" method="GET">
       <?php 	echo "Select Collection:";
	echo "<select name='collections'>";
	foreach ($collections as $col) {
	?>
    <option <?php if(isset($_GET['collections'])){if($_GET['collections']==$col['name']) echo "selected";}?> value= "<?php echo $col['name']; ?>" > <?php echo $col['name'];?></option>
  <?php 	
}
	echo "</select> <br><br>";		
	?>
        &nbsp Start Date: <input type="date" name="basdate" placeholder="Başlangıç tarihi"
                                 value="<?php echo isset($_GET['basdate']) ? $_GET['basdate'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-calendar"><span class="tooltiptext">01.01.1970</span></i></div>
        &nbsp &nbsp &nbsp
        Start Time:<input type="time" name="stime" placeholder="Start Time"
                          value="<?php echo isset($_GET['stime']) ? $_GET['stime'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-clock-o"><span class="tooltiptext">01:01..</span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="sizemin" placeholder="SIZE MIN" id="size"
               value="<?php echo isset($_GET['sizemin']) ? $_GET['sizemin'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-arrow-down"><span class="tooltiptext">
		  <?php if (isset($_GET['sizemin'])) {
              if (strlen($_GET['sizemin']) == 0)
                  echo '0000..';
              else
                  echo 'SIZE MIN';
          } else
              echo '0000..'; ?>
	   </span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="http" placeholder="HTTP" class="kutu"
               value="<?php echo isset($_GET['http']) ? $_GET['http'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span class="tooltiptext">
		  <?php if (isset($_GET['http'])) {
              if (strlen($_GET['http']) == 0)
                  echo 'HTTP/1.1 HTTP/2.0..';
              else
                  echo 'HTTP';
          } else
              echo 'HTTP/1.1 HTTP/2.0..'; ?>
	   </span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="resp" placeholder="RES" class="kutu"
               value="<?php echo isset($_GET['resp']) ? $_GET['resp'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span
                        class="tooltiptext"><?php if (isset($_GET['resp'])) {
                        if (strlen($_GET['resp']) == 0)
                            echo '200 404 304 500 301..';
                        else
                            echo 'RES';
                    } else
                        echo '200 404 304 500 301..'; ?></span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="req" placeholder="REQ " class="kutu"
               value="<?php echo isset($_GET['req']) ? $_GET['req'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span
                        class="tooltiptext"><?php if (isset($_GET['req'])) {
                        if (strlen($_GET['req']) == 0)
                            echo 'GET POST OPTIONS PROPFIND HEAD..';
                        else
                            echo 'REQ';
                    } else
                        echo 'GET POST OPTIONS PROPFIND HEAD..'; ?></span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="ip" placeholder="IP" class="kutu"
               value="<?php echo isset($_GET['ip']) ? $_GET['ip'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span class="tooltiptext"><?php if (isset($_GET['ip'])) {
                        if (strlen($_GET['ip'] == 0))
                            echo '18.191.82.151....';
                        else
                            echo 'IP';
                    } else
                        echo '18.191.82.151....'; ?></span></i></div>
        <br><br>
        Finish Date:<input type="date" name="bitdate" placeholder="Bitiş tarihi"
                           value="<?php echo isset($_GET['bitdate']) ? $_GET['bitdate'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-calendar"><span class="tooltiptext">15.08.2018...</span></i></div>
        &nbsp &nbsp
        Finish Time:<input type="time" name="ftime" placeholder="Finish Time"
                           value="<?php echo isset($_GET['ftime']) ? $_GET['ftime'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-clock-o"><span class="tooltiptext">23:59..</span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="sizemax" placeholder="SIZE MAX" id="size"
               value="<?php echo isset($_GET['sizemax']) ? $_GET['sizemax'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-arrow-up"><span class="tooltiptext"><?php if (isset($_GET['sizemax'])) {
                        if (strlen($_GET['sizemax']) == 0)
                            echo '9999..';
                        else
                            echo 'SIZE MAX';
                    } else
                        echo '9999..'; ?></span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="path" placeholder="PATH" class="kutu"
               value="<?php echo isset($_GET['path']) ? $_GET['path'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span
                        class="tooltiptext"><?php if (isset($_GET['path'])) {
                        if (strlen($_GET['path']) == 0)
                            echo 'uye-girisi-sayfasi..';
                        else
                            echo 'Path';
                    } else
                        echo 'uye-girisi-sayfasi..'; ?></span></i></div>
        &nbsp &nbsp &nbsp
        <input type="text" name="ref" placeholder="REF" class="kutu"
               value="<?php echo isset($_GET['ref']) ? $_GET['ref'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span
                        class="tooltiptext"><?php if (isset($_GET['ref'])) {
                        if (strlen($_GET['ref']) == 0)
                            echo 'http ://bursastore@bursastore.com...';
                        else
                            echo 'REF';
                    } else
                        echo 'http ://bursastore@bursastore.com...'; ?></span></i></div>
        &nbsp &nbsp
        &nbsp
        <input type="text" name="agent" placeholder="AGENT" class="kutu"
               value="<?php echo isset($_GET['agent']) ? $_GET['agent'] : ''; ?>"/>
        <div class="tooltip"><i class="fa fa-question-circle"><span
                        class="tooltiptext"><?php if (isset($_GET['agent'])) {
                        if (strlen($_GET['agent']) == 0)
                            echo 'Mozilla/5.0 (Windows NT 6.1; Win64; x64)...';
                        else
                            echo 'AGENT';
                    } else
                        echo 'Mozilla/5.0 (Windows NT 6.1; Win64; x64)...'; ?></span></i></div>
		 &nbsp &nbsp
        &nbsp <input type="text" name="limit" placeholder="LIMIT" class="kutu"
         value="<?php echo isset($_GET['limit']) ? $_GET['limit'] : ''; ?>"/>
		  <div class="tooltip"><i class="fa fa-question-circle"><span
                        class="tooltiptext"><?php if (isset($_GET['limit'])) {
                        if (strlen($_GET['limit']) == 0)
                            echo 'Sayfalanacak veri miktarı';
                        else
                            echo 'LIMIT';
                    } else
                        echo 'Sayfalanacak veri miktarı'; ?></span></i></div>
        <p><input type="submit" name="gonder" value="Filter" style="margin-left:35%"/></p>
    </form>
</div>
<?php
try {	/*if(isset($_GET['page'])){
		$_POST['collections']=$_SESSION['collectionName'];
		(int)$_POST['limit']=$_SESSION['limitdegeri'];
		$_POST['stime']=$_SESSION['stime'];
		$_POST['ftime']=$_SESSION['ftime'];
		$_POST['basdate']=$_SESSION['basdate'];
		$_POST['bitdate']=$_SESSION['bitdate'];
		$_POST['ip']=$_SESSION['ip'];
		$_POST['resp']=$_SESSION['resp'];
		$_POST['http']=$_SESSION['http'];
		$_POST['req']=$_SESSION['req'];
		$_POST['path']=$_SESSION['path'];
		$_POST['ref']=$_SESSION['ref'];
		$_POST['agent']=$_SESSION['agent'];
		$_POST['sizemax']=$_SESSION['sizemax'];
		$_POST['sizemin']=$_SESSION['sizemin'];
		if(count($_SESSION) > 0)
			$_POST['gonder'] = true;
	}*/
	if (empty($_GET['gonder'])) {
		$collectionName ='raw_log_2018_08_28_18_21';
		$collection=$db->$collectionName;
        $cursor = $collection->find(array(), ['limit' => 2000]);
				$_SESSION['collectionName']=$collectionName;
		/*$_SESSION['limitdegeri']=$limitdegeri;
		$_SESSION['stime']=$_stime;
		$_SESSION['ftime']=$_ftime;
		$_SESSION['basdate']=$_basdate;
		$_SESSION['bitdate']=$_bitdate;
		$_SESSION['ip']=$regex_3;
		$_SESSION['resp']=$regex_4;
		$_SESSION['http']=$regex_5;
		$_SESSION['req']=$regex_7;
		$_SESSION['path']=$regex_8;
		$_SESSION['ref']=$regex_9;
		$_SESSION['agent']=$regex_10;
		$_SESSION['sizemax']=$_sizemax;
		$_SESSION['sizemin']=$_sizemin;*/
        echo " <table>";
        echo "<tr >";
        echo "<th >" . "DATE" . "</th>";
        echo "<th>" . "TIME" . "</th>";
        echo "<th>" . "IP" . "</th>";
        echo "<th>" . "RES" . "</th>";
        echo "<th>" . "SIZE" . "</th>";
        echo "<th>" . "REQ" . "</th>";
        echo "<th >" . "PATH" . "</th>";
        echo "<th >" . "REF" . "</th>";
        echo "<th >" . "AGENT" . "</th>";
        echo "<th>" . "HTTP" . "</th>";
        echo "</tr>";
		$j=0;
        foreach ($cursor as $object) {
		 ?>
		<tr <?php if($j%2==0) echo "bgcolor='white'"; else echo "bgcolor =#f2f2f2";?>>
		   <?php  
            $object['date'] = strtr($object['date'], '/', '-');
            $object['date'] = date("d-m-Y", strtotime($object['date']));
            $bul = "-";
            $degistir = ".";
            $object['date'] = str_replace($bul, $degistir, $object['date']);
            echo "<td >" . $object['date'] . "</td>";
            $parca = explode(":", $object['time']);
            echo "<td >" . $parca[0] . ":" . $parca[1] . "</td>";
            echo "<td '>" . $object['ip'] . "</td>";
            echo "<td align='right'>" . $object['response_code'] . "</td>";
            echo "<td align='right'>" . $object['size'] . "</td>";
            echo "<td>" . $object['request_type'] . "</td>";
            $object['path'] = ltrim($object['path'], "/");
            $yazi = $object['path'];
            $uzunluk = strlen($object['path']);
            $sinir = 100;
            if ($uzunluk > $sinir) {
                $object['path'] = substr($object['path'], 0, $sinir);
            }
            if ($uzunluk > 100)
                echo "<td >" . $object['path'] . "<span title='" . $yazi . "'>...</span></td>";
            else
                echo "<td >" . $object['path'] . "</td>";
            $yazi1 = $object['referer'];
            $uzunluk = strlen($object['referer']);
            $sinir = 100;
            if ($uzunluk > $sinir) {
                $object['referer'] = substr($object['referer'], 0, $sinir);
            }
            if ($uzunluk > 100)
                echo "<td>" . $object['referer'] . "<span title='" . $yazi1 . "'>...</span></td>";
            else
                echo "<td>" . $object['referer'] . "</td>";
            echo "<td >" . $object['agent'] . "</td>";
            echo "<td>" . $object['http'] . "</td>";
            echo "</tr>";
        
	    
		$j++;
		}
		
        echo "</table>";
    }
	else {
		$collectionName =$_GET['collections'];
		$collection=$db->$collectionName;
        date_default_timezone_set('Etc/GMT+0');
		$limitdegeri = (int)$_GET['limit'];
        $_stime = $_GET['stime'];
        $_stime = date("1970-01-01 H:i:s", strtotime($_stime));
        $_stime = strtotime($_stime);
		
			// eğer sayfa girilmemişse 1 varsayalım.
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
 
			// eğer 1'den küçük bir sayfa sayısı girildiyse 1 yapalım.
			if($page < 1) $page = 1; 
 
			
		
        if (empty($_stime))
            $_stime = 0;
        $_ftime = $_GET['ftime'];
        $_ftime = date("1970-01-01 H:i:s", strtotime($_ftime));
        $_ftime = strtotime($_ftime);

        if (empty($_ftime))
            $_ftime = 86399;
        else
            $_ftime = ($_ftime + 59);
        $_basdate = $_GET['basdate'];

        if (empty($_basdate))
            $_basdate = '01.01.1970';

        $_basdate = strtr($_basdate, '.', '-');
        $_basdate = date("Y-m-d", strtotime($_basdate));
        $_basdate = strtotime($_basdate);
        $_bitdate = $_GET['bitdate'];

        if (empty($_bitdate))
            $_bitdate = date("d.m.Y");
		
        $_bitdate = strtr($_bitdate, '.', '-');
        $_bitdate = date("Y-m-d", strtotime($_bitdate));
        $_bitdate = strtotime($_bitdate);
        $_bitdate = $_bitdate + $_ftime;
        $_basdate = $_basdate + $_stime;
        $_ip = $_GET['ip'];
        $_resp = $_GET['resp'];
        $_http = $_GET['http'];
        $_req = $_GET['req'];
        $_path = $_GET['path'];
        $_ref = $_GET['ref'];
        $_agent = $_GET['agent'];
        $_sizemax = (int)$_GET['sizemax'];
        $_sizemin = (int)$_GET['sizemin'];
        if (empty($_sizemin))
            $_sizemin = 0;
        $_ip = preg_quote($_ip, '/');
        $regex_3 = new MongoDB\BSON\Regex($_ip, 'i');
        $regex_4 = new MongoDB\BSON\Regex($_resp, 'i');
        $_http = preg_quote($_http, '/');
        $regex_5 = new MongoDB\BSON\Regex($_http, 'i');
        $regex_7 = new MongoDB\BSON\Regex($_req, 'i');
        $_path = preg_quote($_path, '/');
        $regex_8 = new MongoDB\BSON\Regex($_path, 'i');
        $_ref = preg_quote($_ref, '/');
        $regex_9 = new MongoDB\BSON\Regex($_ref, 'i');
        $_agent = preg_quote($_agent, '/');
        $regex_10 = new MongoDB\BSON\Regex($_agent, 'i');
		$_SESSION['collectionName']=$collectionName;
		$_SESSION['limitdegeri']=$limitdegeri;
		$_SESSION['stime']=$_stime;
		$_SESSION['ftime']=$_ftime;
		$_SESSION['basdate']=$_basdate;
		$_SESSION['bitdate']=$_bitdate;
		$_SESSION['ip']=$regex_3;
		$_SESSION['resp']=$regex_4;
		$_SESSION['http']=$regex_5;
		$_SESSION['req']=$regex_7;
		$_SESSION['path']=$regex_8;
		$_SESSION['ref']=$regex_9;
		$_SESSION['agent']=$regex_10;
		$_SESSION['sizemax']=$_sizemax;
		$_SESSION['sizemin']=$_sizemin;
	
        if (strlen($_GET['sizemax']) == 0){
			$data_total = $collection->count(['$and' => [['unix_date' => ['$gte' => $_basdate, '$lte' => $_bitdate]], ['size' => ['$gte' => $_sizemin]], ['ip' => $regex_3, 'response_code' => $regex_4, 'http' => $regex_5, 'request_type' => $regex_7, 'path' => $regex_8, 'referer' => $regex_9, 'agent' => $regex_10]]]);
			$page_total=ceil($data_total/$limitdegeri);
			$skip=($page-1)*$limitdegeri;
			$limit=$limitdegeri;
		    $cursor = $collection->find(['$and' => [['unix_date' => ['$gte' => $_basdate, '$lte' => $_bitdate]], ['size' => ['$gte' => $_sizemin]], ['ip' => $regex_3, 'response_code' => $regex_4, 'http' => $regex_5, 'request_type' => $regex_7, 'path' => $regex_8, 'referer' => $regex_9, 'agent' => $regex_10]]],['limit'=>$limit,'skip' => $skip ]);
			
			
		}
        else{
			$data_total = $collection->count(['$and' => [['unix_date' => ['$gte' => $_basdate, '$lte' => $_bitdate]], ['size' => ['$gte' => $_sizemin, '$lte' => $_sizemax]], ['ip' => $regex_3, 'response_code' => $regex_4, 'http' => $regex_5, 'request_type' => $regex_7, 'path' => $regex_8, 'referer' => $regex_9, 'agent' => $regex_10]]]);
			$page_total=ceil($data_total/$limitdegeri);
			$skip=($page-1)*$limitdegeri;
			$limit=$limitdegeri;
            $cursor = $collection->find(['$and' => [['unix_date' => ['$gte' => $_basdate, '$lte' => $_bitdate]], ['size' => ['$gte' => $_sizemin, '$lte' => $_sizemax]], ['ip' => $regex_3, 'response_code' => $regex_4, 'http' => $regex_5, 'request_type' => $regex_7, 'path' => $regex_8, 'referer' => $regex_9, 'agent' => $regex_10]]],['limit'=>$limit, 'skip' => $skip]);
			
		}
		
        echo "<table>";
        echo "<tr >";
        echo "<th >" . "DATE" . "</th>";
        echo "<th>" . "TIME" . "</th>";        
		echo "<th>" . "IP" . "</th>";
        echo "<th>" . "RES" . "</th>";
        echo "<th>" . "SIZE" . "</th>";
        echo "<th>" . "REQ" . "</th>";
        echo "<th >" . "PATH" . "</th>";
        echo "<th >" . "REF" . "</th>";
        echo "<th >" . "AGENT" . "</th>";
        echo "<th>" . "HTTP" . "</th>";
        echo "</tr>";
		$i=0;
        foreach ($cursor as $doc) {		
			?>
		<tr <?php if($i%2==0) echo "bgcolor='white'"; else echo "bgcolor =#f2f2f2";?>>
		   <?php  
            $doc['unix_date'] = date('d.m.Y H:i:s', $doc['unix_date']);
            $parca = explode(" ", $doc['unix_date']);
            if (strlen($_GET['bitdate']) != 0 || strlen($_GET['basdate']) != 0)
                $parca[0] = str_ireplace($parca[0], "<font color='red'>" . $parca[0] . "</font>", $parca[0]);
            echo "<td >" . $parca[0] . "</td>";
            if (strlen($_GET['ftime']) != 0 || strlen($_GET['stime']) != 0)
                $parca[1] = str_ireplace($parca[1], "<font color='red'>" . $parca[1] . "</font>", $parca[1]);
            $parca1 = explode(":", $parca[1]);
            echo "<td >" . $parca1[0] . ":" . $parca1[1] . "</td>";
            $_ip = stripslashes($_ip);
            $doc['ip'] = str_ireplace($_ip, "<font color='red'>" . $_ip . "</font>", $doc['ip']);
            echo "<td '>" . $doc['ip'] . "</td>";
            $doc['response_code'] = str_ireplace($_resp, "<font color='red'>" . $_resp . "</font>", $doc['response_code']);
            echo "<td align='right'>" . $doc['response_code'] . "</td>";
            if (strlen($_GET['sizemax']) != 0 || strlen($_GET['sizemin']) != 0)
                $doc['size'] = str_ireplace($doc['size'], "<font color='red'>" . $doc['size'] . "</font>", $doc['size']);
            echo "<td align='right'>" . $doc['size'] . "</td>";
            $doc['request_type'] = str_ireplace($_req, "<font color='red'>" . $_req . "</font>", $doc['request_type']);
            echo "<td>" . $doc['request_type'] . "</td>";
            $_path = stripslashes($_path);
            $doc['path'] = str_ireplace($_path, "<font color='red'>" . $_path . "</font>", $doc['path']);
            $doc['path'] = ltrim($doc['path'], "/");
            $yazi = $doc['path'];
            $uzunluk = strlen($doc['path']);
            $sinir = 100;
            if ($uzunluk > $sinir) {
                $doc['path'] = substr($doc['path'], 0, $sinir);
            }
            if ($uzunluk > 100)
                echo "<td >" . $doc['path'] . "<span title='" . $yazi . "'>...</span></td>";
            else
                echo "<td >" . $doc['path'] . "</td>";
            $_ref = stripslashes($_ref);
            $doc['referer'] = str_ireplace($_ref, "<font color='red'>" . $_ref . "</font>", $doc['referer']);
            $yazi1 = $doc['referer'];
            $uzunluk = strlen($doc['referer']);
            $sinir = 100;
            if ($uzunluk > $sinir) {
                $doc['referer'] = substr($doc['referer'], 0, $sinir);
            }
            if ($uzunluk > 100)
                echo "<td>" . $doc['referer'] . "<span title='" . $yazi1 . "'>...</span></td>";
            else
                echo "<td>" . $doc['referer'] . "</td>";
            $_agent = stripslashes($_agent);
            $doc['agent'] = str_ireplace($_agent, "<font color='red'>" . $_agent . "</font>", $doc['agent']);
            echo "<td>" . $doc['agent'] . "</td>";
            $_http = stripslashes($_http);
            $doc['http'] = str_ireplace($_http, "<font color='red'>" . $_http . "</font>", $doc['http']);
            echo "<td>" . $doc['http'] . "</td>";
            echo "</tr>";	
           $i++;
        }
        echo "</table>";
		for($p = 1; $p <= $page_total; $p++) {
		   if($page	== $p) { // eğer bulunduğumuz sayfa ise link yapma.
			  echo $p . ' '; 
		   } else {
			  echo '<a href="?';
				foreach($_GET as $index => $value){
					echo $index.'='.$value.'&';
				}
			  echo'page=' . $p . '">' . $p . '</a> ';
		   }
		}
    }
} catch (\Exception $exc) {
    echo $exc->getMessage();
}
echo "</font>";
?>
</body>
</html>
