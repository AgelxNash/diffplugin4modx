<?
/**************************************
/** 
* Diff plugin for Modx Evo
*
* en: Class to work with the history of changes in snippets, chunks, templates, modules and plugins
* ru: ����� ��� ������ � �������� ��������� � ���������, ������, ��������, ������� � ��������
* 
* ���������� ��������
* <code>
* $Diff=new ElementVer($modx,'template',$folderPlugin);
* $Diff->save($modx->Event->params['id'],'post');
* </code>
*
* �������� ��������
* <code>
* $Diff=new ElementVer($modx,'snippet',$folderPlugin);
* $Diff->del($modx->Event->params['id']);
* </code>
*
* ����� ����� � ��������
* <code>
* $Diff=new ElementVer($modx,'snippet',$folderPlugin);
* $out=$Diff->loadJs($idBlock,$which_jquery,$jqname,$js_src_type);
* $modx->Event->output($out);
* </code>
*
* @version 2.1
* @author Borisov Evgeniy aka Agel Nash (agel_nash@xaker.ru)
* @date 31.05.2012
* @copyright 2012 Agel Nash
* @link http://agel-nash.ru
* @license http://www.opensource.org/licenses/lgpl-3.0.html LGPL 3.0
*
* @internal @event OnTempFormDelete,OnTempFormSave,OnTempFormRender,OnSnipFormDelete,OnSnipFormSave,OnSnipFormRender,OnPluginFormDelete,OnPluginFormSave,OnPluginFormRender,OnModFormDelete,OnModFormSave,OnModFormRender,OnChunkFormDelete,OnChunkFormSave,OnChunkFormRender
* @internal @properties &idBlock=ID �����;text;Version &folderPlugin=����� �������;text;diff &which_jquery=���������� jQuery;list;�� ����������,/assets/js/,google code,custom url;/assets/js/ &js_src_type=���� url � ���������� jQuery;text; &jqname=��� Jquery ���������� � noConflict;text;j &lang=�����������;list;en,ru;ru
* @internal @modx_category Manager and Admin
*
* @todo �������� � ��������� ����������� ������� ������� ����� ��������� ���������
* @todo �������� ��������� ������
* @todo �������������� ����������� �����������
* @todo ������� ����� � �������� � /assets/cache/
*/
/*************************************/

class ElementVer implements langVer{
	/** @var string ���� �� ������� ������ � �������� ���� ��������� */
	public $verfile='';
	/** @var string �������� ����� � �������� */
	public $dir='';
	/** @var class ��������� ������� modx */
	private $modx;
	/** @var string ������� ����� � ������� �������� */
	private $active='';
	/** @var string  ��� jQuery ���������� � ������� ������ ����� �������� */
	private $jqname='';
	/** @var string ������� ������� � ������� �������� */
	private $ver=0;
	
	/**
	* ����������� ������
	* �������� ����� ����� ���� �� � �� ����������, �� �.�. � ������� modx ��� ����� ��������� �� ����������, �� �� ����� ������ ������ ���
	* @param class $modx ��������� ������� modx
	* @param string $active ��� �������� � ������� ����� �������� (snippet | template | plugin | module | chunk)
	* @param string $dir �������� ����� � ��������
	* @param string $ver ���� � ������� ����� ��������� ��� ������
    */
	function __construct(&$modx,$active,$dir,$ver='version.inc'){
		$this->modx=$modx;
		if(!(is_object($this->modx) && isset($this->modx->Event->name))){
			exit(langVer::err_nomodx);
		}
		if(in_array($active,array('snippet','template','plugin','module','chunk'))){
			$this->active=$active;
		}else{
			exit(langVer::err_mode);
		}
		
		/*
		* en: Still have to specify the folder name in the parameter plug-in
		* ru: ��� ����� �������� ��������� �������� ����� � ��������� �������
		*
		$dir=pathinfo(__FILE__);
		if(!defined('__DIR__')) { 
			$dir=explode("\\",$dir['dirname']);
		}else{
			$dir=explode("/",$dir['dirname']);
		}
		$this->dir=end($dir);
		*/
		$this->dir=$dir;
		$this->verfile=$ver;
	}

	/**
	* ������� ��������� ���� � ����� 
	* @param bool $full ����� ���� � ����� ��������: � http ��� ������������ ����� ���-�������. �� ��������� ������������ �����.
	* @param bool $mode �������� � ���� ����� � ��������� ����� � ������� ������ �������� (����, ������, � �.�.) �� ��������� ������ � ������
	* @return string ���� � ����� 
    */
	public function GVD($full=true,$mode=true){
		$dir=($full?$this->modx->config['base_path']:$this->modx->config['site_url']).'assets/plugins/'.$this->dir.'/'.($mode?($this->active.'/'):'');
		return $dir;
	}
	
