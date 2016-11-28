<?php
namespace app\Home\controller;
use think\Controller;
require "phpQuery-onefile.php";

class BookInfo extends Controller
{
    public function book_info(){
        $ch = curl_init();
        $url='http://202.116.174.108:8080/opac/item.php';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_login);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $fields_string='marc_no='.input('href_num');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $result= curl_exec($ch);
        curl_close($ch);
        //索引内容
        preg_match_all('/dd>.+?<\/dd/uism', $result, $all_book);
        //halt($all_book);
        $all_book = array_slice($all_book[0], 0,-2);
        $all_book=implode($all_book);
        preg_match_all('/\&.+?<\/dd/uism', $all_book, $all_book);
        $all_book=implode($all_book[0]);
        $all_book=explode('</dd', $all_book);
        //halt($all_book);
        $all_book=array_slice($all_book,0,-1);
        //halt($all_book);
        //print_r($all_book);
        //halt($all_book);
        //索引目录
        preg_match_all('/dt>.+?<\/dt/uism', $result, $all_book_c);
        $all_book_c=array_splice($all_book_c[0], 0,-1);
        $all_book_c=implode('', $all_book_c);
        $all_book_c=explode('dt>', $all_book_c);
        $all_book_c=array_splice($all_book_c, 1);
        $all_book_c=implode('', $all_book_c);
        $all_book_c=explode('</dt', $all_book_c);
        $all_book_c=array_splice($all_book_c, 0,-1);
        for($i=0;$i<count($all_book);$i++){
            $data[$i]['book_head']=$all_book_c[$i];
            $data[$i]['book_content']=$all_book[$i];

        }
        // print_r($data);
        $this->assign('data',$data);

        /*$this->assign('book_head',$all_book_c);
        $this->assign('book_content',$all_book);*/
        return $this->fetch('index/bookInfo');
    }
}
