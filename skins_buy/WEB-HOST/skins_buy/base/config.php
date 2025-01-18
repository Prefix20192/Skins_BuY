<?php
	$module_name_execute = array(
		'name' => 'skins_buy', 
		'to_head' => "<script src=\"$site_host/modules_extra/skins_buy/ajax/main.js?v={cache}\"></script>", 
		'to_head_admin' => "<script src=\"$site_host/modules_extra/skins_buy/ajax/acp.js?v={cache}\"></script><link rel=\"stylesheet\" href=\"$site_host/modules_extra/cases/templates/admin/css/style.css?v={cache}\">", 
		'tpl_dir' => "../../../modules_extra/skins_buy/templates/$conf->template/tpl/", 
		'tpl_dir_admin' => "../../../modules_extra/skins_buy/templates/admin/tpl/", 
	);
	 
	class SkinsStore {
		private $pdo;
		
		public function __construct($pdo = null) {
			
			if(isset($pdo)) {
				$this->pdo = $pdo;
			}
		}
		
		public function get_all_servers() {
			$sth = $this->pdo->query("SELECT * FROM `servers` WHERE 1");
			
			$b = "<option value=\"0\" disabled selected>- Выбрать -</option>";
			if($sth->rowCount()) {
				$sth->setFetchMode(PDO::FETCH_OBJ);
				
				while($r = $sth->fetch()) {
					$b .= "<option value=\"{$r->id}\">{$r->name} - {$r->address}</option>";
				}
			}
			else {
				$b = '<option value="0">Серверов нет.</option>';
			}
			
			return $b;
		}
		
		public function get_servers() {
			$sth = $this->pdo->query("SELECT * FROM `skins_buy_users` WHERE 1");
			
			if($sth->rowCount()) {
				$b = "<option value=\"0\" disabled selected>- Выбрать -</option>";
				$sth->setFetchMode(PDO::FETCH_OBJ);
				
				while($r = $sth->fetch()) {
					$ath = $this->pdo->query("SELECT * FROM `servers` WHERE `id`='{$r->server_id}'");
					$ath->setFetchMode(PDO::FETCH_OBJ);
					$a = $ath->fetch();
					
					$b .= "<option value=\"{$r->server_id}\">{$a->name} - {$a->address}</option>";
				}
				
				return [
					'count' => $sth->rowCount(),
					'body' => $b
				];
			}
			
			return ['count' => $sth->rowCount(), 'body' => '<option value="0">Серверов нет.</option>'];
		}
		
		public function get_skins_server($server_id) {
			$sth = $this->pdo->query("SELECT * FROM `skins_buy_users` WHERE `server_id`='{$server_id}'");
			
			if($sth->rowCount()) {
				$b = "<label for=\"skins\" class=\"h6\">Выберете скин</label>";
				$b .= "<select id=\"skins\" class=\"form-control\" OnChange=\"select_skin();\">";
				$b .= "<option value=\"0\" disabled selected>- Выбрать -</option>";
				
				$sth->setFetchMode(PDO::FETCH_OBJ);
				
				while($r = $sth->fetch()) {
					$b .= "<option value=\"{$r->id}\">{$r->name}</option>";
				}
				
				$b .= "</select>";
				
				
				$b .= "<div class=\"form-group\">";
				$b .= "<label for=\"buy_nick\" class=\"h6\">Игровой ник</label>";
				$b .= "<input type=\"text\" class=\"form-control\" maxlength=\"32\" id=\"buy_nick\" placeholder=\"Введите свой ник\" value=\"\">";
				$b .= "<label for=\"steamid\" class=\"h6\">Ваш STEAM ID</label>";
				$b .= "<input type=\"text\" class=\"form-control\" maxlength=\"32\" id=\"steamid\" placeholder=\"Введите свой STEAM ID\" value=\"\">";
				$b .= "</div>";
				
				return [
					'count' => $sth->rowCount(),
					'body' => $b
				];
			}
			
			return ['count' => $sth->rowCount(), 'body' => '<option value="0">Скинов нет.</option>'];
		}
		
		public function get_skins_info($skin_id) {
			$sth = $this->pdo->query("SELECT * FROM `skins_buy_users` WHERE `id`='{$skin_id}'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$row = $sth->fetch();
			
			return ['image' => "<img src=\"{$row->image}\">", 'price' => $row->price, 'server_id' => $row->server_id, 'name' => $row->name, 'mdlt' => $row->modelt, 'mdlct' => $row->modelct];
		}
		
		public function buy($skin_id, $user_id, $steamid, $nickname) {
			$info = $this->get_skins_info($skin_id);
			
			$sth = $this->pdo->query("SELECT * FROM `users` WHERE `id`='{$user_id}'");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			$user = $sth->fetch();
			
			if($user->shilings >= $info['price']) {
				if($this->pdo->query("UPDATE `users` SET `shilings`='".($user->shilings - $info['price'])."' WHERE `id`='{$user_id}'")) {
					$this->pdo->query("INSERT INTO `skins_buy_purchases`(`user_id`, `skin_id`, `server_id`, `modelt`, `modelct`, `price`, `steamid`, `nickname`, `timeleft`, `enable`) VALUES ('{$user_id}', '{$skin_id}', '{$info['server_id']}', '{$info['mdlt']}', '{$info['mdlct']}', '{$info['price']}', '{$nickname}', '{$steamid}', '".time()."', '1')");
					return ['result' => 1, 'name' => $info['name']];
				} 
			}
			
			return ['result' => 0];
		}
		
		public function get_admin_skins() {
			$sth = $this->pdo->query("SELECT * FROM `skins_buy_users` WHERE 1");
			$sth->setFetchMode(PDO::FETCH_OBJ);
			
			$a = "";
			if($sth->rowCount()) {
				while($row = $sth->fetch()) {
					$a .= "<tr>";
						$a .= "<td>{$row->id}</td>";
						$a .= "<td>{$row->name}</td>";
						$a .= "<td>{$row->price}</td>";
						$a .= "<td><span class=\"text-danger\" style=\"cursor:pointer;\" OnClick=\"del({$row->id});\">Удалить</span></td>";
					$a .= "</tr>";
				}
			}
			else {
				$a = "<tr colspan=\"4\"><td>Скинов нет.</td></tr>";
			}
			
			return $a;
		}
	}
	
	$skins = new SkinsStore($pdo);
