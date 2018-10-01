<?php  

error_reporting( E_ALL );

$vebookpath="ebookChess";  //folder where will be uncompress the epub files...
$debug=True;

//-------------------------------
$varraystring=array();

if ( (isset($_POST["url"])) && (!isset($_GET["url"])) ) {
	$_GET = $_POST;
	}
	
$vonlymainlinesNotations=False;  //Process only the main chess line of notations without variants?
$vfinstr='share-after';          // End of a text to parse
$vinistr='';
$vurl=(isset($_GET["url"]) ? trim($_GET["url"]) : '');   //URL to parsing
$vebook=(isset($_GET["ebook"]) ? trim($_GET["ebook"]) : ''); //Ebook to parse
$vebookpos=(isset($_GET["p"]) ? intval($_GET["p"]) : 1);   //Pos in epub toc
$vebookcapsearch=(isset($_GET["c"]) ? trim($_GET["c"]) : "");   //Cap. postoc selected to search
$vimgdl=(isset($_GET["imgdl"]) ? "1" : ""); //Download the images of diagrams?
$vimgpreview=(isset($_GET["imgpreview"]) ? "1" : ""); //Show a regenerated image of diagrams
$vnoanalysis=(isset($_GET["noanalysis"]) ? "1" : "");  //Not analyse viewer show?
$vnoimg=(isset($_GET["noimg"]) ? "1" : "");  //Not show the images of the epubs file

//onlymainlines show (not Variants?)
if (($vimgpreview=="1") or (isset($_GET["onlymainlines"]) ) ) {
	$vonlymainlinesNotations=True;
	}

$vpos=0;
$vdebugid=1;  //show debug div id for founded notations 
$vJScripts="";  //Scripts to show debug content and Viewers of chessboards
$vurlpostact=""; //URL of current post...

$vmenu="";  //Menu content with index of epub or website...
$vindice="";
$vimagesrel="../images";  //Image folder in ebook
$vtextsrel="Text";  //Text folder in ebook


// ---------------------------------
// Get filename from id in Epub content
function  getebookHref($xmlurl, $vidfile) {
	$vresult="";
	$XMLDoc = simplexml_load_file("$xmlurl");
	foreach($XMLDoc->manifest->item as $Record1) {			
		$attrs1 = $Record1->attributes();			
		$vid=$attrs1['id'];
		$vhref=$attrs1['href'];
		if (trim($vid) == trim($vidfile)){
			return $vhref;
			}
		}	
	return $vresult;
	}

// ---------------------------------
// Get id from filename in Epub content	
function  getebookId($xmlurl, $vhreffile) {
	$vresult=$vhreffile;
	$XMLDoc = simplexml_load_file("$xmlurl");
	foreach($XMLDoc->manifest->item as $Record1) {			
		$attrs1 = $Record1->attributes();			
		$vid=$attrs1['id']; 
		$vhref=$attrs1['href'];
		if (trim($vhref) == trim($vhreffile)){
			return $vid;
			}
		}	
	return $vresult;
	}	

// ---------------------------------
// Get the number of elements in array not empty
function  count_array_notempty($varray) {
	$cc=0;
	// print_r($varray);
	foreach($varray as $vvval){	
		if (!empty($vvval)) {
			$cc=$cc+1;
			}
		}
	return $cc;
	}

