<?php

/**
 * GamPress Core Abstraction Functions
 * 的
 * @package gampressustom
 * @subpackage Functions
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !is_multisite() ) {
    global $wpdb;

    $wpdb->base_prefix = $wpdb->prefix;
    $wpdb->blogid      = GP_ROOT_BLOG;

    if ( !function_exists( 'get_blog_option' ) ) {
        function get_blog_option( $blog_id, $option_name, $default = false ) {
            return get_option( $option_name, $default );
        }
    }

    if ( !function_exists( 'update_blog_option' ) ) {
        function update_blog_option( $blog_id, $option_name, $value ) {
            return update_option( $option_name, $value );
        }
    }

    if ( !function_exists( 'delete_blog_option' ) ) {
        function delete_blog_option( $blog_id, $option_name ) {
            return delete_option( $option_name );
        }
    }
}

if ( !function_exists( 'utf8_to_gb2312' ) ) {
    function utf8_to_gb2312($raw) {
        $str = '';

        if( $raw < 0x80) {
            $str .= $raw;
        } elseif( $raw < 0x800) {
            $str .= chr( 0xC0 | $raw >> 6 );
            $str .= chr( 0x80 | $raw & 0x3F );
        }elseif( $raw < 0x10000) {
            $str .= chr( 0xE0 | $raw >> 12);
            $str .= chr( 0x80 | $raw >> 6 & 0x3F );
            $str .= chr( 0x80 | $raw & 0x3F );
        } elseif( $raw < 0x200000) {
            $str .= chr( 0xF0 | $raw >> 18 );
            $str .= chr( 0x80 | $raw >> 12 & 0x3F );
            $str .= chr( 0x80 | $raw >> 6 & 0x3F );
            $str .= chr( 0x80 | $raw & 0x3F );
        }

        return iconv('UTF-8', 'GB2312', $str);
    }
}

if ( !function_exists( 'ends_with' ) ) {
    function ends_with( $haystack, $needle ) {
        return $needle === "" || substr( $haystack, -strlen( $needle ) ) === $needle;
    }
}

if ( !function_exists( 'starts_with' ) ) {
    function starts_with( $haystack, $needle ) {
        //return $needle === "" || strpos($haystack, $needle) === 0;
        return substr( $haystack, 0, strlen($needle) ) === $needle;
    }
}

if ( !function_exists( 'get_array_value' ) ) {
    function get_array_value( $array, $position, $values_map = false ) {
        $count = count( $array );

        if ( $position >= $count || $position < 0 )
            return false;

        $value = $array[$position];

        if ( !empty( $values_map ) )
            $value = $values_map[$value];

        return $value;
    }
}

if ( !function_exists( 'printf_array' ) ) {
    function printf_array( $array, $position, $values_map = false ) {
        $count = count( $array );

        if ( $position >= $count || $position < 0 )
            return false;

        $value = $array[$position];

        if ( !empty( $values_map ) )
            $value = $values_map[$value];

        echo $value;
    }
}

if ( !function_exists( 'get_request_values' ) ) {
    function get_request_values( $names ) {
        $values = array();
        $datas = array();

        foreach( $names as $k => $v ) {
            $values[$k] = isset( $_REQUEST[$k] ) ? $_REQUEST[$k] : '';

            if ( $v['required'] == true && $values[$k] === '' ) {
                $datas['error'] = $v['error'];
                break;
            }
        }
        $datas['values'] = $values;

        return $datas;
    }
}

if ( !function_exists( 'get_words' ) ) {
    function get_words($content) {
        $content = str_replace( " ", '', $content );
        $content = str_replace( "　", '', $content );
        $content = str_replace( "\r\n", '', $content );

        return mb_strlen( $content );
    }
}

if ( !function_exists( 'get_object_attr' ) ) {
    function get_object_attr( $object, $attr, $values_map = false ) {
        if ( empty( $object) || empty( $attr ) )
            return false;

        $value = isset ( $object->{$attr} ) ? $object->{$attr} : '';

        if ( !empty( $values_map ) && !empty( $value ) )
            $value = $values_map[$value];

        return $value;
    }
}

if ( !function_exists( 'printf_object_attr' ) ) {
    function printf_object_attr( $object, $attr, $values_map = false ) {
        echo get_object_attr( $object, $attr, $values_map );
    }
}

if ( !function_exists( 'get_array_object_attr' ) ) {
    function get_array_object_attr( $array, $position, $attr, $values_map = false ) {
        $object = get_array_value( $array, $position );
        return get_object_attr( $object, $attr, $values_map );
    }
}

if ( !function_exists( 'printf_array_object_attr' ) ) {
    function printf_array_object_attr( $array, $position, $attr, $values_map = false ) {
        echo get_array_object_attr( $array, $position, $attr, $values_map );
    }
}

if ( !function_exists( 'array2obj' ) ) {
    function array2obj( $array ) {
        if ( empty( $array ) )
            return false;

        $obj = new stdClass;
        foreach( $array as $key => $value ) {
            $obj->{$key} = $value;
        }
        return $obj;
    }
}

if ( !function_exists( 'date_after_day' ) ) {
    function date_after_day( $date, $days, $format = 'Y-m-d h:m:s' ) {
        $date_time = strtotime( $date );
        $date_time += 86400 * $days;

        return date( $format, $date_time );
    }
}

if ( !function_exists( 'date_diff_ex' ) ) {
    function date_diff_ex( $date1, $date2 ) {
        $datetime1 = new DateTime( $date1 );
        $datetime2 = new DateTime( $date2 );
        $interval = $datetime1->diff($datetime2);
        return $interval->format('%a');
    }
}

if ( !function_exists( 'get_id_num_info' ) ) {
    function get_id_num_info( $id_num ) {
        $result['error'] = 0; //0：未知错误，1：身份证格式错误，2：无错误
        $result['birthday'] ='';//生日，格式如：2012-11-15

        if( !eregi("^[1-9]([0-9a-zA-Z]{17}|[0-9a-zA-Z]{14})$", $id_num ) ){
            $result['error'] = 1;
            return $result;
        } else {
            if( strlen( $id_num ) == 18 ){
                $tyear = intval( substr( $id_num, 6, 4 ) );
                $tmonth = intval( substr( $id_num, 10, 2 ) );
                $tday = intval( substr( $id_num, 12, 2) );

                $birthday=$tyear."-".$tmonth."-".$tday." 00:00:00";
            } elseif ( strlen( $id_num ) == 15 ){
                $tyear = intval( "19" . substr( $id_num, 6, 2 ) );
                $tmonth = intval( substr( $id_num, 8, 2 ) );
                $tday = intval( substr( $id_num, 10, 2 ) );
                $birthday = $tyear . "-" . $tmonth . "-" . $tday . " 00:00:00";
            }
            $b_time = strtotime( $birthday );
            $cur_time = strtotime( 'today' );
            $diff = floor ( ( $cur_time - $b_time) / 86400 / 365 );
            $age = strtotime( $birthday . ' +' . $diff . 'years' ) > $today ? ( $diff + 1 ) : $diff;
            $gender = substr( $id_num, -2, 1) % 2 ? '1' : '0';
        }
        $result['error'] = 2;//0：未知错误，1：身份证格式错误，2：无错误
        $result['age'] = $age;//0标示成年，1标示未成年
        $result['birthday'] = $birthday;//生日日期
        $result['gender'] = $gender;

        return $result;
    }
}

if ( !function_exists( 'ajax_die' ) ) {
    function ajax_die( $status, $msg, $data = '' ) {

        $response = array(
            'status'  => $status,
            'msg'     => $msg,
            'data'    => $data );

        $response = json_encode( $response );

        status_header(200);
        die( $response );
    }
}

if ( !function_exists( 'actived' ) ) {
    function actived( $v1, $v2 = true, $type = 'active', $echo = true ) {
        if ( (string) $v1 === (string) $v2 )
            $result = "class='$type'";
        else
            $result = '';

        if ( $echo )
            echo $result;

        return $result;
    }
}

if ( !class_exists( 'ChuanglanSMS' ) ) :
    /**
     * 创蓝短信接口
     */
    class ChuanglanSMS{
        const SENDURL='http://222.73.117.138:7891/mt';
        const QUERYURL='http://222.73.117.138:7891/bi';
        const ISENDURL='http://222.73.117.140:8044/mt';
        const IQUERYURL='http://222.73.117.140:8044/bi';

        private $_sendUrl='';				// 发送短信接口url
        private $_queryBalanceUrl='';	// 查询余额接口url

        private $_un;			// 账号
        private $_pw;			// 密码

        /**
         * 构造方法
         * @param string $account  接口账号
         * @param string $password 接口密码
         */
        public function __construct($account,$password){
            $this->_un=$account;
            $this->_pw=$password;
        }

        /* ========== 业务模块 ========== */
        /**
         * 短信发送
         * @param string $phone   	手机号码
         * @param string $content 	短信内容
         * @param integer $isreport	是否需要状态报告
         * @return void
         */
        public function send($phone,$content,$isreport=0){
            $requestData=array(
                'un'=>$this->_un,
                'pw'=>$this->_pw,
                'sm'=>$content,
                'da'=>$phone,
                'rd'=>$isreport,
                'dc'=>15,
                'rf'=>2,
                'tf'=>3,
            );

            $url=ChuanglanSMS::SENDURL.'?'.http_build_query($requestData);
            return $this->_request($url);
        }

        /**
         * 国际短信发送
         * @param string $phone   	手机号码
         * @param string $content 	短信内容
         * @param integer $isreport	是否需要状态报告
         * @return void
         */
        public function sendInternational($phone,$content,$isreport=0){
            $requestData=array(
                'un'=>$this->_un,
                'pw'=>$this->_pw,
                'sm'=>$content,
                'da'=>$phone,
                'rd'=>$isreport,
                'rf'=>2,
                'tf'=>3,
            );

            $url=ChuanglanSMS::ISENDURL.'?'.http_build_query($requestData);
            return $this->_request($url);
        }

        /**
         * 查询余额
         * @return String 余额返回
         */
        public function queryBalance(){
            $requestData=array(
                'un'=>$this->_un,
                'pw'=>$this->_pw,
                'rf'=>2
            );

            $url=ChuanglanSMS::QUERYURL.'?'.http_build_query($requestData);
            return $this->_request($url);
        }

        /**
         * 查询余额
         * @return String 余额返回
         */
        public function queryBalanceInternational(){
            $requestData=array(
                'un'=>$this->_un,
                'pw'=>$this->_pw,
                'rf'=>2
            );

            $url=ChuanglanSMS::IQUERYURL.'?'.http_build_query($requestData);
            return $this->_request($url);
        }

        /* ========== 业务模块 ========== */

        /* ========== 功能模块 ========== */
        /**
         * 请求发送
         * @return string 返回状态报告
         */
        private function _request($url){
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_HEADER,0);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_URL,$url);
            $result=curl_exec($ch);
            curl_close($ch);
            return $result;
        }
        /* ========== 功能模块 ========== */
    }
