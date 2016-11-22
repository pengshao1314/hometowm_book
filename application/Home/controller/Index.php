<?php
namespace app\Home\controller;
use think\Controller;
require "phpQuery-onefile.php";

class Index extends Controller
{
    private $cookie;
    private  $cookieVerify;


    public function index(){
        /*session(null);
        exit;*/
        if(session('cookieTxt')&&session('verifyJpg')){
            unlink(session('cookieTxt'));
            unlink(session('verifyJpg'));
        }
        $dirCode='public/curl/'.uniqid().'verify.jpg';
        $cookieTxt='public/curl/'.uniqid().'cookie.txt';
       // $dirCode = 'public/curl/'.uniqid()."verify.jpg";//图片文件路径';
        $url = 'http://202.116.174.108:8080/reader/login.php';//注册页url
        $imgUrl = 'http://202.116.174.108:8080/reader/captcha.php';//验证码url
        $cookie=$this->get_cookie($url,$cookieTxt);
        session('cookieTxt',$cookieTxt);
        session('verifyJpg',$dirCode);
        $this->getImg($imgUrl, $dirCode,$cookie,$cookieTxt);//获取图片验证码保存在本地
        if(cookie('user')!=null){
        $this->assign('number',cookie('user'));
        $this->assign('passwd',cookie('pass'));
        }
        else{
            $this->assign('number','');
            $this->assign('passwd','');
        }
        $this->assign('pic',$dirCode);
        return $this->fetch('Index/index');
        //跳转登录页
    }
    /**
     * 方法名 getImg
     * 参数 图片的URL、保存图片信息的路径、保存cookie的文件路径
     * 功能 访问验证码图片并报存在本地供提取验证码
     * 返回值 无（仅将内容保存在文件当中）
     */
    public function getImg($imgUrl, $img,$cookie,$cookieTxt)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imgUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR,$cookieTxt);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieVerify);
        $rs = curl_exec($ch);
        // preg_match('/Set-Cookie:(.*);/iU',$rs,$str); //这里采用正则匹配来获取cookie并且保存它到变量$str里，这就是为什么上面可以发送cookie变量的原因
        // halt($str);
        //$cookie = $str[1];
        // 把验证码在本地生成，二次拉取验证码可能无法通过验证
        @file_put_contents("$img", $rs);
        curl_close($ch);
        //return $cookie;
        // halt($rs);
    }

    /**
     * 方法名 getCookie
     * 参数 页面URL、保存cookie的文件路径
     * 功能 访问页面获取cookie的值
     * 返回值 无（仅将内容保存在定义文件中）
     */
    public function get_cookie($url,$cookieTxt){

        $ch = curl_init($url);//这里是初始化一个访问对话，并且传入url，这要个必须有
        curl_setopt($ch, CURLOPT_HEADER,1);//如果你想把一个头包含在输出中，设置这个选项为一个非零值，我这里是要输出，所以为 1
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。设置为0是直接输出
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//设置跟踪页面的跳转，有时候你打开一个链接，在它内部又会跳到另外一个，就是这样理解
        curl_setopt($ch,CURLOPT_POST,1);//开启post数据的功能，这个是为了在访问链接的同时向网页发送数据，一般数urlencode码
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieTxt);//获取的cookie 保存到指定的 文件路径，我这里是相对路径，可以是$变量

        $content=curl_exec($ch);     //重点来了，上面的众多设置都是为了这个，进行url访问，带着上面的所有设置
      //  halt($content);
        if(curl_errno($ch)){
            echo 'Curl error: '.curl_error($ch);exit(); //这里是设置个错误信息的反馈
        }
        if($content==false){
            echo "get_content_null";exit();
        }

        preg_match('/Set-Cookie:(.*);/iU',$content,$str); //这里采用正则匹配来获取cookie并且保存它到变量$str里，这就是为什么上面可以发送cookie变量的原因
        $cookie = $str[1]; //获得COOKIE（SESSIONID）
        halt($content);
        curl_close($ch);//关闭会话
        //halt($cookie);
        return     $cookie;//返回cookie

    }



    public function login(){
        //接口登陆
        if(request()->isPost()){
            //Header("Content-Type: image/jpeg");
            //判读密码与用户名
            $ch = curl_init();
            // 用户名\密码
            $user = input('post.number');
            $pass = input('post.passwd');
            cookie('user',$user,30*12*24*3600);
            cookie('pass',$pass,30*12*24*3600);
            $verify =input('post.rand');
            // $loginType=input('post.loginType');
            //$url = "http://202.116.174.108:8080/reader/redr_verify.php";
            $url='http://202.116.174.108:8080/reader/redr_verify.php';
            // 返回结果存放在变量中，不输出
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_COOKIEFILE,session('cookieTxt'));
            //curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_POST, true);
            /*$fields_post = array("name"=> $user, "userType"=>1,"passwd"=> $pass, "loginType"=>$loginType,"rand"=>$verify,"imageField.x"=>19,"imageField.y"=>7);
            $headers_login = array("Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36
");
            $fields_string = "";
            foreach($fields_post as $key => $value){
                $fields_string .= $key . "=" . $value . "&";
            }
            $fields_string = rtrim($fields_string , "&");*/

            $fields_string= 'number='.$user.'&passwd='.$pass.'&captcha='.$verify.'&select=cert_no&returnUrl=';
            // halt($fields_string);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
            curl_setopt($ch, CURLOPT_COOKIEJAR, session('cookieTxt'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result= curl_exec($ch);
            curl_close($ch);
            unlink(session('verifyJpg'));
            session('verifyJpg',null);
            preg_match('/<h5 class="box_bgcolor"><strong>登录我的图书馆<\/strong><\/h5>/',$result,$res);
            if($res!=null){
                unlink(session('cookieTxt'));
                session(null);
                $this->error('请重新登录',url('home/index/index'));
            }
            //halt($result);
            //办证日期
            preg_match_all('/tr>.+?\/tr>/uism', $result, $match1);//抓取tr标签
            preg_match_all('/td>.+?\/td>/uism', $match1[0][0], $match);//抓取td标签
            preg_match('/\/span>.+?td>/uism',$match[0][0],$car_do);

            if($car_do==null){
                unlink(session('cookieTxt'));
                session('cookieTxt',null);
                $this->error('登陆失败',url('home/index/index'));
            }
            preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $car_do[0], $car_do);//一个二维数组00
            preg_match_all('/td.+?\/td>/uism', $result, $match1);
            preg_match('/\/span>.+?td>/uism',$match1[0][1],$name);
            preg_match('/[\x{4e00}-\x{9fa5}]+/u',$name[0],$name1);
            //抓取学号
            preg_match_all('/\/span>.+?td>/uism',$match1[0][2],$name_num);
            preg_match_all('^2\d{11}^',$name_num[0][0],$name_num1);
            $name_num1=$name_num1[0][0];
            //halt($name_num1);//字符串



            //抓取失效日期
            preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $match1[0][4], $time_over);//00
            $time_over=$time_over[0][0];
            //halt($time_over);
            //抓取办证日期日期
            preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $match1[0][5], $time_cteate);//00
            $time_cteate=$time_cteate[0][0];
            //halt($time_cteate);
            //抓取生效日期日期
            preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $match1[0][6], $time_can);//00
            $time_can=$time_can[0][0];
            //halt($time_can);
            //日期转换
            $startdate=strtotime($time_cteate[0][0]);
            //halt($startdate);//函数没问题，可以转换
            $enddate=strtotime($time_over[0][0]);    //上面的php时间日期函数已经把日期变成了时间戳，就是变成了秒。这样只要让两数值相减，然后把秒变成天就可以了，比较的简单，如下：
            $days=round(($enddate-$startdate)/3600/24) ;
            //halt($days);     //days为得到的天数;
            //读者类型
            preg_match_all('/\/span>.+?td>/uism',$match1[0][10],$student);
            //halt($student);//二维数组，00
            preg_match('/[\x{4e00}-\x{9fa5}]+/u',$student[0][0],$student);
            $student=$student[0];
            //halt($student);//一维数组
            //可预约图书数
            preg_match_all('^[1-9]\d*^', $match1[0][8], $book_book);//00
            $book_book=$book_book[0][0];
            //halt($book_book);//二维数组，00
            //历史借阅书总量
            preg_match_all('^[1-9]\d*^', $match1[0][12], $bro_count);//00
            if(!$bro_count[0]){
                $bro_count=0;
            }
            else $bro_count=$bro_count[0][0];
          // halt($bro_count);
            //halt($bro_count);
            //欠款
            preg_match_all('^[0-9]\d*^', $match1[0][14], $fine);//00
            $fine=$fine[0][0];
            //halt($fine);
            //专业
            preg_match_all('/\/span>.+?td>/uism',$match1[0][19],$college);
            //halt($college);
            preg_match_all('/[\x{4e00}-\x{9fa5}]+/u',$college[0][0],$college);
            $college=$college[0][0];
            //halt($college);
            //变量赋值
            $view = new \think\View();

            // halt($name_num1[0][0]);
            session('name',$name1[0]);
            session('num',$name_num1);
            session('student',$student);
            session('time_cteate',$time_cteate);
            session('time_can',$time_can);
            session('time_over',$time_over);
            session('fine',$fine);
            session('bro_count',$bro_count);
            session('college',$college);
            $this->assign('name',$name1[0]);
            $this->assign('num',$name_num1);
            $this->assign('student',$student);
            $this->assign('time_cteate',$time_cteate);
            $this->assign('time_can',$time_can);
            $this->assign('time_over',$time_over);
            $this->assign('fine',$fine);
            $this->assign('bro_count',$bro_count);
            $this->assign('college',$college);
            return $this->fetch('mylib_info');

            //$match=array();
            // preg_match('/<div[^>]*id="mylib_info"[^>]*>(.*?) <\/div>/si',$result,$match);
        }
        else{
            //halt(session('num'));
            $this->assign('college',session('college'));
            $this->assign('name',session('name'));
            $this->assign('num',session('num'));
            $this->assign('student',session('student'));
            $this->assign('time_cteate',session('time_cteate'));
            $this->assign('time_can',session('time_can'));
            $this->assign('time_over',session('time_over'));
            $this->assign('fine',session('fine'));
            $this->assign('bro_count',session('bro_count'));
            return $this->fetch('mylib_info');
        }
    }


    public function modifyPass(){
        $ch = curl_init();
        // 用户名\密码
        $old_passwd = input('old_passwd');
        $new_passwd = input('new_passwd');
        $chk_passwd = input('chk_passwd');
        $submit1 ='确定';
        if($new_passwd!=$chk_passwd){
            return '新密码与确认密码不一致';
        }
        $url='http://202.116.174.108:8080/reader/change_passwd_result.php';
        // 返回结果存放在变量中，不输出
        curl_setopt($ch, CURLOPT_URL, $url);
        $referer='http://202.116.174.108:8080/reader/change_passwd.php';
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $up='[201430350424]:['.$old_passwd.']';
        curl_setopt($ch,CURLOPT_USERPWD,$up);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE,session('cookieTxt'));
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, true);
        $fields_string= 'old_passwd='.$old_passwd.'&new_passwd='.$new_passwd.'&chk_passwd='.$chk_passwd.'&submit1='.$submit1;
        // halt($fields_string);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
        curl_setopt($ch, CURLOPT_COOKIEJAR, session('cookieTxt'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result= curl_exec($ch);
        curl_close($ch);
        preg_match('/<h5 class="box_bgcolor"><strong>登录我的图书馆<\/strong><\/h5>/',$result,$res);
        if($res!=null){
            unlink(session('cookieTxt'));
            unlink(session('verify2Jpg'));
            session('cookieTxt',null);
            $this->error('请重新登录',url('home/index/index'));
        }

        if(preg_match('/您的密码修改成功,请重新登录/',$result,$data)){
            unlink(session('cookieTxt'));
            session(null);
            return $data[0];
        }
       // preg_match('/<strong class="iconerr">.+?</strong>/',$result,$data);
        //preg_match('/[\u4e00-\u9fa5]/',$data[0],$data);
        else if (preg_match('/旧密码输入错误/',$result,$data)){
            return $data[0];
        }
        else
            return "服务器出错";
        //return $result;
    }
    public function logout(){
        unlink(session('cookieTxt'));
        if(session('verify2Jpg')){
        unlink(session('verify2Jpg'));}
        session(null);
        $this->success('退出成功',url('home/index/index'));
    }
}





