<?php

namespace Module;
class Curl {
	
    public function __construct(){
    	
    }
    
    public  static function getApi($service, $data = array(), $type = 'get') {
        $url_root = C('REQUEST_HAPI_URL') . 'service=' . $service . '&';
        unset($data['api_sign']);
        $api_sign = authSign($data, C('APP'));
        
        switch ($type) {
            case 'get':
                $queryString = http_build_query($data);
                $url_root .= $queryString . '&api_sign='. $api_sign;
                $result = self::curlGet($url_root);
            break;
            case 'post':
                $result = self::curlPost($url_root, $data);	
            break;
            default:
            break;
        }

        //开发程序排错使用
        preg_match('/^[{].*[}]$/i', $result,$matches);
        if(empty($matches)){
        	var_dump($result);exit;
        }
        
        $result = json_decode($result, true);
        
        //记录日志
        $logMsg = array( 'url'    => $url_root,
                         'param'  => $data,
                         'result' => $result
                  );
        LogInfo(json_encode($logMsg));
    
        if ($result['code'] == 200) {
            $result = $result['result'];
        } else {
             Ex($result['code']);
        }
        
        return $result;
    }
    
    /**
     * 生成签名请求底层接口
     */
    public  static function curlGet($url, $time_out = 30) {
        $ch = curl_init($url) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_TIMEOUT,$time_out); 
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; 
        $output = curl_exec($ch) ;
        curl_close ($ch);
        return $output;
    }
    
    public  static function curlPost($uri, $data ) { 
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $uri );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        return $result;
   }
}
?>