endif;

if ( !class_exists( 'ChuanglanSMS' ) ) :

endif;

if ( !function_exists( 'send_sms' ) ) {
    function send_sms( $phone, $msg, $prev = '' ) {
        $sms = new ChuanglanSMS( 'N4162140', 'dd7ebdab') ;
        //发送短信
        if ( empty( $prev ) )
            $prev = "【儋州早讯】";

        return $sms->send( $phone,  $prev . $msg );
    }
}

if ( !function_exists( 'cn2py' ) ) {
    class PinYin {
        /**
         * 拼音字符转换图
         * @var array
         */
        private static $_aMaps = array(
            'a'=>-20319,'ai'=>-20317,'an'=>-20304,'ang'=>-20295,'ao'=>-20292,
            'ba'=>-20283,'bai'=>-20265,'ban'=>-20257,'bang'=>-20242,'bao'=>-20230,'bei'=>-20051,'ben'=>-20036,'beng'=>-20032,'bi'=>-20026,'bian'=>-20002,'biao'=>-19990,'bie'=>-19986,'bin'=>-19982,'bing'=>-19976,'bo'=>-19805,'bu'=>-19784,
            'ca'=>-19775,'cai'=>-19774,'can'=>-19763,'cang'=>-19756,'cao'=>-19751,'ce'=>-19746,'ceng'=>-19741,'cha'=>-19739,'chai'=>-19728,'chan'=>-19725,'chang'=>-19715,'chao'=>-19540,'che'=>-19531,'chen'=>-19525,'cheng'=>-19515,'chi'=>-19500,'chong'=>-19484,'chou'=>-19479,'chu'=>-19467,'chuai'=>-19289,'chuan'=>-19288,'chuang'=>-19281,'chui'=>-19275,'chun'=>-19270,'chuo'=>-19263,'ci'=>-19261,'cong'=>-19249,'cou'=>-19243,'cu'=>-19242,'cuan'=>-19238,'cui'=>-19235,'cun'=>-19227,'cuo'=>-19224,
            'da'=>-19218,'dai'=>-19212,'dan'=>-19038,'dang'=>-19023,'dao'=>-19018,'de'=>-19006,'deng'=>-19003,'di'=>-18996,'dian'=>-18977,'diao'=>-18961,'die'=>-18952,'ding'=>-18783,'diu'=>-18774,'dong'=>-18773,'dou'=>-18763,'du'=>-18756,'duan'=>-18741,'dui'=>-18735,'dun'=>-18731,'duo'=>-18722,
            'e'=>-18710,'en'=>-18697,'er'=>-18696,
            'fa'=>-18526,'fan'=>-18518,'fang'=>-18501,'fei'=>-18490,'fen'=>-18478,'feng'=>-18463,'fo'=>-18448,'fou'=>-18447,'fu'=>-18446,
            'ga'=>-18239,'gai'=>-18237,'gan'=>-18231,'gang'=>-18220,'gao'=>-18211,'ge'=>-18201,'gei'=>-18184,'gen'=>-18183,'geng'=>-18181,'gong'=>-18012,'gou'=>-17997,'gu'=>-17988,'gua'=>-17970,'guai'=>-17964,'guan'=>-17961,'guang'=>-17950,'gui'=>-17947,'gun'=>-17931,'guo'=>-17928,
            'ha'=>-17922,'hai'=>-17759,'han'=>-17752,'hang'=>-17733,'hao'=>-17730,'he'=>-17721,'hei'=>-17703,'hen'=>-17701,'heng'=>-17697,'hong'=>-17692,'hou'=>-17683,'hu'=>-17676,'hua'=>-17496,'huai'=>-17487,'huan'=>-17482,'huang'=>-17468,'hui'=>-17454,'hun'=>-17433,'huo'=>-17427,
            'ji'=>-17417,'jia'=>-17202,'jian'=>-17185,'jiang'=>-16983,'jiao'=>-16970,'jie'=>-16942,'jin'=>-16915,'jing'=>-16733,'jiong'=>-16708,'jiu'=>-16706,'ju'=>-16689,'juan'=>-16664,'jue'=>-16657,'jun'=>-16647,
            'ka'=>-16474,'kai'=>-16470,'kan'=>-16465,'kang'=>-16459,'kao'=>-16452,'ke'=>-16448,'ken'=>-16433,'keng'=>-16429,'kong'=>-16427,'kou'=>-16423,'ku'=>-16419,'kua'=>-16412,'kuai'=>-16407,'kuan'=>-16403,'kuang'=>-16401,'kui'=>-16393,'kun'=>-16220,'kuo'=>-16216,
            'la'=>-16212,'lai'=>-16205,'lan'=>-16202,'lang'=>-16187,'lao'=>-16180,'le'=>-16171,'lei'=>-16169,'leng'=>-16158,'li'=>-16155,'lia'=>-15959,'lian'=>-15958,'liang'=>-15944,'liao'=>-15933,'lie'=>-15920,'lin'=>-15915,'ling'=>-15903,'liu'=>-15889,'long'=>-15878,'lou'=>-15707,'lu'=>-15701,'lv'=>-15681,'luan'=>-15667,'lue'=>-15661,'lun'=>-15659,'luo'=>-15652,
            'ma'=>-15640,'mai'=>-15631,'man'=>-15625,'mang'=>-15454,'mao'=>-15448,'me'=>-15436,'mei'=>-15435,'men'=>-15419,'meng'=>-15416,'mi'=>-15408,'mian'=>-15394,'miao'=>-15385,'mie'=>-15377,'min'=>-15375,'ming'=>-15369,'miu'=>-15363,'mo'=>-15362,'mou'=>-15183,'mu'=>-15180,
            'na'=>-15165,'nai'=>-15158,'nan'=>-15153,'nang'=>-15150,'nao'=>-15149,'ne'=>-15144,'nei'=>-15143,'nen'=>-15141,'neng'=>-15140,'ni'=>-15139,'nian'=>-15128,'niang'=>-15121,'niao'=>-15119,'nie'=>-15117,'nin'=>-15110,'ning'=>-15109,'niu'=>-14941,'nong'=>-14937,'nu'=>-14933,'nv'=>-14930,'nuan'=>-14929,'nue'=>-14928,'nuo'=>-14926,
            'o'=>-14922,'ou'=>-14921,
            'pa'=>-14914,'pai'=>-14908,'pan'=>-14902,'pang'=>-14894,'pao'=>-14889,'pei'=>-14882,'pen'=>-14873,'peng'=>-14871,'pi'=>-14857,'pian'=>-14678,'piao'=>-14674,'pie'=>-14670,'pin'=>-14668,'ping'=>-14663,'po'=>-14654,'pu'=>-14645,
            'qi'=>-14630,'qia'=>-14594,'qian'=>-14429,'qiang'=>-14407,'qiao'=>-14399,'qie'=>-14384,'qin'=>-14379,'qing'=>-14368,'qiong'=>-14355,'qiu'=>-14353,'qu'=>-14345,'quan'=>-14170,'que'=>-14159,'qun'=>-14151,
            'ran'=>-14149,'rang'=>-14145,'rao'=>-14140,'re'=>-14137,'ren'=>-14135,'reng'=>-14125,'ri'=>-14123,'rong'=>-14122,'rou'=>-14112,'ru'=>-14109,'ruan'=>-14099,'rui'=>-14097,'run'=>-14094,'ruo'=>-14092,
            'sa'=>-14090,'sai'=>-14087,'san'=>-14083,'sang'=>-13917,'sao'=>-13914,'se'=>-13910,'sen'=>-13907,'seng'=>-13906,'sha'=>-13905,'shai'=>-13896,'shan'=>-13894,'shang'=>-13878,'shao'=>-13870,'she'=>-13859,'shen'=>-13847,'sheng'=>-13831,'shi'=>-13658,'shou'=>-13611,'shu'=>-13601,'shua'=>-13406,'shuai'=>-13404,'shuan'=>-13400,'shuang'=>-13398,'shui'=>-13395,'shun'=>-13391,'shuo'=>-13387,'si'=>-13383,'song'=>-13367,'sou'=>-13359,'su'=>-13356,'suan'=>-13343,'sui'=>-13340,'sun'=>-13329,'suo'=>-13326,
            'ta'=>-13318,'tai'=>-13147,'tan'=>-13138,'tang'=>-13120,'tao'=>-13107,'te'=>-13096,'teng'=>-13095,'ti'=>-13091,'tian'=>-13076,'tiao'=>-13068,'tie'=>-13063,'ting'=>-13060,'tong'=>-12888,'tou'=>-12875,'tu'=>-12871,'tuan'=>-12860,'tui'=>-12858,'tun'=>-12852,'tuo'=>-12849,
            'wa'=>-12838,'wai'=>-12831,'wan'=>-12829,'wang'=>-12812,'wei'=>-12802,'wen'=>-12607,'weng'=>-12597,'wo'=>-12594,'wu'=>-12585,
            'xi'=>-12556,'xia'=>-12359,'xian'=>-12346,'xiang'=>-12320,'xiao'=>-12300,'xie'=>-12120,'xin'=>-12099,'xing'=>-12089,'xiong'=>-12074,'xiu'=>-12067,'xu'=>-12058,'xuan'=>-12039,'xue'=>-11867,'xun'=>-11861,
            'ya'=>-11847,'yan'=>-11831,'yang'=>-11798,'yao'=>-11781,'ye'=>-11604,'yi'=>-11589,'yin'=>-11536,'ying'=>-11358,'yo'=>-11340,'yong'=>-11339,'you'=>-11324,'yu'=>-11303,'yuan'=>-11097,'yue'=>-11077,'yun'=>-11067,
            'za'=>-11055,'zai'=>-11052,'zan'=>-11045,'zang'=>-11041,'zao'=>-11038,'ze'=>-11024,'zei'=>-11020,'zen'=>-11019,'zeng'=>-11018,'zha'=>-11014,'zhai'=>-10838,'zhan'=>-10832,'zhang'=>-10815,'zhao'=>-10800,'zhe'=>-10790,'zhen'=>-10780,'zheng'=>-10764,'zhi'=>-10587,'zhong'=>-10544,'zhou'=>-10533,'zhu'=>-10519,'zhua'=>-10331,'zhuai'=>-10329,'zhuan'=>-10328,'zhuang'=>-10322,'zhui'=>-10315,'zhun'=>-10309,'zhuo'=>-10307,'zi'=>-10296,'zong'=>-10281,'zou'=>-10274,'zu'=>-10270,'zuan'=>-10262,'zui'=>-10260,'zun'=>-10256,'zuo'=>-10254
        );

        /**
         * 将中文编码成拼音
         * @param string $utf8Data utf8字符集数据
         * @param string $sRetFormat 返回格式 [head:首字母|all:全拼音]
         * @return string
         */
        public static function encode($utf8Data, $sRetFormat='all'){
            $sGBK = iconv('UTF-8', 'GBK', $utf8Data);
            $aBuf = array();
            for ($i=0, $iLoop=strlen($sGBK); $i<$iLoop; $i++) {
                $iChr = ord($sGBK{$i});
                if ($iChr>160)
                    $iChr = ($iChr<<8) + ord($sGBK{++$i}) - 65536;
                if ('head' === $sRetFormat)
                    $aBuf[] = substr(self::zh2py($iChr),0,1);
                else
                    $aBuf[] = self::zh2py($iChr);
            }
            if ('head' === $sRetFormat)
                return implode('', $aBuf);
            else
                return implode(' ', $aBuf);
        }

        /**
         * 中文转换到拼音(每次处理一个字符)
         * @param number $iWORD 待处理字符双字节
         * @return string 拼音
         */
        private static function zh2py($iWORD) {
            if($iWORD>0 && $iWORD<160 ) {
                return chr($iWORD);
            } elseif ($iWORD<-20319||$iWORD>-10247) {
                return '';
            } else {
                foreach (self::$_aMaps as $py => $code) {
                    if($code > $iWORD) break;
                    $result = $py;
                }
                return $result;
            }
        }
    }

    function cn2py( $str ) {
        return PinYin::encode( $str );
    }
}

