<?php 

if(!isset($_GET['ratings']) && count($_GET['ratings']) < 3){
	echo "Not All Ratings Were Sent!"; exit();
}

echo "You Entered the Following Ratings: "; $i = 1;
foreach($_GET['ratings'] as $key => $value){
	echo ($i < 3)?$value.", ":$value;
	$i++;
}
exit(); 