	/**
	* ������� ������� �������� javascript ��� ��������������� �������� {@link render}
	* @see {@link render}
	* @param string $idBlock ID HTML ����� ������� ����� ��������� �� ��������. �� ��������� Version
	* @param string $which_jquery ��� ����������� jquery � �������� (google code | /assets/js/ | custom url | none). �� ��������� /assets/js/
	* @param string $jqname ��� jQuery ���������� � ������� ������ ����� ��������. �� ��������� j
	* @param string $url ����� �� �������� ����� ������� jQuery ���������� ���� which_jquery ���������� � custom url. �� ��������� �����.
	* @return string HTML 
    */
	public function loadJs($idBlock='Version',$which_jquery='/assets/js/',$jqname='j',$url=''){
		$js_include='';
		$this->jqname=$jqname;
		switch ($which_jquery){
			case 'google code':{
				$js_include  = '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
				break;
			}
			case '/assets/js/':{
				$js_include  = '<script src="'.$this->modx->config['site_url']. '/assets/js/jquery-1.4.4.min.js" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
				break;
			}
			case 'custom url':{
				if($url!=''){
					$js_include  = '<script src="'.$url.'" type="text/javascript"></script><script type="text/javascript">var $'.$this->jqname.' = jQuery.noConflict();</script>';
				}else{
					$js_include='';
				}
				break;
			}
			default:{ //no include;
				$js_include='';
			}
		}
		$js_include.=$this->render($idBlock);
		return $js_include;
	}
	
	/**
	* �� ����� ���������� �������� ��������� ��� ������ � ������
	* @param int $id ID ��������
	* @param string $postname ��� POST ���������� � ������� ���������� ���������� ��������. �� ��������� post
	* @param string $descV ��� POST ���������� �� ���� ����� �������� ������� ������. �� ��������� descVersion
	* @param string $save ��� POST ���������� ������������ ��������� �� ������� ������. �� ��������� savev
	* @return bool ������ ���������� ������� ��������
    */
	public function save($id,$postname='post',$descV='descVersion',$save='savev'){
		if(!(isset($_POST[$postname]) && $_POST[$postname]!='')){
			return false;
		}
		
		$desc=isset($_POST[$descV])?$_POST[$descV]:'';
		
		if(!isset($_POST[$save])){
			return false;
		}
		$put=base64_encode($_POST[$postname]);

		$dir=$this->GVD(true,true);
		if(!is_dir($dir.$id)) {
			if(!mkdir($dir.$id,0777,true)){
				return false;
			}
		}

		$flag=false;
		$file=md5($put);
		if(!file_exists($dir.$id.'/'.md5($put))){
			$count=file_put_contents($dir.$id.'/'.md5($put),$put);
			if($count<=0){
				return false;
			}
			$flag=true;
		}
		if($flag || $desc!=''){
			if(file_exists($dir.'/'.$this->verfile)){
				$data=unserialize(file_get_contents($dir.'/'.$this->verfile));
				$ver=$data[$id]['last'];
				if($flag){
					$data[$id]['last']++;
					$ver++;
					$data[$id][$ver]['file']=$file;
				}
				$data[$id][$ver]['desc']=$desc;
			}else{
				$data[$id]['last']=1;
				$data[$id][1]['desc']=$desc;
				$data[$id][1]['file']=$file;
			}
			$count=file_put_contents($dir.$this->verfile,serialize($data));
			if($count<=0){
				return false;
			}
		}
		return true;
	}
	
	/**
	* �� ����� �������� �������� ������� ��� ��� �������
	* @param int $id ID ��������
	* @return bool ������ �������� ���� ������� ��������
    */
	public function del($id){
		$dir=$this->GVD(true,true);
		if(!file_exists($dir.'/'.$this->verfile)){
			return false;
		}
		$data=unserialize(file_get_contents($dir.'/'.$this->verfile));
		if(!isset($data[$id]['last'])){
			return false;
		}
		unset($data[$id]['last']);
		foreach($data[$id] as $iditem=>$item){
			if(!unlink($dir.$id.'/'.$item['file'])){
				return false;
			}
		}
		unset($data[$id]);
		if(is_dir($dir.$id)){
			if(!rmdir($dir.$id.'/')){
				return false;
			}
		}
		$count=file_put_contents($dir.'/'.$this->verfile,serialize($data));
		if($count<=0){
			return false;
		}
		return true;
	}
	
