<?php if(iam()){ ?>
	<link rel="stylesheet" type="text/css" href="/cms/mod_admin/assets/admin_panel.css" />
	<script>
		document.body.style.position="relative"
		document.body.style.top="30px"
	</script>
	<div style="position:fixed;width:100%;height:30px;background:#2C2C2C;left:0;top:0;z-index:6900;border-bottom:1px solid white;">
	<ul id="admin_main_user_menu">
	<li>
    <a href="#" >
          Редактировать
          <b class="caret"></b>
    </a>
    <ul>
	
	
 	<?php foreach (d()->admin['leftmenu'] as $_value) { ?>
	
		<?php if(substr( $_value[0],0,7)!='/admin/'){ ?>
			<li><a target="_blank" href="/admin/list/<?php print $_value[0]; ?>"   ><?php print $_value[1]; ?></a></li>
		<?php }else{ ?>
			<li><a target="_blank" href="<?php print $_value[0]; ?>"   ><?php print $_value[1]; ?></a></li>
		<?php } ?>
	<?php } ?>
        
		
		
    	
    </ul>
  </li>

		<?php if(iam('developer')){ ?>
		  
				  <li>
			<a href="#"
				  
				 >
				  Скаффолдинг
				  
			</a>
			<ul >
			
			
			<li><a target="_blank" href="/admin/scaffold/new">Создать</a></li>
				

				
				<li><a target="_blank" href="/admin/scaffold/install_plugin">Установить расширение</a></li>
				<li><a target="_blank" href="/admin/scaffold/update_system">Обновить систему</a></li>	
				<li><a target="_blank" href="/admin/scaffold/update_scheme">Обработать схему</a></li>
				
			</ul>
		  </li>
		  
		  
		<?php } ?>  
		  <li><a href="/admin/logout">Выход</a></li>
	</ul>
	
	</div>
	
	
<?php } ?>