if ( !function_exists( 'http_request' ) ) {
    global $http_header;
    $http_header = array();
    function http_get_header($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            global $http_header;

            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $http_header[$key] = $value;
        }
        return strlen($header);
    }

    function http_request( $url, $method, $postfields = NULL, $headers = array() ) {
        global $http_header;
        $http_info = array();
        $ci = curl_init();
        $useragent = 'Sae T OAuth2 v0.1';
        $connecttimeout = 30;
        $timeout = 30;
        $ssl_verifypeer = FALSE;
        $postdata = false;
        $debug = false;
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, 'http_get_header');
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        if ( isset($access_token) && $access_token )
            $headers[] = "Authorization: OAuth2 ".$access_token;

        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $http_info = array_merge($http_info, curl_getinfo($ci));
        $url = $url;

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info====='."\r\n";
            print_r( curl_getinfo($ci) );

            echo '=====$response====='."\r\n";
            print_r( $response );
        }
        curl_close ($ci);
        return $response;
    }
}

if ( !function_exists( 'get_time' ) ) {
    function get_time() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}

if ( !function_exists( 'nl2p' ) ) {
    function nl2p($string){
        return '<p>' . str_replace(array("\r\n", "\r", "\n"), '</p><p>', $string) . '</p>';
    }
}

