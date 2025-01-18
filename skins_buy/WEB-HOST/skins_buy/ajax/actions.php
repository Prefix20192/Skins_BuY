<?PHP
	include_once("../../../inc/start.php");
	include_once("../../../inc/protect.php");
	include_once("../base/config.php");
	
	if(empty($_POST['phpactions']) || !is_auth() || $_SESSION['token'] != $_POST['token']) {
		exit(json_encode(['status' => '2']));
	}
	
	if(isset($_POST['load_skins_list'])) {
		exit(json_encode([
			'status' => '1',
			'body' => $skins->get_skins_server(check($_POST['server_id'], "int"))['body']
		]));
	}
	
	if(isset($_POST['load_skin_info'])) {
		$info = $skins->get_skins_info(check($_POST['skin_id'], "int"));
		
		exit(json_encode([
			'status' => '1',
			'info' => $info['image'],
			'price' => $info['price']
		]));
	}

	if(isset($_POST['buy'])) {
		if(empty($_POST['steamid']) || $_POST['steamid'] == '') {
			exit(json_encode([
				'status' => '2',
				'type' => 'warning',
				'message' => 'Не оставляйте поле STEAM_ID пустым!'
			]));
		}
		
		if(empty($_POST['nickname']) || $_POST['nickname'] == '') {
			exit(json_encode([
				'status' => '2',
				'type' => 'warning',
				'message' => 'Не оставляйте поле Игровой ник пустым!'
			]));
		}
		
		$r = $skins->buy(check($_POST['skin_id'], "int"), $_SESSION['id'], check($_POST['nickname'], null), check($_POST['steamid'], null));
		
		if($r['result']) {
			exit(json_encode([
				'status' => '1',
				'type' => 'success',
				'message' => "Спасибо за покупку {$r['name']}"
			]));
		}
		
		exit(json_encode([
			'status' => '2',
			'type' => 'danger',
			'message' => 'Недостаточно средств для покупки'
		]));
	}