<html>
<head>
<title>SGB Customer Contact</title>
</head>


<body topmargin=0>
<?PHP  	require("checksession.php"); 
	require("header.php"); 
	if (!checkUser(array("admin","account"))) logout();	
	?>

		<h1>รายชื่อลูกค้า</h1>
		• Active only<br>
		• Last import email id = 959<br>
		• Last import FB audience id = 1027<br>
		<br>
		• รหัสลูกค้า <br>
			0 : ร้านส่ง, หุ้นส่วน<br>
			1 - 3: VIP 1-3
			4 - 6: บุคคล<br>
			7 - 8: ร้านเล็ก<br>
			9 : ร้านกลาง / ใหญ่<br>
		<br><br>

		<?PHP 
		
			function checkRef ($ref, $shop_type)
			{
				
				$r = (int)$ref[0];
				
				if (strcmp($shop_type, "wholesale") == 0)
					return ($r == 0);
					
				if (strcmp($shop_type, "persona") == 0)
					return (($r >= 4) && ($r <= 6));
					
				if (strcmp($shop_type, "retail_s") == 0)
					return (($r >= 7) && ($r <= 8));
				
				if ((strcmp($shop_type, "retail_m") == 0) || (strcmp($shop_type, "retail_l") == 0))
					return ($r == 9);
					
				if ((strcmp($shop_type, "bill") == 0) || (strcmp($shop_type, "other") == 0)) return TRUE;
					
				return FALSE;					
			}
			
			function correctRef ($shop_type)
			{
				
				
				
				if (strcmp($shop_type, "wholesale") == 0)
					return "ที่ถูกต้อง : 0";
					
				if (strcmp($shop_type, "persona") == 0)
					return "ที่ถูกต้อง : 4-6";
					
				if (strcmp($shop_type, "retail_s") == 0)
					return "ที่ถูกต้อง : 7-8";
				
				if ((strcmp($shop_type, "retail_m") == 0) || (strcmp($shop_type, "retail_l") == 0))
					return "ที่ถูกต้อง : 9";
					
				return 0;					
			}
			
			
			
		
			echo "<form action='".basename(__FILE__)."' method='post'>";

			$from = date ("Y-m-d", mktime(0,0,0,date("m")-1,1,date("Y")) );
			// If POST found, set them
			if (!empty($_POST["from"])) 	
			{
				$from = $_POST["from"];
				$first_day_of_last_month = date ("Y-m-d", mktime(0,0,0,date("m")-1,1,date("Y")) );	
				$from =  (strtotime($from) < strtotime($first_day_of_last_month) ) ? $first_day_of_last_month : $from;
			}
	
			$to 	= date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y"))); 

			
			// If POST found, set them
			if (!empty($_GET["from"])) 	$from = $_GET["from"];
			if (!empty($_GET["to"])) 		$to = $_GET["to"];	
			
			echo "ช่วงเวลา <input type='text' id='datepicker' size='10' readonly='true' name='from' value='$from'> - "; 
			echo "<input type='text' id='datepicker2' size='10' readonly='true' name='to' value='$to'>
			<input type='submit' value='OK'>$NR$NR"; 
			
			
			require ("connect.php");				

			$sql = "
						
					SELECT 
						u.name
						,u.create_time
						,u.ref
						,u.id as uid
						,u.email
						,u.app_no
						,u.mobile
						,u.remark
						,marketing.name as mar
						,owner.name as owner_mar
						,u.address
						,u.address2
						,u.state
						,u.tmax
						,u.shop_type
						,u.shop_name
						,u.cust_type
						,u.ttype
						,u.tday
						,u.deposit_cash
						,u.deposit_96
						,u.deposit_99
						,u.init_limit_96 as limit96
						,u.init_limit_99 as limit99
						,u.google_map
						,p.name as province
						,u.region
					
					FROM gt_account u 
						LEFT JOIN base_user marketing on marketing.id = u.mkt_user_id
						LEFT JOIN province p on p.id = u.province
						LEFT JOIN base_user owner on owner.id = u.owner_user_id
					
					WHERE u.state like 'a%'
					
					AND u.create_time BETWEEN '$from' AND '$to'
					
					ORDER BY owner_mar
					
				";	
				
				//AND u.shop_type NOT IN ('bill','other')
				
			if ($rs = pg_query($connection,$sql))
			{
			
				// Get current bid/ask to calculate PL
				if ($data = getCurrentPrice())
				{	
					$bid96 	= $data['g96_bid'];
					$ask96 	= $data['g96_ask'];
					$bid99 	= $data['g99lbma_bid'];
					$ask99 	= $data['g99lbma_ask'];
					
				}
				else { $bid96 = $ask96 = $bid99 = $ask99 = 0; }
		
		
		
				echo $TOPEN;
				$i = 0;
				
				//print_row(array("#","ID","Name", "Mar", "Email", "Mobile", "ประเภท", "","T+","รับ/แมทช์",
				//"หลักประกัน$NR ฿/96/99","Limit 96/99","หลักประกัน$NR ต่อบาท","หมายเหตุ"), true);
				
				
				while ($rc = pg_fetch_assoc($rs))
				{			
				
					$deposit = $rc['deposit_cash'] + ($rc['deposit_96'] * $bid96) + ($rc['deposit_99'] * $bid99 * 65.6);
					
					
					if  ((strncmp($rc['shop_type'],"retail",1) == 0) OR
						(strncmp($rc['shop_type'],"wholesale",1) == 0))
						if ($rc['shop_name'] == '')
							$rc['shop_name'] = "<h2>$red  ERR: ขาดชื่อร้าน";
							
							
					$tmp = explode ("/", $rc['shop_name']);
					@($shopname = $tmp[0]);
					@($location = $tmp[1]);
					
						
					pr(array(
					
					
						$rc['uid'],
						
						$rc['create_time'],
						
						//$rc['ref'],
						
						
						//$rc['email'],
						//phone_format($rc['mobile']),
						
						//$rc['shop_type'] ? $rc['shop_type'] : "$red ERR: ไม่มีประเภทลูกค้า",
						//getShopType($rc['shop_type']),
						
						
						(strncmp($rc['cust_type'],"normal",1) == 0) ? $rc['cust_type'] : $red.$rc['cust_type'],

						//$rc['shop_name'],
						
						
						$rc['address'],
						$rc['address2'],
						$rc['app_no'],
						//$rc['tday'],
						//$rc['tmax'],
						//$rc['ttype'] ? $rc['ttype'] : "$red ไม่มีประเภทซื้อขาย",
						//nf($rc['deposit_cash']/1000)."k/".nf($rc['deposit_96'])."บ./".nf($rc['deposit_99']),
						//nf($rc['limit96']).'/'.nf($rc['limit99']),
						//($deposit > 10000 ? number_format($deposit / max($rc['limit96'],$rc['limit99'] * 65.6),0,',','') : ''),
						
						//$rc['google_map'],
						
						//$rc['province'],
						
						//$rc['region'],
						
						//$tiny.$rc['remark']
						
						$rc['name']
					));
				}
				echo $TBODY_C.$TCLOSE;
			}
			
		
			include "disconnect.php";
			
		?>
   			
<?php include "footer2.php"; ?>
</form>
</body>
</html>