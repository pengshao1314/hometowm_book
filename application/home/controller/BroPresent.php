<?php
namespace app\Home\controller;
use think\Controller;
require "phpQuery-onefile.php";

class BroPresent extends Controller
{

    public function broPresent(){

        $ch = curl_init();
        $url='http://202.116.174.108:8080/reader/book_lst.php';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE,session('cookieTxt'));
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
        curl_setopt($ch, CURLOPT_COOKIEJAR, session('cookieTxt'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result= curl_exec($ch);
        curl_close($ch);
        //$res=curl_getinfo($ch);
      //halt($result);
       // halt($result);
        preg_match('/<h5 class="box_bgcolor"><strong>登录我的图书馆<\/strong><\/h5>/',$result,$res);
        if($res!=null){
            unlink(session('cookieTxt'));
            if(session('verify2Jpg')){
            unlink(session('verify2Jpg'));}
            session(null);
            $this->error('请重新登录',url('home/index/index'));
        }
        preg_match_all('/td.+?\/td>/uism', $result, $match1);
        $view = new \think\View();
//halt($match1);
        $renew=implode('',$match1[0]);
        preg_match_all('/getInLib.+?\)/',$renew,$check);
        $check=$check[0];
       // halt($check);
        $match1=implode('',$match1[0]);

        preg_match_all('/<\/a>.+?<\/td>/uism', $match1,$book_a);//二维数组
        $book_a=implode('',$book_a[0]);
        $book_a=explode('</a> /',$book_a);
        $book_a=implode('',$book_a);
        //halt($book_a);
        $book_a=explode('</td>',$book_a);
        $book_a=array_filter($book_a);

        $array_a=array();

        for ($i=0;$i<=(count($book_a)-1);$i=$i+1){
            $book1=html_entity_decode($book_a[$i], ENT_NOQUOTES, 'utf-8');
            $array_a[$i]= $book1;
        }

        preg_match_all('/<a class="blue".+?<\/a>/uism', $match1,$book);//二维数组

        $book=implode('',$book[0]);

        preg_match_all('/\&.+?<\/a>/uism', $book,$book);//二维数组

        $book=implode('',$book[0]);

        $book=explode('</a>',$book);

        $book=array_filter($book);

        $array=array();
        for($i=0;$i<=(count($book)-1);$i=$i+1){
            $book1=html_entity_decode($book[$i], ENT_NOQUOTES, 'utf-8');
            $array[$i]= $book1;
            //echo $i;
            //echo $array[$i];
        }
        //($array);
        //借书时间和还书时间
        preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $match1, $date_bro);
        $date_bro=$date_bro[0];
        //$date_bro=array_unique($date_bro[0]);
        //halt($date_bro);
        //藏书地
        preg_match_all('/width="15%">.+?\/td>/uism',$match1, $book_place);
        $book_place=implode('',$book_place[0]);
        preg_match_all('/[\x{4e00}-\x{9fa5}]+/u',$book_place, $book_place);
        $book_place=$book_place[0];
        //halt($book_place);
        //书号 $bar_num
        preg_match_all('/(A[0-9]{7})/',$match1, $bar_num);//二维数组
        $bar_num=$bar_num[0];
        //halt($bar_num);
        $view = new \think\View();
        //判断

        $con=true;
        if($bar_num==null){
            $con=false;
        }
        $bar_num=array_unique($bar_num);
        $bar_num=implode('<',$bar_num);
        $bar_num=explode('<',$bar_num);

        for($i=0;$i<count($date_bro);$i++){
            if($i%2==0){
                $date_begin[$i/2]=$date_bro[$i];
            }
            else{
                $date_end[($i+1)/2-1]=$date_bro[$i];
            }
        }
        $data=array();
        for($i=0;$i<count($book);$i++){
            $data[$i]['bar_num']=$bar_num[$i];
            $data[$i]['book']=$book[$i];
            $data[$i]['book_a']=$book_a[$i];
            $data[$i]['date_begin']=$date_begin[$i];
            $data[$i]['date_end']=$date_end[$i];
            $data[$i]['book_place']=$book_place[$i];
            $data[$i]['method']=$check[$i];

        }

        $this->assign('con',$con);

        if(session('verify2Jpg')){
            unlink(session('verify2Jpg'));
            session('verify2Jpg',null);
        }
        //赋值

        if($data!=null){
           // halt('no');
            $dirCode = 'public/curl/'.uniqid()."verify2.jpg";//图片文件路径';
            //$url = 'http://202.116.174.108:8080/reader/login.php';//注册页url
            $imgUrl = 'http://202.116.174.108:8080/reader/captcha.php';//验证码url
            ///$cookie=$this->get_cookie($url);
            //$this->getCookie($url, $this->$cookie);//访问一遍注册页面获取到cookie 保存
            session('verify2Jpg',$dirCode);
            $this->getImg($imgUrl, $dirCode);//获取图片验证码保存在本地
            $this->assign('pic',$dirCode);
            $this->assign('data',$data);

        }
        else {
           // $this->assign('pic',false);
        }

        /* $this->assign('bar_num',$bar_num);
         $this->assign('book',$book);
         //$this->assign('array',$array);//备用循环出书名
         $this->assign('book_a',$book_a);
         //$this->assign('array_a',$array_a);//备用循环出作者
         $this->assign('date_bro',$date_bro);
         $this->assign('book_place',$book_place);*/
        return $this->fetch('index/myLib_borrow');


    }
    public function reNew(){
        $ch = curl_init();
        $url='http://202.116.174.108:8080/reader/ajax_renew.php';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE,session('cookieTxt'));
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, true);
        $bar_code=input('bar_code');
        $check=input('check');
        $captcha=input('captcha');
        $time=input('time');
        $fields_string='bar_code='.$bar_code.'&check='.$check.'&captcha='.$captcha.'&time='.$time;
        // halt($fields_string);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
        curl_setopt($ch, CURLOPT_COOKIEJAR, session('cookieTxt'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result= curl_exec($ch);
        curl_close($ch);
      //  unlink(session('verify2Jpg'));
        preg_match('/<h5 class="box_bgcolor"><strong>登录我的图书馆<\/strong><\/h5>/',$result,$res);
        if($res!=null){
            unlink(session('cookieTxt'));
            if(session('verify2Jpg')){
            unlink(session('verify2Jpg'));}
            session(null);
            $this->error('请重新登录',url('home/index/index'));
        }
       // return 'ok';
        return $result;

    }

    public function getImg($imgUrl, $img)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imgUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_COOKIEFILE,session('cookieTxt'));
        //curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, session('cookieTxt'));
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
}