// ---------------------------------
// Get the notations and variant from array...
function GetNotation(&$vdebugid, &$vlastpass, &$vchesslinesW, &$vchesslinesB, &$vcountVariantes, &$vlineDetails, $vonlymainline) {
	
	$vari=1;
	$vfirst=True;
	$vFollowVariantes=True;
	for ($i = 1; $i <= $vlastpass; $i++) {
	// foreach($vchesspgnW as $vvval){		
		//Blancas
		if (!empty($vchesslinesW[1][$i])) {
			// print(($i).". ".$vchesslinesW[1][$i]." ");
			$vNotationSal=$vNotationSal.($i).". ".$vchesslinesW[1][$i]." ";
			if (!$vonlymainline) {
				for ($v = 2; $v <= 5; $v++) {  //variantes								
					if ( (!empty($vchesslinesW[$v][$i]))  && (empty($vchesslinesB[$v][$i-1])) ) {

						//Mostrar variantes de una jugada y que ya se mostró?
						if ((count_array_notempty($vchesslinesW[$v])+count_array_notempty($vchesslinesB[$v]))==1) {
							for ($dd = 1; $dd <= 5; $dd++) {
								if ( ($dd<>$v) && ($vchesslinesW[$dd][$i]==$vchesslinesW[$v][$i]) ) {
									$vFollowVariantes=False;
									}
								}							
							// $vchesslinesW[$v][$i]=null;
							}
						
						if ($vFollowVariantes) {
							// print("(");
							$vNotationSal=$vNotationSal."(";
							for ($b = $i; $b <= $vlastpass; $b++) {
								if (!empty($vchesslinesW[$v][$b])) {
									if (!$vfirst) {
										// print(" "); 
										$vNotationSal=$vNotationSal." ";
										}
									$vfirst=False;
									// print(($b).". ".$vchesslinesW[$v][$b]);
									$vNotationSal=$vNotationSal.($b).". ".$vchesslinesW[$v][$b];
									// $vchesslinesW[$v][$b]=null;
									}
								if (!empty($vchesslinesB[$v][$b])) {
									// print(" ".$vchesslinesB[$v][$b]);
									$vNotationSal=$vNotationSal." ".$vchesslinesB[$v][$b];
									// $vchesslinesB[$v][$b]=null;
									}
								}
							// print(") c1:".(count_array_notempty($vchesslinesW[$v])+count_array_notempty($vchesslinesB[$v]))." ");
							// print(") ");
							$vNotationSal=$vNotationSal.") ";
							}
						}	
					}
				}					
			}
		else { //ver si es un error?
			if (!empty($vchesslinesW[1][$i+1])) {
				// print(($i).". "."_ERROR_"."  ");
				$vNotationSal=$vNotationSal.($i).". "."_ERROR_"."  ";
				}
			}
		
		//Negras
		if (!empty($vchesslinesB[1][$i])) {
			if ( ($vchesslinesB[1][$i]=="_ERROR_") && (!empty($vchesslinesB[1][$i+1])) ) {
				// print("_ERROR_"." ");
				$vNotationSal=$vNotationSal."_ERROR_"." ";
				}
			else {
				// print($vchesslinesB[1][$i]." ");
				$vNotationSal=$vNotationSal.$vchesslinesB[1][$i]." ";
				}
				
			if (!$vonlymainline) {	
				for ($v = 2; $v <= 5; $v++) {  //variantes
				
					if ( (!empty($vchesslinesB[$v][$i])) && (empty($vchesslinesW[$v][$i])) ) {	
						$vFollowVariantes=True;
						
						//Mostrar variantes de una jugada y que ya se mostró?
						if ((count_array_notempty($vchesslinesW[$v])+count_array_notempty($vchesslinesB[$v]))==1) {
							for ($dd = 1; $dd <= 5; $dd++) {
								if ( ($dd<>$v) && ($vchesslinesB[$dd][$i]==$vchesslinesB[$v][$i]) ) {
									$vFollowVariantes=False;
									}
								}							
							// $vchesslinesB[$v][$i]=null;
							}
						
						if ($vFollowVariantes) {
							// print("(");
							$vNotationSal=$vNotationSal."(";
							for ($b = $i; $b <= $vlastpass; $b++) {
								// print("Debug2 [$v][$b]/$vlastpass: ".($vchesslinesW[$v][$b]) );
								if ($b == $i) {
										// print(($b).". ... ".$vchesslinesB[$v][$b]);
										$vNotationSal=$vNotationSal.($b).". ... ".$vchesslinesB[$v][$b];
										// $vchesslinesW[$v][$b]=null;
										}
								else {
									if (!empty($vchesslinesW[$v][$b])) {
										// print(" ".($b).". ".$vchesslinesW[$v][$b]);
										$vNotationSal=$vNotationSal." ".($b).". ".$vchesslinesW[$v][$b];
										// $vchesslinesW[$v][$b]=null;
										}
									if (!empty($vchesslinesB[$v][$b])) {								
										// print(" ".$vchesslinesB[$v][$b]);
										$vNotationSal=$vNotationSal." ".$vchesslinesB[$v][$b];
										// $vchesslinesB[$v][$b]=null;
										}								
									}
								}
							// print(") c2:".(count_array_notempty($vchesslinesW[$v])+count_array_notempty($vchesslinesB[$v]))." ");
							// print(") ");
							$vNotationSal=$vNotationSal.") ";
							}
						}
					
					}	
				}
			}
		else { //ver si es un error?
			if (!empty($vchesslinesB[1][$i+1])) {
				// print("_ERROR_ ");
				$vNotationSal=$vNotationSal."_ERROR_ ";
				}
			}	
			
		}
	$vNotationSal=str_replace(". ...", "...",  $vNotationSal);
	return $vNotationSal;
}
	
