<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/2
 * Time: 15:00
 */

namespace Admin\Logic;


class Lottery {

    protected $lottery;
    protected $inputLottery;

    /**
     * 获取当前期的彩票数据
     */
    public function getCurrentStage() {
        $lottery = M( 'lottery_num l' )->order( "l.id desc" )->find();

        $trend = M( "lottery_num_trend" )->where( [ 'lottery_num_id' => $lottery['id'] ] )->select();

        $distance = M( "lottery_num_distance" )->where( [ 'lottery_num_id' => $lottery['id'] ] )->find();

        $this->lottery['lottery']  = $lottery;
        $this->lottery['distance'] = $distance; // 距离走势

        foreach ( $trend as $v ) {     // 指标走势和距离指标走势
            if ( $v['type_id'] == 1 ) {
                $this->lottery['trend'] = $v;
            } else {
                $this->lottery['distance_trend'] = $v;
            }
        }


        return $this->lottery;
    }

    /**
     * 设置数字走势
     */
    public function setLottery() {
        for ( $i = 0 ; $i < 10 ; $i ++ ) {
            $num1 = $this->lottery['lottery'][ 'lottery_num_1_trend_' . $i ] == - 1 ? 0 : $this->lottery['lottery'][ 'lottery_num_1_trend_' . $i ];
            $num2 = $this->lottery['lottery'][ 'lottery_num_2_trend_' . $i ] == - 1 ? 0 : $this->lottery['lottery'][ 'lottery_num_2_trend_' . $i ];
            $num3 = $this->lottery['lottery'][ 'lottery_num_3_trend_' . $i ] == - 1 ? 0 : $this->lottery['lottery'][ 'lottery_num_3_trend_' . $i ];

            $trend[ 'lottery_num_1_trend_' . $i ] = $num1 + 1;
            $trend[ 'lottery_num_2_trend_' . $i ] = $num2 + 1;
            $trend[ 'lottery_num_3_trend_' . $i ] = $num3 + 1;
        }
        $trend[ 'lottery_num_1_trend_' . $this->inputLottery['lottery_num_1'] ] = - 1;
        $trend[ 'lottery_num_2_trend_' . $this->inputLottery['lottery_num_2'] ] = - 1;
        $trend[ 'lottery_num_3_trend_' . $this->inputLottery['lottery_num_3'] ] = - 1;
        $trend                                                                  = array_merge( $this->inputLottery , $trend );

        //        var_dump( $trend );

        return $trend;
    }

    /**
     * 设置距离走势
     */
    public function setDistance() {
        $this->lottery['distance'];
        $distance['num_1_distance'] = abs( $this->inputLottery['lottery_num_1'] - $this->lottery['lottery']['lottery_num_1'] );
        $distance['num_2_distance'] = abs( $this->inputLottery['lottery_num_2'] - $this->lottery['lottery']['lottery_num_2'] );
        $distance['num_3_distance'] = abs( $this->inputLottery['lottery_num_3'] - $this->lottery['lottery']['lottery_num_3'] );
        for ( $i = 0 ; $i < 10 ; $i ++ ) {
            $num1 = $this->lottery['distance'][ 'num_1_num_' . $i ] == - 1 ? 0 : $this->lottery['distance'][ 'num_1_num_' . $i ];
            $num2 = $this->lottery['distance'][ 'num_2_num_' . $i ] == - 1 ? 0 : $this->lottery['distance'][ 'num_2_num_' . $i ];
            $num3 = $this->lottery['distance'][ 'num_3_num_' . $i ] == - 1 ? 0 : $this->lottery['distance'][ 'num_3_num_' . $i ];

            $distance[ 'num_1_num_' . $i ] = $num1 + 1;
            $distance[ 'num_2_num_' . $i ] = $num2 + 1;
            $distance[ 'num_3_num_' . $i ] = $num3 + 1;
        }
        $distance[ 'num_1_num_' . $distance['num_1_distance'] ] = - 1;
        $distance[ 'num_2_num_' . $distance['num_2_distance'] ] = - 1;
        $distance[ 'num_3_num_' . $distance['num_3_distance'] ] = - 1;

        //        var_dump( $distance );

        return $distance;
    }

