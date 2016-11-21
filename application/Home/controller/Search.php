<?php
namespace app\Home\controller;
use think\Controller;
require "phpQuery-onefile.php";

class Search extends Controller
{
    public function search(){
        $number=array();
        if(!input('page')){
            $page=1;
            $text=input('key_word');
            session('search_info',$text);
            $ch = curl_init();
            $url='http://202.116.174.108:8080/opac/openlink.php';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $fields_string='strSearchType=title&historyCount=1&strText='.$text.'&doctype=ALL';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            $result= curl_exec($ch);
            //halt($result);
            curl_close($ch);
            preg_match('/<strong class="red">.+?\/strong>/uism', $result, $match);
            //halt($match);
            $match=$match[0];
            preg_match('/[0-9]{1,}/',$match,$number);
            //找出书名 $book_n
            $number=$number[0];
            session('search_all',$number);

            preg_match_all('/<li class="book_list_info">.+?\/li>/uism', $result, $all_book);
            $all_book=$all_book[0];
            $all_book=implode('',$all_book);                //每页书名的那部分
            //halt($all_book);
            preg_match_all('/[0-9]\.&.+?</uism', $all_book, $all_book_name);
            $all_book_name=implode('',$all_book_name[0]);
            preg_match_all('/\&.+?</uism', $all_book_name, $all_book_name);
            $all_book_name=implode('',$all_book_name[0]);
            $all_book_name=explode('<',$all_book_name); //书名
            $book_n=array();
            for($i=0;$i<=(count($all_book_name)-2);$i=$i+1){
                $all_book_name1=html_entity_decode($all_book_name[$i], ENT_NOQUOTES, 'utf-8');//必须数组
                $book_n[$i]= $all_book_name1;
            }
            //可以借的书本 $bro_num
            preg_match_all('/<br>.+?<\/span/uism', $all_book, $bro_num);
            $bro_num=implode($bro_num[0]);
            preg_match_all('/[0-9]/', $bro_num, $bro_num);//二维数组
            $bro_num=$bro_num[0];

            //作者 $array_a
            preg_match_all('/<\/span>.+?<font/uism', $all_book, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            preg_match_all('/\&.+?<font/uism', $bk_a, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            preg_match_all('/<\/span>.+?<font/uism', $bk_a, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            preg_match_all('/\&.+?<font/uism', $bk_a, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            $qian=array(" ","　","\t","\n","\r");
            $hou=array("","","","","");
            $bk_a=str_replace( $qian,$hou,$bk_a);
            $bk_a=explode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font', $bk_a);
            $bk_a=array_splice($bk_a,0,-1);
            $array_a=array();
            for($i=0;$i<=(count($bk_a)-1);$i=$i+1){
                $bk_a1=html_entity_decode($bk_a[$i], ENT_NOQUOTES, 'utf-8');//必须数组
                $array_a[$i]= $bk_a1;
                //echo $array_a[$i];
            }

            //出版社 和日期 $array_p
            preg_match_all('/<li class="book_list_info">.+?<img/uism', $all_book, $bk);
            //halt($bk);
            $bk=implode('', $bk[0]);
            preg_match_all('/<\/font> .+?<img/uism', $bk, $bk);
            $bk=implode('', $bk[0]);
            $qian=array(" ","　","\t","\n","\r");
            $hou=array("","","","","");
            $bk=str_replace( $qian,$hou,$bk);
            preg_match_all('/\&.+?<br/uism', $bk, $bk);
            //halt($bk);
            $bk=implode('', $bk[0]);
            $bk=explode('<br', $bk);
            $bk=array_splice($bk, 0,-1);
            //$bk=implode('', $bk);
            //$bk=explode('&nbsp;', $bk);
            $array_p=array();
            for($i=0;$i<=(count($bk)-1);$i=$i+1){
                $bk1=html_entity_decode($bk[$i], ENT_NOQUOTES, 'utf-8');
                $array_p[$i]= $bk1;
                //echo $array_p[$i];
            }
            //tp号，$array_tp
            preg_match_all('/<li class="book_list_info">.+?<img/uism', $all_book, $bk_tp);
            $bk_tp=implode('', $bk_tp[0]);
            preg_match_all('/<\/a.+?<\/h3/uism', $bk_tp, $bk_tp);
            $bk_tp=implode('', $bk_tp[0]);
            $bk_tp=explode('</h3', $bk_tp);
            $bk_tp=array_splice($bk_tp,0,-1);
            $bk_tp=implode('', $bk_tp);
            $bk_tp=explode('</a>', $bk_tp);
            $bk_tp=array_splice($bk_tp,1);
            $qian=array(" ","　","\t","\n","\r");
            $hou=array("","","","","");
            $bk_tp=str_replace( $qian,$hou,$bk_tp);
            $array_tp=array();
            for($i=0;$i<=(count($bk_tp)-1);$i=$i+1){
                $bk_tp1=html_entity_decode($bk_tp[$i], ENT_NOQUOTES, 'utf-8');
                $array_tp[$i]= $bk_tp1;
                //echo $array_tp[$i];
            }
            //halt($array_tp);
            $view = new \think\View();
            //判断
            $con=true;
            if($array_a==null){
                $con='false';
            }
            //鹏哥代码
            preg_match_all('/item.+?"/uism',$all_book,$href);
            $href=array_unique($href[0]);
            $href=implode('',$href);
            preg_match_all('/[0-9]{1,}"/',$href,$href_number);
            $href_number=implode('',$href_number[0]);
            $href_number=explode('"',$href_number);                      //每页书的链接的数组
            $href_number=array_filter($href_number);
            for($i=0;$i<count($array_a);$i++){
                $data[$i]['book_a']=$array_a[$i];
                $data[$i]['book_name']=$book_n[$i];
                $data[$i]['book_p']=$array_p[$i];
                $data[$i]['book_tp']=$array_tp[$i];
                $data[$i]['book_r']=$bro_num[$i];
                $data[$i]['href_num']=$href_number[$i];
                /* if($i==count($array_a)-1){
                     $data[$i+1]['page']=$page;
                 }*/
            }
            //赋值
            //halt($data);
            //halt($data);
            $page=intval($page);
            $res[0]=$page;
            $res[1]=$data;
            $res[2]=session('search_all');
            $res=json_encode($res);
            //halt($data);
            return $res;

        }
        else{
            $page=input('page');

            $ch = curl_init();
            $url='http://202.116.174.108:8080/opac/openlink.php';
            //$strText=input('strText');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie.txt');
            //curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
            //curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $fields_string='location=ALL&title='.session('search_info').'&doctype=ALL&lang_code=ALL&match_flag=forward&displaypg=20&showmode=list&orderby=DESC&sort=CATA_DATE&onlylendable=no&count='.session('search_all').'&with_ebook=&page='.$page;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            $result= curl_exec($ch);
            //dump($result);
            curl_close($ch);
            preg_match('/<strong class="red">.+?\/strong>/uism', $result, $match);
            //halt($match);
            $match=$match[0];
            preg_match('/[0-9]{1,}/',$match,$number);
            //找出书名 $book_n
            $number=$number[0];
            session('search_all',$number);

            preg_match_all('/<li class="book_list_info">.+?\/li>/uism', $result, $all_book);
            $all_book=$all_book[0];
            $all_book=implode('',$all_book);                //每页书名的那部分
            //halt($all_book);
            preg_match_all('/[0-9]\.&.+?</uism', $all_book, $all_book_name);
            $all_book_name=implode('',$all_book_name[0]);
            preg_match_all('/\&.+?</uism', $all_book_name, $all_book_name);
            $all_book_name=implode('',$all_book_name[0]);
            $all_book_name=explode('<',$all_book_name); //书名
            $book_n=array();
            for($i=0;$i<=(count($all_book_name)-2);$i=$i+1){
                $all_book_name1=html_entity_decode($all_book_name[$i], ENT_NOQUOTES, 'utf-8');//必须数组
                $book_n[$i]= $all_book_name1;
            }
            //可以借的书本 $bro_num
            preg_match_all('/<br>.+?<\/span/uism', $all_book, $bro_num);
            $bro_num=implode($bro_num[0]);
            preg_match_all('/[0-9]/', $bro_num, $bro_num);//二维数组
            $bro_num=$bro_num[0];

            //作者 $array_a
            preg_match_all('/<\/span>.+?<font/uism', $all_book, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            preg_match_all('/\&.+?<font/uism', $bk_a, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            preg_match_all('/<\/span>.+?<font/uism', $bk_a, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            preg_match_all('/\&.+?<font/uism', $bk_a, $bk_a);
            $bk_a=implode('',$bk_a[0]);
            $qian=array(" ","　","\t","\n","\r");
            $hou=array("","","","","");
            $bk_a=str_replace( $qian,$hou,$bk_a);
            $bk_a=explode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font', $bk_a);
            $bk_a=array_splice($bk_a,0,-1);
            $array_a=array();
            for($i=0;$i<=(count($bk_a)-1);$i=$i+1){
                $bk_a1=html_entity_decode($bk_a[$i], ENT_NOQUOTES, 'utf-8');//必须数组
                $array_a[$i]= $bk_a1;
                //echo $array_a[$i];
            }

            //出版社 和日期 $array_p
            preg_match_all('/<li class="book_list_info">.+?<img/uism', $all_book, $bk);
            //halt($bk);
            $bk=implode('', $bk[0]);
            preg_match_all('/<\/font> .+?<img/uism', $bk, $bk);
            $bk=implode('', $bk[0]);
            $qian=array(" ","　","\t","\n","\r");
            $hou=array("","","","","");
            $bk=str_replace( $qian,$hou,$bk);
            preg_match_all('/\&.+?<br/uism', $bk, $bk);
            //halt($bk);
            $bk=implode('', $bk[0]);
            $bk=explode('<br', $bk);
            $bk=array_splice($bk, 0,-1);
            //$bk=implode('', $bk);
            //$bk=explode('&nbsp;', $bk);
            $array_p=array();
            for($i=0;$i<=(count($bk)-1);$i=$i+1){
                $bk1=html_entity_decode($bk[$i], ENT_NOQUOTES, 'utf-8');
                $array_p[$i]= $bk1;
                //echo $array_p[$i];
            }
            //tp号，$array_tp
            preg_match_all('/<li class="book_list_info">.+?<img/uism', $all_book, $bk_tp);
            $bk_tp=implode('', $bk_tp[0]);
            preg_match_all('/<\/a.+?<\/h3/uism', $bk_tp, $bk_tp);
            $bk_tp=implode('', $bk_tp[0]);
            $bk_tp=explode('</h3', $bk_tp);
            $bk_tp=array_splice($bk_tp,0,-1);
            $bk_tp=implode('', $bk_tp);
            $bk_tp=explode('</a>', $bk_tp);
            $bk_tp=array_splice($bk_tp,1);
            $qian=array(" ","　","\t","\n","\r");
            $hou=array("","","","","");
            $bk_tp=str_replace( $qian,$hou,$bk_tp);
            $array_tp=array();
            for($i=0;$i<=(count($bk_tp)-1);$i=$i+1){
                $bk_tp1=html_entity_decode($bk_tp[$i], ENT_NOQUOTES, 'utf-8');
                $array_tp[$i]= $bk_tp1;
                //echo $array_tp[$i];
            }
            //halt($array_tp);
            $view = new \think\View();
            //判断
            $con=true;
            if($array_a==null){
                $con='false';
            }
            //鹏哥代码
            preg_match_all('/item.+?"/uism',$all_book,$href);
            $href=array_unique($href[0]);
            $href=implode('',$href);
            preg_match_all('/[0-9]{1,}"/',$href,$href_number);
            $href_number=implode('',$href_number[0]);
            $href_number=explode('"',$href_number);                      //每页书的链接的数组
            $href_number=array_filter($href_number);
            for($i=0;$i<count($array_a);$i++){
                $data[$i]['book_a']=$array_a[$i];
                $data[$i]['book_name']=$book_n[$i];
                $data[$i]['book_p']=$array_p[$i];
                $data[$i]['book_tp']=$array_tp[$i];
                $data[$i]['book_r']=$bro_num[$i];
                $data[$i]['href_num']=$href_number[$i];
            }
            //赋值

            $page=intval($page);
            $res[0]=$page;
            $res[1]=$data;
            $res[2]=session('search_all');
            $res=json_encode($res);

            return $res;
            //return $res;
        }

    }
}
