<?php
//方形
// $sudo = [
// 	[[],[],[],1,[],[],[],[],[]],//1x1~3x3
// 	[7,1,[],8,9,2,4,3,[]],//1x4~3x6
// 	[8,[],9,6,[],5,2,7,1],//1x7~3x9
// 	[[],[],[],[],1,[],[],[],[]],//4x1~6x3
// 	[1,[],8,5,[],9,[],[],[]],//4x4~6X6
// 	[5,9,6,7,[],[],4,1,8],//4X7~6x9
// 	[[],[],[],[],[],2,[],4,1],//7x1~9x3
// 	[[],8,1,[],[],[],[],[],[]],//7x4~9x6
// 	[9,6,[],1,5,[],3,8,[]],//7x7~9x9
// ];

$sudo = (array)json_decode($_POST['sudo']);

function start_sudoku(&$sudo){
	//直線暫存
	$sudo2 = [[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]]];
	//直線
	$sudo3 = [[],[],[],[],[],[],[],[],[]];

	//橫線暫存
	$sudo4 = [[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]]];
	//橫線
	$sudo5 = [[],[],[],[],[],[],[],[],[]];

	//九宮格暫存
	$sudo6 = [];

	//九宮格
	create_sucoku($sudo, $sudo2, $sudo4);

	//直線
	if(row_f($sudo, $sudo2, $sudo3)){

		//橫線
		if(line_f($sudo, $sudo4, $sudo5, $sudo6)){
			if(check_sucoku($sudo, $sudo6, 'check_sucoku_end')){
				if(row_s($sudo, $sudo6)){
					line_s($sudo, $sudo6);
				}
			}
		}
	}
	//橫線 (存入$sudo6)
}

start_sudoku($sudo);

// print_r($sudo);
// exit();

foreach ($sudo as $key => $value){
	foreach ($value as $row => $item){
		if(is_array($item)){
			$sudo[$key][$row] = '&nbsp;';
		}
	}
}

echo json_encode((object)$sudo);

function check_sucoku(&$sudo, &$sudo6, $fun){
	//比對規則內其餘格子
	// ex: 1 2 x
	//     4 x x
	//     7 8 9
	//0:2 位置可能出現 3,5,6
	//1:1 位置可能出現 5,6
	//1:2 位置可能出現 5,6
	// 則0:2 填入 3

	foreach ($sudo6 as $key => $value){
		if(count($value)==1) continue;
		foreach ($value as $row => $item){
			$co = $value;
			unset($co[$row]);
			$da = array_diff($item,array_unique(call_user_func_array('array_merge',$co)));
			if(count($da)==1){
				$fun($sudo, $da, $key, $row);
				start_sudoku($sudo);
				return false;
			}
		}
	}
	return true;
}

function row_s(&$sudo, &$sudo6){
	//陣列轉換為一行九格
	// 第0行 => [
	// 	第0列( 可能出現 [1,2,3])
	// 	第1列
	// 	...
	// ],
	// 第1行 => [
	$co = [];

	foreach ($sudo6 as $key => $value){
		foreach ($value as $row => $item){
			$co[($row%3) + ($key%3 * 3)][floor($row/3) + (floor($key/3) * 3)] = $item;
		}
	}

	return check_sucoku($sudo, $co, 'row_s_end');
}

function line_s(&$sudo, &$sudo6){
	//陣列轉換為一列九格
	// 第0列 => [
	// 	第0行( 可能出現 [1,2,3])
	// 	第1行
	// 	...
	// ],
	// 第1列 => [
	$co = [];

	foreach ($sudo6 as $key => $value) {
		foreach ($value as $row => $item){
			$co[floor($key/3)*3 + floor($row/3)][$row%3 + ($key%3 * 3)] = $item;
		}
	}

	check_sucoku($sudo, $co, 'line_s_end');

}


//塞回處理-------------------------
function check_sucoku_end(&$sudo, $da, $key, $row){
	foreach ($da as $vv){
		$sudo[$key][$row] = $vv;
	}
}

function row_s_end(&$sudo, $da, $key, $row){
	foreach ($da as $vv){
		$sudo[floor($key/3) + floor($row/3)*3][($row%3 * 3) + $key%3] = $vv;
	}
}

function line_s_end(&$sudo, $da, $key, $row){
	foreach ($da as $vv){
		$sudo[(floor($key/3) * 3) + (floor($row/3) * 3)][$row%3 + ($key%3 * 3)] = $vv;
	}
}
//塞回處理-------------------------