// ---------------------------------
// Show notation in a ViewerJs
function ShowNotation(&$vdebugid, &$vlastpass, &$vchesslinesW, &$vchesslinesB, &$vcountVariantes, &$vlineDetails) {
	// array_filter($array, function($x) { return !empty($x); });
	$vNotationSal="";
	
	if ( ($vlastpass>0) && ($vchesslinesW[1][1]!="_ERROR_") && (!empty($vchesslinesW[1][1])) ) {	//&& ((!empty($vchesslinesW[1][1])))  )  {	//&& ((($vchesslinesW[1][1]!=="_ERROR_")))	
		// print("<textarea rows='3' cols='50'>");
		$vNotationSal=GetNotation($vdebugid, $vlastpass, $vchesslinesW, &$vchesslinesB, $vcountVariantes, $vlineDetails, False);
		
		$vlineDetails=trim(strip_tags($vlineDetails, '<font>'));
		
		$vlineDetails=str_replace('&nbsp;'," ",$vlineDetails); 
		$vlineDetails=str_replace('</font> ',"</font>{",$vlineDetails); 
		$vlineDetails=str_replace(' {',"{",$vlineDetails); 
		$vlineDetails=str_replace('{{',"{",$vlineDetails); 
		$vlineDetails=str_replace('{<font',"<font",$vlineDetails);		 
		$vlineDetails=str_replace('} ',"}",$vlineDetails); 
		$vlineDetails=str_replace('}}',"}",$vlineDetails); 		
		$vlineDetails=str_replace('  '," ",$vlineDetails); 		
		// $vlineDetails=str_replace('{ }'," ",$vlineDetails); 
		// $vlineDetails=str_replace('{.}'," ",$vlineDetails); 
		// $vlineDetails=str_replace('{;}'," ",$vlineDetails); 
		// $vlineDetails=str_replace('{,}'," ",$vlineDetails); 
		$vlineDetails=preg_replace('/\{[\s\.,;]\}/', ' ', $vlineDetails);
		$vlineDetails=str_replace('{}'," ",$vlineDetails); 
		$vlineDetails=str_replace('<font'," <font",$vlineDetails);
		$vlineDetails=str_replace('<font/>',"<font/> ",$vlineDetails);		 		
		$vlineDetails=trim(strip_tags($vlineDetails));
		$vlineDetails=str_replace('{'," {",$vlineDetails); 		
		$vlineDetails=str_replace('  '," ",$vlineDetails);
		if ( (substr_count($vlineDetails,'{')+1==substr_count($vlineDetails,'}')) ) {
				$vlineDetails="{".$vlineDetails;
				}
		else {
			if ( (substr_count($vlineDetails,'{')==substr_count($vlineDetails,'}')+1) ) {
					$vlineDetails=$vlineDetails."}";
				}
			}
		if ((strpos($vNotationSal,"(") != false)) {  //insertar en notation details todos los paréntesis con las variantes...
			// while ((strpos($vNotationSal,"(") != false)) {
				// $vtemp=strpos($vNotationSal,"(");
				
				// }
			}
	
		$vNotationFIN=$vNotationSal;
		if ( ((strpos($vNotationSal,"(") == false)) &&  (substr_count($vlineDetails,'{')==substr_count($vlineDetails,'}')) ) {  //((strpos($vNotationSal,"(") == false)) && 
			$vNotationFIN=$vlineDetails;
			$vNotationFIN=preg_replace('/(.*>)(1\..*)/', '\2', $vNotationFIN);
			$vNotationFIN=strip_tags(str_replace("'","",$vNotationFIN));				
			}
		if ((strpos($vNotationSal,"_ERROR_") != false)) {
			$vNotationFIN="";
			}
			
		if (empty($GLOBALS["vnoanalysis"])) {
			// print('<div id="divdebug'.($vdebugid).'" style="display: none;">');
?>
		
<script>
   	var cfg = { position: 'start', pgn: '<?php print($vNotationFIN); ?>', locale: 'en', layout: 'top-right', pieceStyle: 'merida' };		
    // var board = pgnEdit('board<?php print($vdebugid); ?>', cfg);
	
	config = {pgn: '1. e4 e5 2. Nf3 Nc6 3. Bc4 Nf6 4. d4 exd4 5. O-O Nxe4 6. Re1 d5 7. Bxd5 Qxd5 ', position: 'start', showNotation: true, orientation: 'white', theme: 'chesscom', pieceStyle: 'merida', locale: 'en', layout: 'top-right', };
	var board = pgnEdit('board<?php print($vdebugid); ?>', cfg);
	// showDiv('divbutton<?php print($vdebugid); ?>');
	// showDiv('board<?php print($vdebugid); ?>');

	showDiv('divbutton<?php print($vdebugid); ?>');
</script>
<?php	
			}  //fin print tablero
		
	if ((strpos($vNotationSal,"_ERROR_") != false)) {
		print("<textarea rows='3' cols='50'>$vNotationSal</textarea>");
		}
		
		// print('<input type="button" value="Debug..." onclick="javascript: showDiv(\'divdebug'.($vdebugid).'\');" />');
		print('<div id="divdebug'.($vdebugid).'" style="display: none;">');
		print("<textarea rows='3' cols='50'>$vNotationSal</textarea>");
		print("<textarea rows='3' cols='50'>$vlineDetails</textarea>");
		
		// print("W:<pre>"); print_r($vchesslinesW); print("</pre>"); 
		// print("B:<pre>"); print_r($vchesslinesB); print("</pre>"); 
		
		print("<table><tr><td>Línea</td><td>Jugada</td><td>Blancas</td><td>Negras</td></tr>");
		for ($i = 1; $i <= $vlastpass; $i++) {
			for ($v = 1; $v <= 5; $v++) {  //variantes								
				if  ( (!empty($vchesslinesW[$v][$i])) || (!empty($vchesslinesW[$v][$i])) ) {
					print("<tr><td>$v</td><td>$i</td><td>".($vchesslinesW[$v][$i])."</td><td>".($vchesslinesB[$v][$i])."</td></tr>");
					}
			}
		}
		print("</table>");
		
		print("Cant. jugadas: ".($vlastpass));
		// print("<hr>$vlineDetails<hr>");
		print('</div>');	
		print('<hr/>');		
		
		}
	for ($i = 1; $i <= 5; $i++) {
		$vchesslinesW[$i]=array();
		$vchesslinesB[$i]=array();
		}	
	$vActLine=1;  //Línea de variante actual
	$vcountVariantes=1;  //número de variantes encontradas (la línea principal se considera una variante)
	$vlastpass=0;
	$varicant=1;
	$vlineDetails="";	
	$vdebugid=$vdebugid+1;	
	}  //fin de function ShowNotation()
		


//=============================================================================
//if upload a epub file to be upload and parsed...
if($_FILES['esource']['name'])
	{
	 //if no errors...
	 if(!$_FILES['esource']['error'])
		 {
			// print_r($_FILES['esource']);
			// move_uploaded_file($_FILES['esource']['tmp_name'], '$vebookpath/'.$_FILES['esource']['name']);

			// $vebookname='$vebookpath/'.$_FILES['esource']['name'];
			$vebookname=$_FILES['esource']['tmp_name'];

			// get the absolute path to $file
			// $path = pathinfo(realpath($vebookname), PATHINFO_DIRNAME);
			$path = "$vebookpath/".$_FILES['esource']['name'];
			$vebook=$_FILES['esource']['name'];
			// print("Path: $path");			

			$zip = new ZipArchive;
			$res = $zip->open($vebookname);
			if ($res === TRUE) {
			  // extract it to the path we determined above
			  $zip->extractTo($path);
			  $zip->close();
			  // echo "WOOT! $file extracted to $path";
			}
?>		
<script>
	window.location.replace("?ebook=<?php print($vebook); ?>");
</script>
		<?php	
		exit;
		 }
	}
	
	
