<?php
// +----------------------------------------------------------------------
// | Pachira IoT PaaS
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.pachila.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 卢渊 <microlyu@qq.com> <http://www.pachila.cn>
// +----------------------------------------------------------------------


namespace Product\Common;

class LightColorGenerator extends DataGenerator
{
	public function generate(){
		$colorRandom = rand(1,3);// 1 : red, 2, green, 3, blue, 4 white
		
		$red = rand(0, 1000);
		$green = rand(0, 1000);
		$blue = rand(0, 1000);
		$white = rand(0, 1000);
		
		
		if ($colorRandom==1) {
			$red = $red + rand(1000, 10000);
		} 
		if ($colorRandom==2) {
			$green = $green + rand(1000, 10000);
		}
		if ($colorRandom==3) {
			$blue = $blue + rand(1000, 10000);
		}
		if ($colorRandom==4) {
			$white = $white + rand(1000, 10000);
		}
		
		
		$value = sprintf("{\"rgb\":{\"red\":%d,\"green\":%d,\"blue\":%d,\"white\":%d}}", $red, $green, $blue, $white);
		return json_decode($value, TRUE);
	}
}