if ( !function_exists( 'http_referer_domain_is' ) ) {
    function http_referer_domain_is( $domain ) {
        if ( !isset( $_SERVER['HTTP_REFERER'] ) )
            return false;
        return strpos( $_SERVER['HTTP_REFERER'], $domain ) === 0;
    }
}

if ( !function_exists( 'is_weixin_browser' ) ) {
    function is_weixin_browser() {
        return strpos( $_SERVER['HTTP_USER_AGENT'], 'MicroMessenger' ) !== false;
    }
}

if ( !function_exists( 'is_safari_browser' ) ) {
    function is_apple_mobile_browser() {
        return strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')
            || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')
            || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad');
    }
}

if ( !function_exists( 'wp_sanitize_redirect' ) ) {
    function wp_sanitize_redirect($location)
    {
        return $location;
    }
}

if ( !function_exists( 'friendly_time' ) ) {
    function friendly_time( $sTime, $type = 'normal',$alt = 'false' ) {
        if (!$sTime)
            return '';
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime = time();
        $dTime = $cTime - $sTime;
        $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
        //$dDay     =   intval($dTime/3600/24);
        $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
        //normal：n秒前，n分钟前，n小时前，日期
        if ($type == 'normal') {
            if ($dTime < 60) {
                if ($dTime < 10) {
                    return '刚刚';    //by yangjs
                } else {
                    return intval(floor($dTime / 10) * 10) . "秒前";
                }
            } elseif ($dTime < 3600) {
                return intval($dTime / 60) . "分钟前";
                //今天的数据.年份相同.日期相同.
            } elseif ($dYear == 0 && $dDay == 0) {
                //return intval($dTime/3600)."小时前";
                return '今天' . date('H:i', $sTime);
            } elseif ($dYear == 0) {
                return date("m月d日 H:i", $sTime);
            } else {
                return date("Y-m-d H:i", $sTime);
            }
        } elseif ($type == 'mohu') {
            if ($dTime < 60) {
                return $dTime . "秒前";
            } elseif ($dTime < 3600) {
                return intval($dTime / 60) . "分钟前";
            } elseif ($dTime >= 3600 && $dDay == 0) {
                return intval($dTime / 3600) . "小时前";
            } elseif ($dDay > 0 && $dDay <= 7) {
                return intval($dDay) . "天前";
            } elseif ($dDay > 7 && $dDay <= 30) {
                return intval($dDay / 7) . '周前';
            } elseif ($dDay > 30) {
                return intval($dDay / 30) . '个月前';
            }
            //full: Y-m-d , H:i:s
        } elseif ($type == 'full') {
            return date("Y-m-d , H:i:s", $sTime);
        } elseif ($type == 'ymd') {
            return date("Y-m-d", $sTime);
        } else {
            if ($dTime < 60) {
                return $dTime . "秒前";
            } elseif ($dTime < 3600) {
                return intval($dTime / 60) . "分钟前";
            } elseif ($dTime >= 3600 && $dDay == 0) {
                return intval($dTime / 3600) . "小时前";
            } elseif ($dYear == 0) {
                return date("Y-m-d H:i:s", $sTime);
            } else {
                return date("Y-m-d H:i:s", $sTime);
            }
        }
    }
}

if ( !function_exists( 'get_remote_ip' ) ) {
    function get_remote_ip() {

        $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['X_FORWARDED_FOR'])) {

            $X_FORWARDED_FOR = explode(',', $_SERVER['X_FORWARDED_FOR']);

            if (!empty($X_FORWARDED_FOR)) {
                $REMOTE_ADDR = trim($X_FORWARDED_FOR[0]);
            }

        }

        /*
        * Some php environments will use the $_SERVER['HTTP_X_FORWARDED_FOR']
        * variable to capture visitor address information.
        */

        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            $HTTP_X_FORWARDED_FOR= explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            if (!empty($HTTP_X_FORWARDED_FOR)) {
                $REMOTE_ADDR = trim($HTTP_X_FORWARDED_FOR[0]);
            }

        }

        return preg_replace('/[^0-9a-f:\., ]/si', '', $REMOTE_ADDR);

    }
}

if ( !function_exists( 'query_get_value' ) ) {
    function query_get_value( $url, $key ) {
        $pattern='/[?|&]' . $key . '=' . '([^&;]+?)(&|#|;|$)/';
        $matches = array();
        if ( preg_match( $pattern, $url, $matches ) ) {
            return $matches[1];
        }

        return '';
    }
}