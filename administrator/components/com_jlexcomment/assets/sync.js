(function($){
	$(document).ready(function(){
		// form
		(function(){
			if(typeof ace=='undefined') return;

			var init=function(){
				ace.config.set('basePath',window.path2ace);

				$('.aceditor').each(function(){
					var txt = $(this),
						el = ace.edit(txt.next('div')[0]);
					el.setTheme("ace/theme/monokai");
					el.setOptions({
						minLines:5,
					    maxLines: 15
					});

					el.getSession().setMode("ace/mode/php");
					el.setValue(txt.val());
					//el.getValue();

					el.getSession().on("change", function () {
					    txt.val(el.getValue());
					});

					txt.hide();
				});
			};
			
			var wt=window.setInterval(function(){
				if(typeof window.path2ace=='undefined') return;
				init();
				window.clearInterval(wt);
			},500);
		})();
		
		// sync bar
		(function(){
			var r={
					option:'com_jlexcomment',
					task:'sync.sync',
					offset:0
				};

			var g=function(){
				$.post(window.path2admin+'/index.php', r, function(d){
					var p=0;
					if(d.total>d.offset+d.limit){
						// continue
						p=parseInt((d.offset+d.limit)*100/d.total);
						r.offset=d.offset+d.limit;
						g();
					} else {
						// complete
						p=100;
						window.setTimeout(function(){
							window.location.reload();
						},2000);
					}

					$('#syncIndex .jpercent span').text(p);
					$('#syncIndex .jprocess').css('width', p+'%');
				},'json');
			};

			Joomla.submitbutton = function(task)
			{
				if(task=='sync'){
					$('#syncIndex').addClass('active');
					g();
					return false;
				}

				Joomla.submitform(task);
				return true;
			}
		})();
	});
})(jQuery);