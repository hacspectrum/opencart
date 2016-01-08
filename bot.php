<?php 
header('Access-Control-Allow-Origin: *');  
header('Content-Type: text/html; charset=utf-8');
class Bot {
	public $dom;
	public $resimYolu;
	public function __construct($dom)
    {
		$this->dom = $dom;
    }
	public function bul($sonuc,$alan1,$alan2){
			$baslangic=strpos($sonuc,$alan1);
			$sonuc_yeni=substr($sonuc,$baslangic+strlen($alan1));
			
			$son=strpos($sonuc_yeni,$alan2);
			return $this->bosluk_temizle(substr($sonuc_yeni,0,$son));
	}
	
	function sqlresult($sql)
	{
		$result='';
		$tt1 = _sql($sql);
		if (@mysql_num_rows($tt1)>0)	$result = mysql_result ($tt1,0);
		@mysql_free_result($tt1);	
		return $result;
	}
	
	public function opencart($prefix,$user,$pass,$db,$tip,$localhost='127.0.0.1'){
		
		$result='';
		if($tip=='kategori'){
		$t1=mysql_query('select category_id,name FROM '.$prefix.'category_description');
			$result.= '<select name="kategori">';
			$result.= '<option value="">Opencart Kategori Seçiniz...</option>';
			while($s1=mysql_fetch_array($t1)){
			$result.= '<option value="'.$s1['category_id'].'">'.$s1['name'].'</option>';
			}
			$result.= '</select>';
			
		}
		return $result;
	}
	
	public function opencart_bosalt(){
		mysql_query("TRUNCATE `oc_option_value_description`");
		mysql_query("TRUNCATE `oc_category_description`");
		mysql_query("TRUNCATE `oc_category`");
		mysql_query("TRUNCATE `oc_category_path`");
		mysql_query("TRUNCATE `oc_product_description`");
		mysql_query("TRUNCATE `oc_product_image`");
		mysql_query("TRUNCATE `oc_product`");
		mysql_query("TRUNCATE `oc_product_option`");
		mysql_query("TRUNCATE `oc_product_option_value`");
		mysql_query("TRUNCATE `oc_product_reward`");
		mysql_query("TRUNCATE `oc_product_to_category`");
		mysql_query("TRUNCATE `oc_product_to_store`");	
		mysql_query("TRUNCATE `oc_product_special`");
	}
	