//-------------------------------------
// Ebook uploadedand process ...
if (!empty($vebook)) {
	$vebookOEBPS="";
	$path = "$vebookpath/$vebook";
	$vimagesrel="../images";  //Image folder in ebook
	$vtextsrel="";  //Text folder in ebook
	if ((is_dir("$path/OEBPS")) || (file_exists("$path/OEBPS/toc.ncx")) ){
		$path = "$vebookpath/$vebook/OEBPS";
		$vimagesrel="images";
		$vtextsrel="Text"; 
		}	

	// print("Path: $path");

	
	if (true) {  //create a index from toc.ncx
		$xmlurl = "$path/toc.ncx";
		// echo $xmlurl;
		
		if (file_exists($xmlurl)) {	
			if (!empty($vebookcapsearch)) {
				$vebookcapsearch=getebookId("$path/content.opf", $vebookcapsearch);
				}
			$XMLDoc = simplexml_load_file("$xmlurl");
			// var_dump($XMLDoc);
			foreach($XMLDoc->navMap->navPoint as $Record)
				{
				$vvvaltext=(string)$Record->navLabel->text;
				$attrs = $Record->content->attributes();
				$vindval=($attrs["src"]);
				// echo ' '.($attrs["src"]).'" '.$vvvaltext.' ';
				$vesel="";
				if ($vebookcapsearch==$vindval) {
					$vesel=" selected";
					}
				$vindice=$vindice."<a href='?ebook=$vebook&c=$vindval' $vesel>$vvvaltext</a>";
				foreach($Record->navPoint as $Record1) {
					$vvvaltext=(string)$Record1->navLabel->text;
					$attrs = $Record1->content->attributes();
					$vindval=($attrs["src"]);
					$vesel="";
					if ($vebookcapsearch==$vindval) {
						$vesel=" selected";
						}
					$vindice=$vindice."<a href='?ebook=$vebook&c=$vindval' $vesel> » $vvvaltext</a>";
					}
				}
			}	
		}
		// print($vindice);
	
	//Parse the content/index of the ebook 
	$vposcap=0;
	$xmlurl = "$path/content.opf";
	if (file_exists($xmlurl)) {		
		$XMLDoc = simplexml_load_file("$xmlurl");
		// <option value="audi" selected>Audi</option>
		// echo "<select name='booktitle'>";
		
		foreach($XMLDoc->spine->itemref as $Record)
			{
			$attrs = $Record->attributes();
			$vebookfile=$attrs['idref'];
			$vposcap=$vposcap+1;
			$vselected="";
			if ( ( ($vposcap==$vebookpos) && (empty($vebookcapsearch)) ) || (($vebookpos==1) && ($vebookfile==str_replace($vtextsrel."/","",$vebookcapsearch))) ) {  // 
				$vebookpos=$vposcap;
				$vselected=" selected";
				$vurl=str_replace("//","/","$path/$vtextsrel/$vebookfile");				
				if (!file_exists($vurl)) {
				$vhref=getebookHref($xmlurl, $vebookfile);
				// echo("<br>url1: $vurl");
				if (!empty($vhref)) {
						$vurl=str_replace("//","/","$path/$vhref");
						}
					}					
				}
			
			}
		$vposcantcap=$vposcap;
		}	
		if ($debug) {
			echo("<br><a href='$vurl'>$vurl</a>");	
			}
	
		
	
	// $vebook=(isset($_GET["ebook"]) ? trim($_GET["ebook"]) : ''); 
	// $vebookpos=(isset($_GET["p"]) ? int(trim($_GET["p"])) : 1);
	if ($vebookpos>1) {
		$vposant="<a href='?ebook=$vebook&p=".($vebookpos-1)."'  >".($vebookpos-1)."</a> - ";
		} else {
		$vposant="";
		}
	if ($vebookpos<$vposcantcap) {
		$vpospost=" - <a href='?ebook=$vebook&p=".($vebookpos+1)."' >".($vebookpos+1)."</a>";
		} else {
		$vpospost="";
		}	
	$vurlpostact="?ebook=$vebook&p=".($vebookpos);
	$vmenu =" | $vposant [$vebookpos] $vpospost / $vposcantcap";
	// echo (" | $vposant [$vebookpos] $vpospost / $vposcantcap");
	// $vurl="$path/OEBPS/Text/sec_0008.xhtml";
	}



