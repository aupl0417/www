<?php

return array(

		'view_filter' => array(
				// 防止表单重复提交
				'Behavior\TokenBuildBehavior',
		),
		
		/* 控制器开始标签位 */
		'action_begin'	=>	array(
				'Home\\Behaviors\\ShopkeeperAutoLoginBehavior',
		),
		
);