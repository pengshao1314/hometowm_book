<?php
namespace app\Home\controller;
use think\Controller;
require "phpQuery-onefile.php";

class BroHistory extends Controller
{
    public function index(){

    }

    public function broHistory(){
        if(!input('page')){
            $page=1;
            $ch = curl_init();
            $url='http://202.116.174.108:8080/reader/book_hist.php';
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
            //halt($result);
            preg_match('/<h5 class="box_bgcolor"><strong>登录我的图书馆<\/strong><\/h5>/',$result,$res);
            //halt($res);
            if($res!=null){
                halt('no');
                unlink(session('cookieTxt'));
                if(session('verify2Jpg')){
                unlink(session('verify2Jpg'));}
                session(null);

                $this->error('请重新登录',url('home/index/index'));
            }
            $result=$this->getHistory($result);
            if($result==0){
                $con=false;
                $this->assign('con',$con);
                $this->assign('page','1');
                $this->assign('number','');
                return $this->fetch('index/mylib_borHistory');
            }
            $con=$result['con'];
            $data=$result['data'];
            $this->assign('con',$con);
            $this->assign('data',$data);
            $this->assign('page',$page);
            $this->assign('number',count($data));
            return $this->fetch('index/mylib_borHistory');
        }
        else{
            $page=input('page');
            $ch = curl_init();
            $url='http://202.116.174.108:8080/reader/book_hist.php?page='.input('page');
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
            //dump($result);
            curl_close($ch);
            $result=$this->getHistory($result);
            $con=$result['con'];
            $data=$result['data'];
            $this->assign('con',$con);
            $this->assign('data',$data);
            $this->assign('page',$page);
            $this->assign('number',count($data));
            return $this->fetch('index/mylib_borHistory');
        }
    }
    public function getHistory($result){
        preg_match_all('/td.+?\/td>/uism', $result, $match1);
        //条形码 $bar_num
        $bar_num=implode('',$match1[0]);
        //$book=explode('</',$book);
        //halt($bar_num);
        preg_match_all('/([A-Z][0-9]{7})/',$bar_num, $bar_num);//二维数组
        $bar_num=$bar_num[0];
       // halt($bar_num);
        if(!$bar_num){
            return 0;
        }
        //halt($bar_num);
        //书名；$book_name 循环则是$array
        $book_name=implode('',$match1[0]);
        preg_match_all('/<a class="blue".+?<\/a>/uism', $book_name, $book_name);
        $book_name=implode('',$book_name[0]);
        preg_match_all('/\&.+?<\/a>/uism', $book_name, $book_name);
        $book_name=implode('',$book_name[0]);
        $book_name=explode('</a>',$book_name);//一维数组
        $array=array();
        //echo (count($book));
        for ($i=0;$i<=(count($book_name)-2);$i=$i+1){
            $book_name1=html_entity_decode($book_name[$i], ENT_NOQUOTES, 'utf-8');
            $array[$i]= $book_name1;
            //echo $book_name1;
            //echo $i;
        }
        //print_r($array);
        //作者；$book_a；循环则是$array_a
        $book_a=implode('',$match1[0]);
        preg_match_all('/width="15%">\&.+?<\/td>/uism', $book_a, $book_a);
        $book_a=implode('',$book_a[0]);
        preg_match_all('/\&.+?<\/td>/uism', $book_a, $book_a);
        $book_a=implode('',$book_a[0]);
        $book_a=explode('</td>',$book_a);
        $array_a=array();
        //echo (count($book_a));
        for ($i=0;$i<=(count($book_a)-2);$i=$i+1){
            $book_a1=html_entity_decode($book_a[$i], ENT_NOQUOTES, 'utf-8');
            $array[$i]= $book_a1;
            //echo $book_a1;
            // echo $i;
        }
        //print_r($array);
        //借阅日期 date_bro 奇数偶数,volist输出奇数是借书日期，偶数是还书日期
        $date_bro=implode('',$match1[0]);
        preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $date_bro, $date_bro);
        $date_bro=$date_bro[0];
        //书的地址
        $book_place=implode('',$match1[0]);
        preg_match_all('/width="15%">.+?\/td>/uism',$book_place, $book_place);
        $book_place=implode('',$book_place[0]);
        preg_match_all('/[\x{4e00}-\x{9fa5}]+/u',$book_place, $book_place);
        $book_place=$book_place[0];
        //halt($book_place);
        $view = new \think\View();
        //判断
        $con=true;
        //halt($result);
        if($bar_num==null){
            $con=false;
        }

        for($i=0;$i<count($date_bro);$i++){
            if($i%2==0){
                $date_begin[$i/2]=$date_bro[$i];
            }
            else{
                $date_end[($i+1)/2-1]=$date_bro[$i];
            }
        }
        $book_name=array_filter($book_name);
        $book_a=array_filter($book_a);
        //halt($bar_num);
        for($i=0;$i<count($date_begin);$i++){
            $data[$i]['date_begin']=$date_begin[$i];
            $data[$i]['date_end']=$date_end[$i];
            $data[$i]['bar_num']=$bar_num[$i];
            $data[$i]['book_name']=$book_name[$i];
            $data[$i]['book_a']=$book_a[$i];
            $data[$i]['book_place']=$book_place[$i];
        }
        return array('data'=>$data ,'con'=>$con);
    }
}
