(function($){
	$(document).ready(function(){
		$('#jlexcomment._settings .nav-tabs a[data-toggle]').click(function(){
			if($(this).hasClass('active')) return;

			var n=$(this).attr('href'),
				c=$(this).parents('ul.nav-tabs'),
				d=c.next('.tab-content');

			c.find('.active').removeClass('active');
			d.find('.active').removeClass('active');

			$(this).addClass('active');
			d.find(n).addClass('active');

			if(n=='#permission')
				$('#permissions-sliders .nav-tabs a:eq(0)').trigger('click');
		});
	});
})(jQuery);