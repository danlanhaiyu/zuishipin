<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {
	
    //首页
	public function index() {
		$this->assign('img', rand(1,21));
    	$this->display(":index");
    }
	
	public function creatthumb(){
		$allfile = $this->getDir('data/upload/portal');
		foreach($allfile as $k => $v){
			$this->cthumb($v);
		}
		
	}
	
	function cthumb($file){
		//20170807 TD 将图片进行裁剪另外生成缩略图
		$imagetd = new \Think\Image();
		$imagetd->open($file);
		//20170807 TD 生成一个居中裁剪为300*300的缩略图并保存为****_thunm.***
		$newfn = explode(".",$file);
		$imagetd->thumb(300, 300,\Think\Image::IMAGE_THUMB_FIXED)->save($newfn[0]."_thumb.".$newfn[1]);
		echo $newfn[0]."_thunm.".$newfn[1]."<br>";
		/*
		IMAGE_THUMB_SCALE     =   1 ; //等比例缩放类型
		IMAGE_THUMB_FILLED    =   2 ; //缩放后填充类型
		IMAGE_THUMB_CENTER    =   3 ; //居中裁剪类型
		IMAGE_THUMB_NORTHWEST =   4 ; //左上角裁剪类型
		IMAGE_THUMB_SOUTHEAST =   5 ; //右下角裁剪类型
		IMAGE_THUMB_FIXED     =   6 ; //固定尺寸缩放类型
		*/
	}
	
	function searchDir($path,&$data){
		if(is_dir($path)){
			$dp=dir($path);
			while($file=$dp->read()){
				if($file!='.'&& $file!='..'){
					$this->searchDir($path.'/'.$file,$data);
				}
			}
			$dp->close();
		}
		if(is_file($path)){
			$data[]=$path;
		}
	}
	
	function getDir($dir){
		$data=array();
		$this->searchDir($dir,$data);
		return   $data;
	}

}


