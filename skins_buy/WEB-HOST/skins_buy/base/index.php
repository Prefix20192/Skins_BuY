<?PHP
	include_once("{$_SERVER['DOCUMENT_ROOT']}/modules_extra/skins_buy/base/config.php");
	
	if($page->privacy == 1 && !is_auth()) {
		show_error_page('not_auth');
	}
	
	$tpl->load_template('elements/title.tpl');
	$tpl->set("{title}", $page->title);
	$tpl->set("{name}", $conf->name);
	$tpl->compile("title");
	$tpl->clear();
	
	$tpl->load_template('head.tpl');
	$tpl->set("{title}", $tpl->result['title']);
	$tpl->set("{site_name}", $conf->name);
	$tpl->set("{image}", $page->image);
	$tpl->set("{robots}", $page->robots);
	$tpl->set("{type}", $page->kind);
	$tpl->set("{description}", $page->description);
	$tpl->set("{keywords}", $page->keywords);
	$tpl->set("{url}", $page->full_url);
	$tpl->set("{other}", $module_name_execute['to_head']);
	$tpl->set("{token}", $token);
	$tpl->set("{cache}", $conf->cache);
	$tpl->set("{template}", $conf->template);
	$tpl->set("{site_host}", $site_host);
	$tpl->compile("content");
	$tpl->clear();
	
	$menu = $tpl->get_menu($pdo);

	$nav = array(
		$PI->to_nav('main', 0, 0),
		$PI->to_nav('store', 2, 0),
		$PI->to_nav('skins_store', 1, 0)
	);
	
	$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');
	
	if(isset($_SESSION['id'])) {
		include_once "inc/authorized.php";
	} else {
		include_once "inc/not_authorized.php";
	}
	
	$tpl->load_template($module_name_execute['tpl_dir']."index.tpl");
	$tpl->set("{site_host}", $site_host);
	$tpl->set("{template}", $conf->template);
	
	$tpl->set("{servers}", $skins->get_servers()['body']);
	
	$tpl->compile("content");
	$tpl->clear();