    /**
     * @param $lottery
     * @param int $typeId
     * 设置距离趋势
     *
     * @return mixed
     */
    public function setTrend( $lottery , $typeId = 1 ) {

        for ( $i = 1 ; $i < 4 ; $i ++ ) {

            if ( $typeId == 1 ) {
                $trend['type_id'] = 1;
                $num              = $lottery["lottery_num_{$i}"];
            } else {
                $trend['type_id'] = 2;
                $num              = $lottery["num_{$i}_distance"];
            }

            if ( $this->isEven( $num ) ) {  // 判断奇偶数
                $trend["num_{$i}_even"] = - 1;
                $trend["num_{$i}_odd"]  = $this->processNum( $this->lottery['trend']["num_{$i}_odd"] );
            } else {
                $trend["num_{$i}_odd"]  = - 1;
                $trend["num_{$i}_even"] = $this->processNum( $this->lottery['trend']["num_{$i}_even"] );
            }

            if ( $this->isBig( $num ) ) {  // 判断大小
                $trend["num_{$i}_big"]   = - 1;
                $trend["num_{$i}_small"] = $this->processNum( $this->lottery['trend']["num_{$i}_small"] );
            } else {
                $trend["num_{$i}_small"] = - 1;
                $trend["num_{$i}_big"]   = $this->processNum( $this->lottery['trend']["num_{$i}_big"] );
            }

            if ( $this->isPrime( $num ) ) {  // 判断质合数
                $trend["num_{$i}_prime"]     = - 1;
                $trend["num_{$i}_composite"] = $this->processNum( $this->lottery['trend']["num_{$i}_composite"] );
            } else {
                $trend["num_{$i}_composite"] = - 1;
                $trend["num_{$i}_prime"]     = $this->processNum( $this->lottery['trend']["num_{$i}_prime"] );
            }

            if ( $this->getSMB( $num ) == 1 ) {// 判断数字是小中大哪一种
                $trend["num_{$i}_small_1"]  = - 1;
                $trend["num_{$i}_middle_1"] = $this->processNum( $this->lottery['trend']["num_{$i}_middle_1"] );
                $trend["num_{$i}_big_1"]    = $this->processNum( $this->lottery['trend']["num_{$i}_big_1"] );
            } elseif ( $this->getSMB( $num ) == 2 ) {
                $trend["num_{$i}_small_1"]  = $this->processNum( $this->lottery['trend']["num_{$i}_small_1"] );
                $trend["num_{$i}_middle_1"] = - 1;
                $trend["num_{$i}_big_1"]    = $this->processNum( $this->lottery['trend']["num_{$i}_big_1"] );
            } else {
                $trend["num_{$i}_small_1"]  = $this->processNum( $this->lottery['trend']["num_{$i}_small_1"] );
                $trend["num_{$i}_middle_1"] = $this->processNum( $this->lottery['trend']["num_{$i}_middle_1"] );
                $trend["num_{$i}_big_1"]    = - 1;
            }

            if ( $this->getRemainder( $num ) == 0 ) {// 3求余数
                $trend["num_{$i}_num_0"] = - 1;
                $trend["num_{$i}_num_1"] = $this->processNum( $this->lottery['trend']["num_{$i}_num_1"] );
                $trend["num_{$i}_num_2"] = $this->processNum( $this->lottery['trend']["num_{$i}_num_2"] );
            } elseif ( $this->getSMB( $num ) == 1 ) {
                $trend["num_{$i}_num_0"] = $this->processNum( $this->lottery['trend']["num_{$i}_num_0"] );
                $trend["num_{$i}_num_1"] = - 1;
                $trend["num_{$i}_num_2"] = $this->processNum( $this->lottery['trend']["num_{$i}_num_2"] );
            } else {
                $trend["num_{$i}_num_0"] = $this->processNum( $this->lottery['trend']["num_{$i}_num_0"] );
                $trend["num_{$i}_num_1"] = $this->processNum( $this->lottery['trend']["num_{$i}_num_1"] );
                $trend["num_{$i}_num_2"] = - 1;
            }

        }

        //        var_dump( $trend );
        return $trend;
    }

    public function add( $lotteryNum ) {
        $this->inputLottery = $lotteryNum;
        $currentStage       = $this->getCurrentStage();
        $lottery            = $this->setLottery();
        $distance           = $this->setDistance();
        $trend              = $this->setTrend( $lottery );
        $trend_distance     = $this->setTrend( $distance , 'distance' );

        $m = M( '' );
        $m->startTrans();
        if ( $id = M( 'lottery_num' )->add( $lottery ) ) {
            $distance['lottery_num_id']       = $id;
            $trend['lottery_num_id']          = $id;
            $trend_distance['lottery_num_id'] = $id;
            if ( M( 'lottery_num_distance' )->add( $distance ) && M( 'lottery_num_trend' )->add( $trend ) && M( 'lottery_num_trend' )->add( $trend_distance ) ) {
                $m->commit();
            }
        } else {
            $m->rollback();
        }
    }

    /**
     * @param $num
     * 判断奇数偶数
     *
     * @return bool
     */
    protected function isEven( $num ) {
        if ( $num % 2 == 0 ) {
            return true;
        }

        return false;
    }


    protected function isBig( $num ) {
        if ( $num > 4 ) {
            return true;
        }

        return false;
    }

    protected function processNum( $num ) {
        if ( $num > 0 ) {
            return $num + 1;
        }

        return 1;
    }

    protected function isPrime( $n ) {
        if ( $n <= 3 ) {
            return $n > 1;
        } else if ( $n % 2 === 0 || $n % 3 === 0 ) {
            return false;
        } else {
            for ( $i = 3 ; $i * $i <= $n ; $i += 6 ) {
                if ( $n % $i === 0 || $n % ( $i + 2 ) === 0 ) {
                    return false;
                }
            }

            return true;
        }
    }

    protected function getSMB( $num ) {
        if ( $num > 2 && $num < 7 ) {
            return 2;
        } elseif ( $num >= 7 ) {
            return 3;
        } else {
            return 1;
        }
    }

    protected function getRemainder( $num ) {
        if ( $num % 3 == 0 ) {
            return 0;
        } elseif ( $num % 3 == 1 ) {
            return 1;
        } else {
            return 2;
        }
    }
}