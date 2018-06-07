/* comment operation */
(function() {
    $('#cmt-wrapper').on('click', 'span', function (event) {
        var $this = $(this);
        if ($this.hasClass('cmt-reply')) {
            if (!this.isCmtBoxShow) {
                $this.parent().parent().parent().parent().find('.cmt-tool').addClass('hidden');
                this.isCmtBoxShow = true;
            } else {
                $this.parent().parent().parent().parent().find('.cmt-tool').removeClass('hidden');
                this.isCmtBoxShow = false;
            }
        }
    });
    $('#cmt-wrapper').on('click', 'button', function (event) {
        var $this = $(this);
        if ($this.hasClass('cmt-btn')) {
            alert('您回复的是：' + $this.parent().parent().find('input').val());
        }
    });
    /* switcher for user message.html */
    var tabController = new TabController({
        menuId: 'm-menu',
        itemsWrapperId: 'switcher',
        itemClass: 'switch-helper',
        eventName: 'click'
    });
})();