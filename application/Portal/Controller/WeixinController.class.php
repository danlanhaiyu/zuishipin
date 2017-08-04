<?php

namespace Portal\Controller;
use Common\Controller\HomebaseController; 
use Portal\Lib\Wechat\wxBizMsgCrypt;

/**
 * 首页  http://www.kemayixian.com/index.php?m=weixin&a=index
 */
class WeixinController extends HomebaseController {

    function index(){
		$token = "zspznd2017wjzwx";
		$encodingAesKey = "YCyMDPt1yuvLu5ltUi6ioz1VA09skyv4httWmsdZEVN";
		$appId = "wxd3e67fd9c1ce6e9f";
		
		//公众号服务器数据  
		$sReqMsgSig = $sVerifyMsgSig = $_GET['msg_signature'];  
		$sReqTimeStamp = $sVerifyTimeStamp = $_GET['timestamp'];  
		$sReqNonce = $sVerifyNonce = $_GET['nonce'];  
		$sReqData = file_get_contents("php://input");;  
		$sVerifyEchoStr = $_GET['echostr'];
		
		//echo $sVerifyEchoStr;exit;//验证token使用
		
		/*ob_start();
        print_r($_GET);
        $out = ob_get_contents();        
        ob_clean();
        file_put_contents('smg_response_111.txt',date('Y-m-d H:i:s',time())." >(return)\n".$out,FILE_APPEND);
		*/
		
		//decrypt  
		$sMsg = "";  //解析之后的明文
		$wxcpt = new wxBizMsgCrypt($token, $encodingAesKey, $appId);
		$errCode = $wxcpt->decryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);  

		if ($errCode == 0) {
			$xml = new \DOMDocument();  
			$xml->loadXML($sMsg);
			$toUsername = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;  
			$fromUsername = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;  
			$time = $xml->getElementsByTagName('CreateTime')->item(0)->nodeValue;  
			$msgType = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;  
			$reqContent = $xml->getElementsByTagName('Content')->item(0)->nodeValue;  
			$reqMsgId = $xml->getElementsByTagName('MsgId')->item(0)->nodeValue;  
			$reqAgentID = $xml->getElementsByTagName('AgentID')->item(0)->nodeValue;
			
			$startxml = "<xml>";
			$endxml = "</xml>";
			$comxml = "<ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType>";
			$thehelptext = "海~亲爱的朋友，您已经成功关注【最视频之脑洞微信号 <a href='http://www.kemayixian.com'>www.kemayixian.com</a>】。可回复以下相应内容查看最新视频
【1】笑一下
【2】短动画
【3】恶搞整人
【10】随机视频
【0】帮助
或者输入相关关键字查看相关视频，如：恶搞 。";
			$keyword_str = $keyword = $reqContent;
			file_put_contents('smg_response.txt', $keyword_str); //debug:查看smg
			$posts_model = M("Posts");
			if((int)$keyword!=0){//数字
				/*
				ob_start();
				print_r("1");
				$out = ob_get_contents();        
				ob_clean();
				file_put_contents('smg_response_111.txt',date('Y-m-d H:i:s',time())." >(return)\n".$out,FILE_APPEND);*/
			
				$keyword = intval($keyword);
				if($keyword>0 && $keyword<10){
					$i = 0;
					$newslist=$posts_model->alias("a")
						->field('a.*,b.term_id')
						->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id")
						->where(array('b.term_id'=>$keyword))
						->limit("3")
						->order("b.object_id desc")
						->select();
					
				}elseif($keyword==10){
					$i = 0;
					$newslist=$posts_model->alias("a")
						->field('a.*,b.term_id')
						->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id")
						->limit("3")
						->order("rand()")
						->select();
					$needhelp = false;
				}							
			}else{
				if($keyword_str=='0'){
					$needhelp = true;
				}else{
					$where = array();
					$where["a.post_title"] = array('like',"%".$keyword_str."%");					
					//$where["a.post_excerpt"] = array('like',"%{$keyword_str}%");
					//$where["a.post_content"] = array('like',"%{$keyword_str}%");
					$newslist = $posts_model->alias("a")->field('a.*,b.term_id')
						->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id")
						->where($where)
						->limit("3")
						->order("b.object_id desc")
						->select();
					$needhelp = false;
				}
			}
			
			if($newslist){
				$othertpl = "<Articles>";
				foreach($newslist as $k => $v){
					$smeta=json_decode($v['smeta'], true);
					$othertpl .= "<item><Title>".$v['post_title']."</Title><Description>".$v['post_excerpt']."</Description>";
					$othertpl .= "  <PicUrl>".sp_get_asset_upload_path($smeta['thumb'])."</PicUrl>";
					$othertpl .= "	<Url>http://www.kemayixian.com/article/".$v["term_id"]."/".$v['id'].".html</Url></item>";//index.php?m=article&a=index&id=7&cid=1
					$i++;
				}
				$othertpl .= "</Articles>";
				$thisTpl = "<ArticleCount>%s</ArticleCount>";
				$xmlTpl = $startxml.$comxml.$thisTpl;//集合xml
				$msgType = "news";
				$resultStr = sprintf($xmlTpl, $fromUsername, $toUsername, $time, $msgType, $i);
				$sRespData = $resultStr.$othertpl.$endxml;
				$needhelp = false;
			}else{
				$thisTpl = "<Content><![CDATA[%s]]></Content><FuncFlag>0</FuncFlag>";
				$xmlTpl = $startxml.$comxml.$thisTpl.$endxml;//集合xml
				$msgType = "text";
				$thehelptext = "抱歉，暂无这方面视频！可回复以下相应内容查看最新视频
【1】笑一下
【2】短动画
【3】恶搞整人
【10】随机视频
【0】帮助
或者输入相关关键字查看相关视频，如：恶搞 。";
				$resultStr = sprintf($xmlTpl, $fromUsername, $toUsername, $time, $msgType, $thehelptext);
				$sRespData = $resultStr;
				$needhelp = false;
			}
			
			if($needhelp){//是否发送帮助信息
				$thisTpl = "<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>";
				$xmlTpl = $startxml.$comxml.$thisTpl.$endxml;//集合xml
				$msgType = "text";
				$resultStr = sprintf($xmlTpl, $fromUsername, $toUsername, $time, $msgType, $thehelptext);
				$sRespData = $resultStr;
			}

			$sEncryptMsg = ""; //xml格式的密文  
			$errCode = $wxcpt->EncryptMsg($sRespData, $sReqTimeStamp, $sReqNonce, $sEncryptMsg);  
			
			if ($errCode == 0) {
				//ob_start();      
				ob_clean();
				echo $sEncryptMsg;
				exit;
				//print($sEncryptMsg);
				//echo "success";
			} else {
				ob_clean();
				echo $errCode;
				//print($errCode . "\n\n");
				exit;
				//echo "success";
			}  
		}else { 
			ob_clean();
			echo $errCode;
			//print($errCode . "\n\n"); 
			exit;
		} 
		
	}
	
}


