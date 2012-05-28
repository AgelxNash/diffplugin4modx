<?
/**************************************
** Diff plugin for Modx Evo
**
** @category plugin
** @version 1.0
** @author Borisov Evgeniy aka Agel Nash (agel-nash@xaker.ru)
** @date 28.05.2012
**
** @internal @event OnSnipFormSave,OnSnipFormRender,OnSnipFormDelete
** @internal @properties &nameBlock=Заголовок;text;Версии &idBlock=ID блока;text;Version &folderPlugin=Папка плагина;text;diff &which_jquery=Подключить jQuery;list;Не подключать,Локально (assets/js),Удаленно (google code),Свой url;Удаленно (google code) &js_src_type=Свой url к библиотеке jQuery;text;
** @internal @modx_category Manager and Admin
**
**
*************************************/
require_once '../../../manager/includes/protect.inc.php'; 
include_once ('../../../manager/includes/config.inc.php');
include_once (MODX_BASE_PATH.'manager/includes/document.parser.class.inc.php');
include_once (MODX_BASE_PATH.'assets/modules/docmanager/classes/docmanager.class.php');
$modx = new DocumentParser;
$modx->getSettings();
startCMSSession();

if(!(isset($_SESSION['mgrPermissions']['edit_snippet']) && isset($_SESSION['mgrPermissions']['save_snippet']) && $_SESSION['mgrPermissions']['save_snippet']==1 && $_SESSION['mgrPermissions']['edit_snippet']==1)){
	return;
}

$mode=(isset($_GET['mode']))?$_GET['mode']:'';
$dir=pathinfo(__FILE__);
$dir=$dir['dirname'];

if(isset($_GET['file']) && $_GET['file']!='' && isset($_GET['id']) && (int)$_GET['id']>0 && file_exists($dir.'/snippet/'.(int)$_GET['id']."/".$_GET['file'])){
	$file=$dir.'/snippet/'.(int)$_GET['id']."/".$_GET['file'];
}else{
	return;
}

switch($mode){
	case 'load':{
		echo base64_decode(file_get_contents($file));
		break;
	}
	case 'del':{
		$flag=unlink($file);
		if($flag){
			$data=unserialize(file_get_contents($dir.'/snippet/version.inc'));
			$tmp=$data[(int)$_GET['id']];
			unset($tmp['last']);
			foreach($tmp as $id=>$item){
				if($item['file']==$_GET['file']){
					unset($data[(int)$_GET['id']][$id]);
				}
			}
			if(file_put_contents($dir.'/snippet/version.inc',serialize($data))){
				echo 'good';
			}			
		}
		break;
	}
	default:{
		return;
	}
}
?>