	public function opencart_aktar($urun,$parent=0,$islem=1,$dil=2){
			
			
			if($islem==1){
				$kategori_adi=$urun['kategori'];
				mysql_query("INSERT INTO `oc_category` (`image`, `parent_id`, `top`, `column`, `sort_order`, `status`, `date_added`, `date_modified`) VALUES ('', $parent, 0, 1, 0, 1, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')");
				$id=mysql_insert_id();
				mysql_query("INSERT INTO `oc_category_description` (`category_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`) VALUES ($id, $dil, '$kategori_adi', '', '', '')");
				if($parent>0){
					mysql_query("INSERT INTO `oc_category_path` (`category_id`, `path_id`, `level`) VALUES ($id, $parent, 0)");
					mysql_query("INSERT INTO `oc_category_path` (`category_id`, `path_id`, `level`) VALUES ($id, $id, 1)");
				}else{
					mysql_query("INSERT INTO `oc_category_path` (`category_id`, `path_id`, `level`) VALUES ($id, $id, 0)");
				}
				
				mysql_query("INSERT INTO `oc_category_to_store` (`category_id`, `store_id`) VALUES ($id, 0)");

				return $id;
			}else{
			
			$kategori_id=$urun['kategori'];
			$ad=$urun['adi'];
			$ozellik=mysql_real_escape_string(htmlspecialchars(@$urun['ozellik']));
			$tutar=str_replace('.','',$urun['fiyat']);
			
			$resimyolunuz=$this->resimYolu;
			$eski_fiyat=str_replace(',','.',$urun['efiyat']);
			$yeni_fiyat=str_replace(',','.',$urun['fiyat']);
			
			if($eski_fiyat>0){
				$tutar=$eski_fiyat;
			}else $tutar=$yeni_fiyat;
			
			
			mysql_query("INSERT INTO `oc_product` (`model`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `quantity`, `stock_status_id`, `image`, `manufacturer_id`, `shipping`, `price`, `points`, `tax_class_id`, `date_available`, `weight`, `weight_class_id`, `length`, `width`, `height`, `length_class_id`, `subtract`, `minimum`, `sort_order`, `status`, `date_added`, `date_modified`, `viewed`) VALUES ('$ad', '', '', '', '', '', '', '', 1, 5, '', 0, 1, '$tutar', 0, 0, '2014-08-11', 0.00000000, 1, 0.00000000, 0.00000000, 0.00000000, 1, 1, 1, 1, 1, '2014-08-12 22:23:37', '0000-00-00 00:00:00', 0)");
			
			$urun_id=mysql_insert_id();
			
			if($eski_fiyat>0){
				mysql_query("INSERT INTO `oc_product_special` (`product_id`, `customer_group_id`, `priority`, `price`, `date_start`, `date_end`) VALUES ($urun_id, 1, 1, $yeni_fiyat, '0000-00-00', '0000-00-00')");
			}
			
			mysql_query("INSERT INTO `oc_product_description` (`product_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `tag`) VALUES ($urun_id, $dil, '$ad', '$ozellik', '', '', '')");
			mysql_query("INSERT INTO `oc_product_reward` (`product_id`, `customer_group_id`, `points`) VALUES ($urun_id, 1, 0)");
			mysql_query("INSERT INTO `oc_product_to_category` (`product_id`, `category_id`) VALUES ($urun_id, $kategori_id)");
			mysql_query("INSERT INTO `oc_product_to_store` (`product_id`, `store_id`) VALUES ($urun_id, 0)");
			
			// Seçenek Belirleme
			
						
			if(count($urun['beden'])>0){
				
				$s=0;
				
				mysql_query("INSERT INTO `oc_option` (`type`, `sort_order`) VALUES ('select', 1)");
				$oc_option_id=mysql_insert_id();
				mysql_query("INSERT INTO `oc_option_description` (`option_id`, `language_id`, `name`) VALUES ($oc_option_id, $dil, 'Beden')");	
				
				foreach($urun['beden'] as $b){
					
					mysql_query("INSERT INTO `oc_option_value` (`option_id`,`sort_order`) VALUES ($oc_option_id,$s)");
					$oc_option_value_id=mysql_insert_id();
					
					mysql_query("INSERT INTO `oc_option_value_description` (option_value_id, `language_id`, `option_id`, `name`) VALUES ($oc_option_value_id, $dil, $oc_option_id, '$b')");
					$oc_option_value_description_id=mysql_insert_id();
					
					if($s==0){
						mysql_query("INSERT INTO `oc_product_option` (`product_id`, `option_id`, `option_value`, `required`) 
						VALUES ($urun_id, $oc_option_id, '', 0)");
						$oc_product_option_id=mysql_insert_id();
					}
					
					mysql_query("INSERT INTO `oc_product_option_value` (`product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES 
					($oc_product_option_id, $urun_id, $oc_option_id, $oc_option_value_id, 1, 1, 0.0000, '+', 0, '+', 0.00000000, '+')");
					$s++;
				}
				
			}
			
			
			if($urun['renk']!=''){
			
				mysql_query("INSERT INTO `oc_option` (`type`, `sort_order`) VALUES ('select', 1)");
				$oc_option_id=mysql_insert_id();
				mysql_query("INSERT INTO `oc_option_description` (`option_id`, `language_id`, `name`) VALUES ($oc_option_id, $dil, 'Renk')");	
			
				mysql_query("INSERT INTO `oc_option_value` (`option_id`,`sort_order`) VALUES ($oc_option_id,0)");
					$oc_option_value_id=mysql_insert_id();
					
					mysql_query("INSERT INTO `oc_option_value_description` (option_value_id, `language_id`, `option_id`, `name`) VALUES ($oc_option_value_id, $dil, $oc_option_id, '".$urun['renk']."')");
					$oc_option_value_description_id=mysql_insert_id();
					
					
						mysql_query("INSERT INTO `oc_product_option` (`product_id`, `option_id`, `option_value`, `required`) 
						VALUES ($urun_id, $oc_option_id, '', 0)");
						$oc_product_option_id=mysql_insert_id();
					
					
					mysql_query("INSERT INTO `oc_product_option_value` (`product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES 
					($oc_product_option_id, $urun_id, $oc_option_id, $oc_option_value_id, 1000, 1, 0.0000, '+', 0, '+', 0.00000000, '+')");
					$s++;
				
				
			}
			
				$key=0;
				foreach($urun['resim'] as $val){
					$key++;
					$dosya=explode('uplfiles/',$val);
					$dosya_adi=$dosya[1];
					$res=$this->indir($val,$resimyolunuz.$dosya_adi);
					mysql_query("INSERT INTO `oc_product_image` (`product_id`,`image`,`sort_order`) VALUES ('$urun_id','data/demo/$dosya_adi', 0)");
					if($key>2) mysql_query("UPDATE oc_product SET image='data/demo/$dosya_adi' WHERE product_id=".$urun_id);
					
				}
				
		}
	}
	
	
	public function viewstate($deger,$code=0){
		
		$V['__VIEWSTATE'] = $this->bul($deger, "__VIEWSTATE\" value=\"", "\" />");
		$V['__EVENTVALIDATION'] = $this->bul($deger, "__EVENTVALIDATION\" value=\"", "\" />");
		$V['__EVENTTARGET'] = $this->bul($deger, "__EVENTTARGET\" value=\"", "\" />");
		$V['__EVENTARGUMENT'] = $this->bul($deger, "__EVENTARGUMENT\" value=\"", "\" />");
		//$V['__PREVIOUSPAGE'] = $this->bul($deger, "__PREVIOUSPAGE\" value=\"", "\" />");

		if($code==1){
			$V['__VIEWSTATE']=urlencode($V['__VIEWSTATE']);
			$V['__EVENTVALIDATION']=urlencode($V['__EVENTVALIDATION']);
			$V['__EVENTTARGET']=urlencode($V['__EVENTTARGET']);
			$V['__EVENTARGUMENT']=urlencode($V['__EVENTARGUMENT']);
			//$V['__PREVIOUSPAGE']=urlencode($V['__PREVIOUSPAGE']);
			
		}
		return $V;
	}
	public function bosluk_temizle($string)
	{
		$string = preg_replace("/\s+/", " ", $string);
		$string = preg_replace("/\n+/", " ", $string);
		$string = preg_replace("/\t+/", " ", $string);
		$string = trim($string);
		return $string;
	}
	public function temizle($metin,$temizle){
		
		if(is_array($temizle)){
			foreach($temizle as $vl){
				$metin=str_replace($v1,'',$metin);
			}
		}elseif(strpos($temizle,',')){
			$silinecek=explode($temizle,',');
			for($i=0;$i<count($silinecek);$i++){
				$metin=str_replace($v1,'',$metin);
			}
		}else{
			$metin=str_replace($temizle,'',$metin);
		}
		return $metin;
	}
	public function dom($html,$path){
		@$this->dom->loadHTML($html);
		$xpath = new DOMXPath($this->dom);
		$result=$xpath->query($path);
		return $result;
	}
	public function dom_array($html,$path){
		$dom=$this->dom($html,$path);
		foreach($dom as $node){
			$array[] = $node;
		}
		return $array;
	}
	
	public function href_cek($vl){
		preg_match_all('/<a href="(.*)">/',$vl,$cikti);
		return $cikti[1];
	}
	
    public function dom_attr($html,$path,$attr,$x=-1){
        $dom=$this->dom($html,$path);
        foreach($dom as $node){
			if ($attr=='nodeValue')$array[] = $node->nodeValue;
                              else $array[] = $node->getAttribute($attr);
        }
        if ($x>=0) $array=@$array[$x];
        return @$array;
    }
    public function tr($tr){
	$hallet=iconv("UTF-8","Windows-1252",$tr); 
	return $hallet; 
	}
	public function random () {
	  return (float)rand()/(float)getrandmax();
	}
	public function degistir($veri,$aranacak,$degisecek=''){
		$snc=str_replace($aranacak,$degisecek,$veri);
		return $snc;
	}
	public function seo($tr1) {
		$turkce=array("ş","Ş","ı","ü","Ü","ö","Ö","ç","Ç","ş","Ş","ı","ğ","Ğ","İ","ö","Ö","Ç","ç","ü","Ü");
		$duzgun=array("s","S","i","u","U","o","O","c","C","s","S","i","g","G","I","o","O","C","c","u","U");
		$tr1=str_replace($turkce,$duzgun,$tr1);
		$tr1 = preg_replace("@[^a-z0-9\-_şıüğçİŞĞÜÇ]+@i","-",$tr1);
		return $tr1;
	}
	function indir($img, $fullpath) {
		$ch = curl_init($img);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		$rawdata = curl_exec ( $ch );
		curl_close($ch);
		if (file_exists($fullpath)) {
				unlink($fullpath);
		}
		$fp = fopen($fullpath, 'x');
		fwrite($fp, $rawdata);
		fclose($fp);
		return $rawdata;
	}
	public function gonder($url,$array) {
		
		$curl = curl_init ();
		if(strpos(' '.$url,'https://')>0){
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 2);
		}
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		//curl_setopt ( $curl, CURLOPT_VERBOSE, true );
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
		if($array['header']) curl_setopt ( $curl, CURLOPT_HTTPHEADER, array($array['header'])) ;
		curl_setopt ( $curl, CURLOPT_USERAGENT, $array['agent'] );
		if($array['referer']!='') curl_setopt ( $curl, CURLOPT_REFERER, $array['referer']);
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 300 );
		curl_setopt ( $curl, CURLE_OPERATION_TIMEOUTED, 300 );
		if(isset($array['cerez'])){
		curl_setopt ( $curl, CURLOPT_COOKIEJAR, $array['cerez']);   // Cookie management.
		curl_setopt ( $curl, CURLOPT_COOKIEFILE, $array['cerez']);
		}
		if (isset($array['proxy'])) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
        curl_setopt($curl, CURLOPT_PROXY,$array['proxy']);
		}
		if(isset($array['method']) && $array['method']=='options'){
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $array['method']);
		}
		curl_setopt ( $curl, CURLOPT_HEADER, false );
		if($array['post']!=''){
		curl_setopt ( $curl, CURLOPT_POST, true );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $array['post'] );
		}
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_ENCODING , 1);
		$result = curl_exec ( $curl );
		if(curl_errno($curl))
		{
			$result= curl_error($curl);
		}
		if($array['kapat']) curl_close ( $curl );
		return $result;
	}
	public function __destruct()
    {
	 //
    }
}
$dom = new DOMDocument();
$bot = new Bot($dom);
//$dom->childNodes
?>