function create_sucoku(&$sudo, &$sudo2, &$sudo4){

	//正方形
	foreach ($sudo as $cube_n => &$cube){
		//預設
		$a = [1,2,3,4,5,6,7,8,9];

		//砍掉已有
		foreach ($cube as $item) {
			if(is_numeric($item)){
				unset($a[$item-1]);
			}
		}

		//塞回格子內可能出現數字 (正方形比對後)
		foreach ($cube as $key => &$value) {
			if(is_array($value)){
				// $sudo[$cube_n][$key] = $a;
				$value = $a;
			}else{
				//暫存直線
				$sudo2[$cube_n][$key%3][] = $value;

				//暫存橫線
				$sudo4[$cube_n][floor($key/3)][] = $value;
			}
		}
	}
}

function row_f(&$sudo, &$sudo2, &$sudo3){

	//直線比對-----------------------開始

	//直線內 有出現的數字
	$y = 0;
	for($x=0;$x<9;){

		$sudo3[$y] = array_merge($sudo3[$y],$sudo2[$x][0]);
		$sudo3[$y+1] = array_merge($sudo3[$y+1],$sudo2[$x][1]);
		$sudo3[$y+2] = array_merge($sudo3[$y+2],$sudo2[$x][2]);

		temp_b($x,$y);
	}


	//直線內 可以出現的數字
	$a = [1,2,3,4,5,6,7,8,9];
	foreach ($sudo3 as $key => $value) {
		$sudo3[$key] = array_diff($a,$value);
	}

	$y = 0;
	for($x=0;$x<9;){
		$w = $y;
		for($z=0;$z<9;){
			if(is_array($sudo[$x][$z])){

				//用九宮格內可能出現的數字 跟直線可能數字 比對 (若剩下一個塞回)
				$sudo[$x][$z] = array_intersect($sudo[$x][$z],$sudo3[$w]);
				if(count($sudo[$x][$z])==1){
					foreach ($sudo[$x][$z] as $value){
						$sudo[$x][$z] = $value;
					}
					start_sudoku($sudo);
					return false;
				}
			}

			switch ($z) {
				case 6:
					$z=1;
					$w++;
					break;
				case 7:
					$z=2;
					$w++;
					break;
				default:
					$z+=3;
					break;
			}
		}

		temp_b($x,$y);
	}
	return true;
	//直線比對-----------------------結束
}

function line_f(&$sudo, &$sudo4, &$sudo5, &$sudo6){

	//橫線比對-----------------------開始
	$y = 0;
	for($x=0;$x<9;$x++){

		$sudo5[$y] = array_merge($sudo5[$y],$sudo4[$x][0]);
		$sudo5[$y+1] = array_merge($sudo5[$y+1],$sudo4[$x][1]);
		$sudo5[$y+2] = array_merge($sudo5[$y+2],$sudo4[$x][2]);

		temp_a($x,$y);
	}

	$a = [1,2,3,4,5,6,7,8,9];
	foreach ($sudo5 as $key => $value) {
		$sudo5[$key] = array_diff($a,$value);
	}

	$y = 0;
	for($x=0;$x<9;$x++){

		$w = $y;
		for($z=0;$z<9;$z++){

			if(is_array($sudo[$x][$z])){
				$sudo[$x][$z] = array_intersect($sudo[$x][$z],$sudo5[$w]);
				if(count($sudo[$x][$z])==1){
					foreach ($sudo[$x][$z] as $value){
						$sudo[$x][$z] = $value;
					}
					start_sudoku($sudo);
					return false;
				}else{
					//存下 剩下的空格子內 可以出現的數字
					$sudo6[$x][$z] = $sudo[$x][$z];
				}
			}

			switch ($z) {
				case 2:
				case 5:
					$w++;
					break;
			}
		}

		temp_a($x,$y);
	}

	return true;
	//橫線比對-----------------------結束
}

function temp_a($a,&$b){
	switch ($a) {
		case 2:
			$b=3;
			break;
		case 5:
			$b=6;
			break;
	}
}

function temp_b(&$a,&$b){
	switch ($a) {
		case 6:
			$a=1;
			$b=3;
			break;
		case 7:
			$a=2;
			$b=6;
			break;
		default:
			$a+=3;
			break;
	}
}

?>