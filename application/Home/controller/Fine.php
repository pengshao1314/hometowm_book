<?php
namespace app\Home\controller;
use think\Controller;
require "phpQuery-onefile.php";

class Fine extends Controller
{
    public function fine(){
        $ch = curl_init();
        $url='http://202.116.174.108:8080/reader/fine_pec.php';
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
        //取出所有标;
        preg_match('/<h5 class="box_bgcolor"><strong>登录我的图书馆<\/strong><\/h5>/',$result,$res);
        //halt($result);
        if($res!=null){
            
            unlink(session('cookieTxt'));
            if(session('verify2Jpg')){
            unlink(session('verify2Jpg'));}
            session(null);

            $this->error('请重新登录',url('home/index/index'));
        }
        preg_match_all('/td.+?\/td>/uism', $result, $match1);
        //halt($match1);

        $match1=implode('',$match1[0]);
        //条码号
        preg_match_all('/(A[0-9]{7})/', $match1, $bar_num);
        $bar_num=array_unique($bar_num[0]);//由于有链接，链接里面可能包含相同东西，所以去掉相同的
        if(!$bar_num){
            $this->assign('con',false);
            return $this->fetch('index/fine');
        }
        //halt($bar_num);
        //已经用循环输出
        //书名和作者 $array
        preg_match_all('/\&.+?<\//uism', $match1,$book);//二维数组

        $book=implode('',$book[0]);
        $book=explode('</',$book);
        // print_r($book);
        //halt($book);

        $array_tp=array();
        $array_name=array();
        $array_a=array();
        //halt(count($book));
        for($i=0;$i<=(count($book)-1);$i=$i+3){
            $book1=html_entity_decode($book[$i], ENT_NOQUOTES, 'utf-8');//必须数组
            //halt($book);
            $array_tp[$i]= $book1;
            //echo $i;
            //echo $array_tp[$i];
        }

        for($i=1;$i<=(count($book)-1);$i=$i+3){
            $book2=html_entity_decode($book[$i], ENT_NOQUOTES, 'utf-8');//必须数组
            //halt($book);
            $array_name[$i]= $book2;
            //echo $i;
            // echo $array_name[$i];
        }

        for($i=2;$i<=(count($book)-1);$i=$i+3){
            $book3=html_entity_decode($book[$i], ENT_NOQUOTES, 'utf-8');//必须数组
            //halt($book);
            $array_a[$i]= $book3;
            //echo $i;
            // echo $array_a[$i];
        }
        $book_name=implode('<',$array_name);
        $book_name=explode('<',$book_name);
        $book_a=implode('<',$array_a);
        $book_a=explode('<',$book_a);
        $book_tp=implode('<',$array_tp);
        $book_tp=explode('<',$book_tp);
        //借书时间和还书时间
        preg_match_all('^\d{4}\-\d{1,2}-\d{1,2}^', $match1, $date_bro);
        $date_bro=$date_bro[0];
        //$date_bro=array_unique($date_bro[0]);
        //halt($date_bro);
        //藏书地和处理状态；volist数组从10开始，10是地点，11是处理状态，单双
        preg_match_all('/[\x{4e00}-\x{9fa5}]+/u',$match1, $book_place);
        $book_place=$book_place[0];
        $book_place = array_slice($book_place, 10);
        //halt($book_place);
        //应该和实际缴费 volist循环输出
        // halt($match1);
        preg_match_all('/right.+?</',$match1, $book_money);
        $book_money=implode("",$book_money[0]);
        //halt($book_money);
        preg_match_all('/[0-9].+?</',$book_money, $book_money);
        //$book_money=$book_money[0];
        $book_money=implode('',$book_money[0]);
        $book_money=explode('<',$book_money);
        $book_money=array_filter($book_money);
        for($i=0;$i<count($book_money);$i++){
            if($i%2==0){
                $fine_s[$i/2]=$book_money[$i];
            }
            else{
                $fine_r[($i+1)/2-1]=$book_money[$i];
            }
        }

        for($i=0;$i<count($date_bro);$i++){
            if($i%2==0){
                $date_begin[$i/2]=$date_bro[$i];
            }
            else{
                $date_end[($i+1)/2-1]=$date_bro[$i];
            }
        }
        for($i=0;$i<count($book_place);$i++){
            if($i%2==0){
                $book_p[$i/2]=$book_place[$i];
            }
            else{
                $book_status[($i+1)/2-1]=$book_place[$i];
            }
        }
        //判断
        $view = new \think\View();
        $con=true;
        if($bar_num==null){
            $con=false;
        }
        for($i=0;$i<count($bar_num);$i++){
            $data[$i]['bar_num']=$bar_num[$i];
            $data[$i]['book_tp']=$book_tp[$i];
            $data[$i]['book_name']=$book_name[$i];
            $data[$i]['book_a']=$book_a[$i];
            $data[$i]['date_begin']=$date_begin[$i];
            $data[$i]['date_end']=$date_end[$i];
            $data[$i]['book_p']=$book_p[$i];
            $data[$i]['fine_s']=$fine_s[$i];
            $data[$i]['fine_r']=$fine_r[$i];
            $data[$i]['book_status']=$book_status[$i];

        }
        $this->assign('con',$con);
        $this->assign('data',$data);
        return $this->fetch('Index/fine');
    }
}