	/**
	* �������� ������ �� �������� �� ����� � ��������
	* @param int $id ID ��������
    * @access private
	* @return string HTML ��� � ���������� ������� ���� <tr><td>...data...</td></tr>
    */
	private function getDataVer($id){
		$out=array();
		$flag=true;
		$data=array();
		$dir=$this->GVD(true,true);
		
		if(!file_exists($dir.$this->verfile)){
			$flag=false;
		}else{
			$data=unserialize(file_get_contents($dir.$this->verfile));
		}
		if(isset($data[$id]) && $flag){
			$this->ver=$data[$id]['last'];
			unset($data[$id]['last']);
			
			foreach($data[$id] as $iditem=>$desc){
				$tmp='';
				if($desc['desc']==''){
					$tmp=langVer::form_nodesc;
				}else{
					$tmp=htmlspecialchars($desc['desc']);
				}
				if($iditem!=$this->ver){
					$out[$iditem]=langVer::word_ver.' '.$iditem.': <i>'.$tmp.'</i> ';
					$out[$iditem].=' &nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="delversion" rel="'.$desc['file'].'">'.langVer::word_del.'</a> | <a href="#" class="loadversion" rel="'.$desc['file'].'">'.langVer::word_load.' </a> ';
				}else{
					$out[$iditem]='<strong>'.langVer::word_ver.' '.$iditem.': <i>'.$tmp.'</i></strong>';
				}
			}
		}
		
		if(count($out)>0){
			$out=array_reverse($out);
			$out=$this->modx->makeList($out);
		}else{
			$out="<p>".langVer::form_noversion."</p>";
		}
		$out='<tr><td>'.str_replace("'","\"",$out).'</td></tr>';
		return $out;
	}
	
	/**
	* ��������� html � JavaScript'�� ��� ����������� ������ � ������ ������
    * @access private
	* @return string HTML 
    */
	private function render($idBlock){
		$output='';
		if($this->jqname==''){
			exit(langVer::err_loadjs);
		}
		switch($this->active){
			case 'snippet':{
				$js_tab_object='tpSnippet';
				$id=$this->modx->Event->params['id'];
				$lastTab='tabProps';
				break;
			}
			case 'template':{
				$js_tab_object='tpResources';
				$lastTab='tabAssignedTVs';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'plugin':{
				$js_tab_object='tpSnippet';
				$lastTab='tabEvents';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'module':{
				$js_tab_object='tpModule';
				$lastTab='tabDepend';
				$id=$this->modx->Event->params['id'];
				break;
			}
			case 'chunk':{
				/** @todo ��������� ������ */
				exit();
			}
			default:{
				exit(langVer::err_mode);
			}
		}
		
		$output=$this->getDataVer($id);
		
		$output = '<div class="tab-page" id="tab'.$idBlock.'"><h2 class="tab">'.langVer::form_nameblock.'</h2><table width="90%" border="0" cellspacing="0" cellpadding="0" >'.$output.'</table></div>';
		$output=str_replace(array("\n", "\t", "\r"), '', $output);
		
		$output = "<script type=\"text/javascript\">
		mm_lastTab = '".$lastTab."'; 
		\$".$this->jqname."('div#'+mm_lastTab).after('".$output."'); 
		mm_lastTab = 'tab".$idBlock."'; ".
		$js_tab_object.".addTabPage( document.getElementById( \"tab".$idBlock."\" ) ); 
		\$".$this->jqname."('div.sectionBody:first').before('<div class=\"sectionBody\"><p><strong>".langVer::form_descver.":</strong></p><input type=\"text\" name=\"descVersion\" style=\"width:100%\"></p><p><input type=\"checkbox\" name=\"savev\" checked /> ".langVer::form_savever."</p></div>');
		\$".$this->jqname."('.loadversion').click(function(el){
			\$".$this->jqname.".ajax({
				url: '".$this->GVD(false,false)."version.ajax.php?mode=load&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."',
				 cache: false,
				error: function(){
                    alert('".langVer::err_noload."');
                },
				success: function(html){
					if(html!=''){
						if(\$".$this->jqname."('.oldver').length){
							\$".$this->jqname."('.oldver').val(html);
						}else{
							\$".$this->jqname."('.phptextarea[name=post]').after('<div style=\"padding:1px 1px 5px 1px; width:100%; height:16px;background-color:#eeeeee; border-top:1px solid #e0e0e0;margin-top:5px\"><span style=\"float:left;color:#707070;font-weight:bold; padding:3px\">".langVer::form_beforever."</span></div><textarea dir=\"ltr\" name=\oldver\" class=\"phptextarea oldver\" style=\"width:100%; height:370px;\" wrap=\"off\" onchange=\"documentDirty=true;\">'+html+'</textarea>');
						}
					}else{
						alert('".langVer::err_fatalload."');
					}
				}
			});
		});
		\$".$this->jqname."('.delversion').click(function(el){
			\$".$this->jqname.".ajax({
				url: '".$this->GVD(false,false)."version.ajax.php?mode=del&active=".$this->active."&file='+\$".$this->jqname."(this).attr('rel')+'&id=".$id."',
				 cache: false,
				context:\$".$this->jqname."(this).parent('li'),
				error: function(){
                    alert('".langVer::err_noload."');
                },
				success: function(html){
					if(html!=''){
						\$".$this->jqname."(this).remove();
					}else{
						alert('".langVer::err_del."');
					}
				}
			});
		});
		</script>";
		return $output;
	}
}
?>