// ==============================================================	
   $vnewurl="";

   if (True) {
     $vcontent = file_get_contents(trim($vurl)); 

     //$ch = curl_init($vwebsite);  
     //curl_setopt($ch, CURLOPT_HEADER, 0);  
     // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
     //$vcontent = curl_exec($ch);
  
     //print_r($vcontent);
     //$vcontent=mb_convert_encoding($vcontent, 'HTML-ENTITIES', "UTF-8");
     
	$vtitle=""; 
	$vlinkinserted=False; 
	$vmatches=array();
	$vchesslinesW=array();
	$vchesslinesB=array();
	for ($i = 1; $i <= 5; $i++) {
		$vchesslinesW[$i]=array();
		$vchesslinesB[$i]=array();
		}
	$vActLine=1;
	$vcountVariantes=1;  //número de variantes encontradas (la línea principal se considera una variante)
	$vlastposiLine=1;
	
	$vlastpass=0;
	$vposiLine=1;
	$vcolorno=0;
	$varicant=1;
	$vlineDetails="";
	
	
	$vcontent=str_replace('<br />',"<br />\r\n",$vcontent); 
	$vcontent=str_replace('<br/>',"<br />\r\n",$vcontent); 
	$vcontent=str_replace('<br>',"<br />\r\n",$vcontent); 
	$vcontent=str_replace('<BR />',"<br />\r\n",$vcontent); 
	$vcontent=str_replace('<BR/>',"<br />\r\n",$vcontent); 
	$vcontent=str_replace('<BR>',"<br />\r\n",$vcontent); 
	$vcontent=str_replace('<h',"\r\n<h",$vcontent); 
	$vcontent=str_replace('</h2>',"</h2>\r\n",$vcontent); 
	$vcontent=str_replace('</h1>',"</h1>\r\n",$vcontent); 
	$vcontent=str_replace('\r\n\r\n\r\n',"\r\n",$vcontent); 
	$vcontent=str_replace('\r\n\r\n',"\r\n",$vcontent);  
	
	$vcontent=str_replace('</html>',"",$vcontent);  
	$vcontent=str_replace('</body>',"",$vcontent);  
	$vcontent=str_replace('<image','<img',$vcontent);
	$vcontent=str_replace('xlink:href="../','src="'.$vurl."/../../",$vcontent);
	
	
	// <link href="../Styles/estilos.css" rel="stylesheet" type="text/css"/>
	if (!empty($vebook)) {
		// $vline=str_replace('../Images',$vurl.'/../../Images',$vline);
		$vcontent=str_replace('<link href="../Styles','<link href="'.$vurl.'/../../Styles',$vcontent); 
		}

	// |<br \/>|<br\/>|<br>|<BR \/>|<BR\/>|<BR>
	foreach(preg_split("/\r\n|\n|\r/", $vcontent) as $vline){
			if  ( ( preg_match('/<title>(.*)<\/title>/', $vline, $vmatches) ) ) {
				$vtitle=($vmatches[1]);
				} 
			}
	
	$vfinstr= (trim($vfinstr)=="") ? "</body>" : $vfinstr ;
	$vinistr= (trim($vinistr)=="") ? "<body" : $vinistr ;
	$vfollow=True;
	$vinit=False;
	$vmetalinkwith=False;
	
	
	// $vurl
	
	foreach(preg_split("/\r\n|\n|\r/", $vcontent) as $vline){ 
		$vline=str_replace("'prev'","'tttprev'",$vline);
		$vline=str_replace('"prev"','"tttprev"',$vline);
		
		
		if (!empty($vebook)) {
			if (strpos(" ".$vline,"<im") >1 )  { 
				// <p><img alt="" src="../Images/g8a.jpg"/></p>
				// <image width="370" height="550" xlink:href="../Images/i0.jpg"/>
				// if (strpos("  ".$vline,"<image") >1 )  { 
					$vline=str_replace('<image','<img',$vline);
					$vline=str_replace('xlink:href','src',$vline);
					// }
				
				$vimagename=preg_replace('/.*src="(.*)\.jpg.*/', '\1', $vline);
				$vimagename=basename($vimagename);
				
				if ( ( (!empty($vlineDetails)) && (empty($vnoimg)) ) && ((!empty($vimgdl)) || (!empty($vimgpreview))) )  {   //&& (strpos($vline,"..") == 0)
					$vNotationSal=GetNotation($vdebugid, $vlastpass, $vchesslinesW, &$vchesslinesB, $vcountVariantes, $vlineDetails, True);
					// $vline=$vline."\r\n<div id='image".($vdebugid)."' ></div>";
	?>		
	<div id='image<?php echo $vdebugid; ?>' ></div>
	<?php			
				$vdl="0";
				if (!empty($vimgdl)) { 
					$vdl="1";
					}
				$vJScripts=$vJScripts."\r\n ShowNewChessBoardImage('image".$vdebugid."','".$vNotationSal."','".$vimagename."','".$vdl."');";
				// <p><img alt="" src="ebookChess/Aprende a hacer trampas al ajedrez - Lococo Cobo, Nicola.epub/OEBPS/Text/sec_0008.xhtml/../../Images/g4.jpg"/></p>

					}
				if  (empty($vnoimg)) {		
					$vline=str_replace('../Images','/../../Images',$vline);
					$vline=str_replace('src="','src="'.$vurl.'/../',$vline);
					$vline=str_replace('</p>','<strong>'.$vimagename.'</strong></p>',$vline);				
					}
				}
			}
		else {
			// <h2 class="entry-title"><a href="//blogs.deia.eus/ajedreztxiki/2017/01/08/celada-grob/" rel="bookmark">Celada Grob</a></h2>
			if ((strpos("  ".$vline,"<h2") > 1) || (strpos("  ".$vline,"<h1") > 1) ) {
				$vtitleline=strip_tags($vline);
				$vtitlemark=trim(str_replace(' ','_',$vtitleline));					
				$vline=str_replace("<h","<a name='$vtitlemark'></a>\r\n<h",$vline);
				$vindice=$vindice."<a href='#$vtitlemark'>$vtitleline</a>";
				//<div id="myLinks">
				}
			}
		
		$vlineoriginal=$vline;
		
		$vprocessthisline=True;			
				
		if ($vonlymainlinesNotations) { //Process only the main chess line of notations without variants?
			$vtempo=strip_tags($vline);
			$visnumberfirst=is_numeric(substr($vtempo, 0, 1));
			if (!$visnumberfirst) {
				$vprocessthisline=False;
				}
			}
				
		if ( ($vinit) &&  ($vprocessthisline) ) {
	
			
			$vline=str_replace('—','-',$vline); 
			$vline=str_replace('0-0-0','O-O-O',$vline); 
			$vline=str_replace('0–0','O-O',$vline);
 
			$vline=str_replace('…','...',$vline); $vline=str_replace('&#8230;','...',$vline);
			$vline=str_replace('...',' ... ',$vline);
			$vline=str_replace(' ... -',' ... ',$vline);
						
			// $vline=str_replace(' y ...',' ',$vline);
			$vline=str_replace('++','#',$vline);
			// $vline=str_replace('0','O',$vline);
			$vline=preg_replace('/0[^0-9]0/', 'O-O', $vline);
			if ( ($vinit) ) {
				$vline=preg_replace('/(\d)\.([A-Za-z\d])/', '\1. \2', $vline);
				$vline=preg_replace('/(\d)([A-Za-z])/', '\1 \2', $vline);
				}
			$vline=str_replace('  ',' ',$vline);
			// $vline=str_replace('<br />','<br />',$vline);
			// $vline=str_replace('<br/>','<br />',$vline);
			// $vline=str_replace('<br>','<br />',$vline);
			
			// <h2 class="entry-title"><a href="//blogs.deia.eus/ajedreztxiki/2015/12/17/celada-muro-de-adriano/" rel="bookmark">Celada Muro de Adriano</a></h2>

			// preg_match_all('/(\d{1,2}(\s+)?)(((\.\.\.)|(?:(?:O-O(?:-O)?)|(?:[RDACT][a-h]?x?[a-h]x?[1-8])|(?:[a-h]x?[a-h]?[1-8]))\+?)(\s+|[,;\:\.])?((?:(?:O-O(?:-O)?)|(?:[RDACT][a-h]?x?[a-h]x?[1-8])|(?:[a-h]x?[a-h]?[1-8]))\+?)?)/', $vline, $matches, PREG_PATTERN_ORDER);
			preg_match_all('/(\d{1,2}(\.\s)?(\s+)?)(((\.\.\.)|(?:(?:O-O(?:-O)?)|(?:[RDACTKQBNR][1-8]?x?[a-h]x?[1-8])|(?:[RDACTKQBNR][a-h]?x?[a-h]x?[1-8])|(?:[a-h]x?[a-h]?[1-8]))([\?\!\+=#]+)?)(\s+|\-)?((?:(?:O-O(?:-O)?)|(?:[RDACTKQBNR][1-8]?x?[a-h]x?[1-8])|(?:[RDACTKQBNR][a-h]?x?[a-h]x?[1-8])|(?:[a-h]x?[a-h]?[1-8]))([\?\!\+\-=#]+)?)?)/', $vline, $matches, PREG_PATTERN_ORDER);
			
			
			if ( ($matches) && ($matches[0][0]<>"")  ) {
				// print("<pre>"); print_r($matches[0]); print("</pre>"); //<font color='red'>$matches[0]</font>";
				foreach($matches[0] as $vplay){ 
					$vplayEN=$vplay;
					$vplayEN=str_replace('C','N',$vplayEN); 
					$vplayEN=str_replace('A','B',$vplayEN); 
					$vplayEN=str_replace('D','Q',$vplayEN); 
					// $vplayEN=str_replace('R','K',$vplayEN); 
					$vplayEN=str_replace('T','R',$vplayEN); 	
					
					if ($vcolorno==1) {
						$vcolor="brown";
						$vcolorno=0;
					} else {
						$vcolor="green";
						$vcolorno=1;
					}
					
					$vNotation=$vplayEN;
					$vpppos=0;
					$vresaltar=True; //Resaltar la notación encontrada?
					// $vlastpass=0;
					// $vposiLine=1;
										
					foreach(preg_split("/[\s]+/", $vplayEN) as $vvval){
						$vpppos=$vpppos+1;	
						if ($vpppos==1) {  //Es un número 1., 2.
							$vNotation=$vvval;
							$vposiLine=intval($vvval);
							if ($vposiLine<$vlastposiLine) {
								$vActLine=1;								
								}	
							if ($vposiLine>$vlastpass) {
								$vlastpass=$vposiLine;
								}	
							$vlastposiLine=$vposiLine;
							
							if (strpos($vvval,".") == false) {
								$vNotation=$vvval.".";								
								}
							if ( (($vlastpass>1) || ($vlastposiLine>1)) && (intval($vvval)==1) )  {									
								ShowNotation(&$vdebugid, $vlastpass, $vchesslinesW, $vchesslinesB, $vcountVariantes, $vlineDetails);
								}								
							}
						elseif ($vpppos==2) {  //Notación de la parte de las blancas
							$vNotation=$vNotation." ".$vvval;
							if ($vvval!="...") {  // Notación blanca
								if (empty($vchesslinesW[$vActLine][$vposiLine])) {
									// $vchesslinesW[$vActLine][$vposiLine]=$vvval;
									
									if ( ($vActLine==1) && ($vposiLine>1) && (empty($vchesslinesB[$vActLine][$vposiLine-1])) ) {  //Validar que ya exista una notación en la jugada anterior?
										// $vchesslinesW[$vActLine][$vposiLine-1]="_ERROR_";
										$vresaltar=False;
										}
									else {
										$vchesslinesW[$vActLine][$vposiLine]=$vvval;
										}
									}
								else {  //es una variante, localizar la última libre para agregar
									$vcountVariantes=$vcountVariantes+1;
									$vActLine=$vcountVariantes;
									$vchesslinesW[$vActLine][$vposiLine]=$vvval;
									
									// $notvarfound=True;
									// for ($i = $vActLine+1; $i <= 5; $i++) {
										// // print("<Br/> DebugW: $notvarfound ($vposiLine $vvval) i:$i (vActLine:$vActLine) posiLine:$vposiLine ".(empty($vchesslinesW[$i][$vposiLine])) );
										// if ( (empty($vchesslinesW[$i][$vposiLine])) && ($notvarfound) ) {
											// $vActLine=$i; 
											// $notvarfound=False;
											// $vchesslinesW[$vActLine][$vposiLine]=$vvval;
											// }
										// }
									}
								}
							else { //Notación de la parte de las negras (3. ... h4 )
								// Se realiza en el tercer pase su parsing...
								}
														
							}
						else {  //Notación de la jugada de las negras $vpppos==3
							$vNotation=$vNotation." ".$vvval;
							
							if (True) {  // Notación Negra
								if (empty($vchesslinesB[$vActLine][$vposiLine])) {
									// $vchesslinesB[$vActLine][$vposiLine]=$vvval;
									
									// if ( ($vActLine==1) && (empty($vchesslinesB[$vActLine][$vposiLine-1])) && (!empty($vchesslinesB[$vActLine][$vposiLine])) ) {  //Validar que ya exista una notación en la jugada anterior?
										// $vchesslinesB[$vActLine][$vposiLine-1]="_ERROR_";
										// }
										
									if ( ($vActLine==1) && (empty($vchesslinesW[$vActLine][$vposiLine])) ) {  //Validar que ya exista una notación en la jugada anterior?
										// $vchesslinesW[$vActLine][$vposiLine-1]="_ERROR_";
										$vresaltar=False;
										}
									else {
										$vchesslinesB[$vActLine][$vposiLine]=$vvval;
										}
										
									}
								else {  //es una variante, localizar la última libre para agregar
									$vcountVariantes=$vcountVariantes+1;
									$vActLine=$vcountVariantes;
									$vchesslinesB[$vActLine][$vposiLine]=$vvval;
								
									// $notvarfound=True;
									// for ($i = $vActLine+1; $i <= 5; $i++) {
										// // print("<Br/> DebugB: $notvarfound ($vposiLine...$vvval) i:$i (vActLine:$vActLine) posiLine:$vposiLine ".(empty($vchesslinesW[$i][$vposiLine])) );
										// if ( (empty($vchesslinesB[$i][$vposiLine])) && ($notvarfound) ) {
											// $vActLine=$i; 
											// $notvarfound=False;
											// $vchesslinesB[$vActLine][$vposiLine]=$vvval;
											// }
										// }
									}
								}
			
							}
						}
						
					if ($vresaltar) {
						$vline=str_replace($vplay,"<font color='$vcolor'>$vNotation</font>",  $vline);
						}	
					// $vline=preg_replace('/'.$vplay.'[;,]?/', "<font color='$vcolor'>$vNotation</font>", $vline);
				}
				
			$vline1=$vline;				
			$vdebugid=$vdebugid+1;
			$vline=$vline."\r\n<div id='board".($vdebugid)."' style='' class='merida zeit' style='display: none;' ></div>";
			$vline=$vline."\r\n".'<div id="divbutton'.($vdebugid).'" style="display: none;"><br /><input type="button" value="Debug..." onclick="javascript: showDiv(\'divdebug'.($vdebugid).'\');" /></div>';
			
				
			
			$vline1=str_replace("</font> <font color='brown'>"," ",  $vline1);
			$vline1=str_replace("</font> <font color='green'>"," ",  $vline1);

			$vline1=str_replace("{","",  $vline1); 
			$vline1=str_replace("}","",  $vline1);
			// $vline1=str_replace("<br />"," ",  $vline1);
			// $vline1=str_replace("<br>"," ",  $vline1);
			// $vline1=str_replace("<BR />"," ",  $vline1);
			// $vline1=str_replace("<BR>"," ",  $vline1);
			
			$vline1=str_replace("</font>","</font>{",  $vline1);
			$vline1=str_replace("<font","}<font",  $vline1);
			
			$vtempoline=strip_tags($vline1, '<font>');
			// print("<hr>tempoline:".$vtempoline);
			
			
			if ( trim(substr($vtempoline, 0, 1)=="}")  ) {  //&& (substr($vtempoline, 0, 2)=="}<")
				$vline1=preg_replace('/}/', '', $vline1, 1);
				}
			if ( (substr($vtempoline, -1, 1)!="}") && (strpos($vline1,"{")>1) ) {
				$vrepfin=substr($vtempoline, -5);
				$vline1=str_replace($vrepfin,$vrepfin."}",  $vline1);
				}
				
			$vline1=str_replace("{; }"," ",  $vline1);	
			$vline1=str_replace("{, }"," ",  $vline1);
			$vline1=str_replace("{.}","",  $vline1);
			$vline1=str_replace("</font>{}","</font>{",  $vline1);
			
			if ( (substr($vtempoline, -1, 1)=="{") ) {
				$vrepfin=substr($vtempoline, -10,9);
				$vline1=str_replace($vrepfin."{",$vrepfin,  $vline1);
				}
			
			
			// $vline1=preg_replace('/<\/font>(.+?(?=<font))/', '</font>{\1}', $vline1);
			$vlineDetails=$vlineDetails." ".str_replace(". ...", "...",  $vline1);
			}
		else {  //si no cumple la línea con contener una notación de ajedrez dejarla como estaba original
			if (strpos("   ".$vlineoriginal,'<span class="h')>1) {
				ShowNotation(&$vdebugid, $vlastpass, $vchesslinesW, $vchesslinesB, $vcountVariantes, $vlineDetails);
				}
			$vline=$vlineoriginal;
			}
		}
		
		if (strpos($vline,"entry-content") !== false) {
			ShowNotation(&$vdebugid, $vlastpass, $vchesslinesW, $vchesslinesB, $vcountVariantes, $vlineDetails);
			// print("<textarea rows='4' cols='50'>");			
			// for ($i = 1; $i <= $vlastpass; $i++) {
			// // foreach($vchesspgnW as $vvval){
				// if (!empty($vchesspgnW[$i])) {
					// print(($i)." ".$vchesspgnW[$i]." ");
					// }
				// if (!empty($vchesspgnB[$i])) {
					// print($vchesspgnB[$i]." ");
					// }					
				// }
			print("</textarea>");
			
			}

		
		
		if ( ( (strpos("   ".$vline,'</head>')>0) ) ) {  //or (strpos("   ".$vline,'<meta name="generator"')>0)

			?> 	
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body {
  font-family: Arial, Helvetica, sans-serif;
}

.mobile-container {
  max-width: 480px;
  margin: auto;
  background-color: #555;
  height: 500px;
  color: white;
  border-radius: 10px;
}

.topnav {
  overflow: hidden;
  background-color: #333;
  position: relative;
}

.topnav #myLinks {
  display: none;
}

.topnav a {
  color: white;
  padding: 14px 16px;
  text-decoration: none;
  font-size: 17px;
  display: block;
}

.topnav a.icon {
  background: black;
  display: block;
  position: absolute;
  right: 0;
  top: 0;
}

.topnav a:hover {
  background-color: #ddd;
  color: black;
}

.active {
  background-color: #4CAF50;
  color: white;
}
</style>	
			<!-- Library to convert png to fen-->
			<script language="JavaScript" src="js/ltpgnviewer.js"></script>
			
			<!-- Libraries Js from PgnViewerJS -->
			<script src="js/pgnvjs.js" type="text/javascript"></script>			

			<!-- CSS used -->
			<link rel="stylesheet" href="css/pgnvjs.css">
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	
			   <script>
			   function showDiv(velemid) {
					var x = document.getElementById(velemid);
					if (x.style.display === "none") {
						x.style.display = "block";
					} else {
						x.style.display = "none";
					}
				}
				
				function pgn2fen(pgn) { 
				  Init("");
				  SetPgnMoveText(pgn);  
				  var ff="", ff_new="", ff_old;  
				  do
				  { ff_old=ff_new;
					MoveForward(1);
					ff_new=GetFEN();
					if (ff_old!=ff_new) ff+=ff_new+"\n";
				  }
				  while (ff_old!=ff_new);
				  
				  <!-- window.document.forms[0].fen.value=ff; -->  
				  ff=GetFEN();
				  return(ff);
				}	
				
				function NotationEN2EN(vnotationES) {
					vnotationEN=" "+vnotationES;
					while (vnotationEN.includes("C")) { vnotationEN=vnotationEN.replace("C","N"); }
					while (vnotationEN.includes("A")) { vnotationEN=vnotationEN.replace("A","B"); }
					while (vnotationEN.includes("D")) { vnotationEN=vnotationEN.replace("D","Q"); }
					// vnotationEN=vnotationEN.replace("R","K");
					while (vnotationEN.includes("T")) { vnotationEN=vnotationEN.replace("T","R"); }
					while (vnotationEN.includes("  ")) { vnotationEN=vnotationEN.replace("  "," "); }
					while (vnotationEN.match(/ (\d+) /)) { vnotationEN=vnotationEN.replace(/ (\d+) /, ' $1. '); }
					while (vnotationEN.match(/ (\d+) \.\.\. /)) { vnotationEN=vnotationEN.replace(/ (\d+) \.\.\. /, ' '); }
					while (vnotationEN.match(/ (\d+)\. \.\.\. /)) { vnotationEN=vnotationEN.replace(/ (\d+)\. \.\.\. /, ' '); }
					while (vnotationEN.includes("  ")) { vnotationEN=vnotationEN.replace("  "," "); }
					vnotationEN=vnotationEN.trim()
					return(vnotationEN);
					}
					
				function ShowNewChessBoardImage(vdiv,vpgn,iname,vdl) {
				vpgnEN=NotationEN2EN(vpgn);
				vfen=pgn2fen(vpgnEN);	 
				vurl="http://www.jinchess.com/chessboard/?p="+vfen+"&ps=merida-flat&gs";  //&tm=w / &tm=b
				vurldl=vurl+"&s=s&download="+iname;  //&s=s
				vformpgn="document."+vdiv+".pgn.value";
				vformimg="document."+vdiv+".img.value";
				document.getElementById(vdiv).innerHTML = "<table><tr><td><p><img src='"+vurl+"&s=s' /></td><td><form  name='"+vdiv+"'><br>PGN: <textarea rows='3' cols='100' name='pgn'>"+vpgnEN+"</textarea><br>Image: <input name='img' value='"+iname+"'><br>FEN: "+vfen+"<br><a href='javascript:ShowNewChessBoardImage(\""+vdiv+"\","+vformpgn+","+vformimg+")'>Refresh!</a> <a href='javascript:open(\""+vurldl+"\")'>Download!</a> <a href='javascript:open(\""+vurl+"&download1="+iname+"\")'>Window Preview!</a></form></td></tr></table></p>";
				if (vdl=="1") {
					open(vurldl);
					}
				}
				
				
				</script>

<body>

<!-- Top Navigation Menu -->
<div class="topnav">
  
  <a href="?" class="active">Home</a>
  <div id="myLinks">
	<?php echo $vindice; ?>	
  </div>
  <a href="javascript:void(0);" class="icon" onclick="myFunction()">
    <i class="fa fa-bars"></i>
  </a>
</div>	
<?php echo $vebook." ".$vmenu; ?>
<hr/>
<script>
function myFunction() {
  var x = document.getElementById("myLinks");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}
</script>
			
				<?php
				
		   if  (!$vlinkinserted) {
			   $vlinkinserted=True; 
			   
			   print($vnewurl."\r\n");
			   // print($vline."\r\n");  

				// print("<body>\r\n");   
							
				
			   $vmetalinkwith=True;
		       }
			   
			  		   
		   }
		else {
			// if ( (trim($_GET["url"])=="") and (strpos("   ".$vline,'<title>')>0) ) {
				// print("<title>La ayuda que nuestra historia puede brindarte</title>\r\n");
				// }
			// else {
				// print($vline."\r\n");
				// }
			$vinit=( (strpos("   ".$vline,$vinistr)>0) or $vinit );
			$vfollow= ( (strpos("   ".$vline,$vfinstr)==0) and $vfollow );  // ( trim($vline)<>trim($vfinstr) )
			if ( ($vfollow and $vinit) or (!$vmetalinkwith) ) {
				if ( (strpos("   ".$vline,"rss")==0) and (strpos("   ".$vline,"xml")==0) ) {
					print($vline."\r\n");		
					}				
				}
				
			
		   }

		}
   }
   ShowNotation(&$vdebugid, $vlastpass, $vchesslinesW, $vchesslinesB, $vcountVariantes, $vlineDetails);

    

