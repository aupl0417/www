/**
 * TabController. We must give config params before working
 *
 * menuId(ul element supposed),
 * itemsWrapperId,
 * itemClass,
 * eventName
 */
function TabController (config) {
	if (!config) return;
	this.config = config;
	this.menu = $('#' + config.menuId);
	this.menuLis = $('#' + config.menuId + ' li');
	this.items = $('#' + config.itemsWrapperId + ' .' + config.itemClass);
	this.eventName = config.eventName || 'mouseover';
	var that = this;
	function reset () {
		for (var i = 0; i < that.menuLis.length; i++) {
			$(that.menuLis[i]).removeClass('active');
		}
		for (var i = 0; i < that.items.length; i++) {
			$(that.items[i]).addClass('hidden');
		};
	}
	function fire (event) {
		var index = $.inArray(this, that.menuLis);
		reset();
		$(this).addClass('active');
		$(that.items[index]).removeClass('hidden');
	}
	this.menu.on(config.eventName, 'li', fire);
}
