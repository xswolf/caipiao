<?php
namespace Admin\Controller;
use Admin\Logic\Lottery;
use Think\Controller;
class IndexController extends Controller {
    public function index(){

        $lottery = new Lottery();
        $lotteryNum = ["lottery_num_1"=>2,"lottery_num_2"=>1,"lottery_num_3"=>9]; //测试号
        $lottery->add($lotteryNum);
    }


    public function lists(){

    }
}