?>
<hr />   

<?php 

if ( ($debug) || ((empty($vurl)) && (empty($vebook))) ) {
?>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body>
	<form name='main' method="post" enctype="multipart/form-data">
	   Chess URL: <input name='url' type='text' value='<?php echo $vurl; ?>' /><br />
	   ePub File: <input type="file" name="esource" accept=".epub" onchange="javascript:document.main.submit();alert('Espera a que se suba y procese el archivo...');" />
	   <input name='submit' type='submit' value='Parse!' />
	</form>
	<?php 
	if (empty($vebook)) {
		print("<h3>Chess ebooks:</h3>");
		$dirs = array_filter(glob($vebookpath.'/*'), 'is_dir');
		foreach($dirs as $vd) {
			$vn=str_replace($vebookpath.'/','',$vd);
			print("<a href='?ebook=$vn'>$vn</a><br />");
			}
		}	
	?>

	
	<?php
		
	  // print("[".htmlentities($vnewurl)."]<br/>");
	  print("<pre>"); print_r($_GET);print("</pre>");
	  print("<pre>"); print_r($_FILES);print("</pre>");
	  
	  if (!isset($_GET["url"])) {
		  print("<a href='parseChess.php'>parseChess.php</a>");
	  }
  	}
	
	// -------------------
	if (empty($vebook)) {
		// $vindice=$vindice."<a href='#$vtitlemark'>$vtitleline</a>";
		//<div id="myLinks">
		?>
		<script>
		document.getElementById("myLinks").innerHTML = "<?php echo  $vindice; ?>";
		<?php echo $vJScripts; ?>
		</script>
		<?php
		}


echo $vebook." ".$vmenu; 
echo "<br />[<a href='$vurlpostact&imgpreview'>Preview the diagrams images</a>]";
echo "<br />[<a href='$vurlpostact&imgdl'>Preview and Download the diagrams images</a>]";
echo "<br />[<a href='$vurlpostact&noimg'>No Images</a>]";
echo "<br />[<a href='$vurlpostact&noanalysis'>No Analysis</a>]";
echo "<br />[<a href='$vurlpostact&onlymainlines'>Only mainchesslines...</a>]";
echo "<br />[<a href='$vurlpostact'>Refresh</a>]";
echo "<br />[<a href='$vurlpostact&bruto'>No Parsing</a>]";


?>

<script>

<?php echo $vJScripts; ?>

</script>

</body>
</html>