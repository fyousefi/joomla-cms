function JCMReaction($,data)
{
	var jsondata = null,
		nextIndex = 2;
	try
	{
		jsondata = $.parseJSON(data);
	} catch (err) {
		jsondata = null;
		console.log(err);
	}

	var addNewRow = function(data)
	{
		var _el = $(".jcm-reaction-layout").clone();
			_el.removeClass("jcm-reaction-layout")
				.addClass("jcm-reaction-item");

		if (typeof data=="object")
		{
			$.each(data, function(k,v){
				_el.find('input[data-name='+k+']').val(v);
				if (k=='id' && v*1>=nextIndex)
				{
					nextIndex=v*1+1;
				}
			});
		} else {
			_el.find('input[data-name=id]').val(nextIndex);
			nextIndex++;
		}

		// pick color
		_el.find("input[data-name=color]").colpick({
			layout:'hex',
			colorScheme:'dark',
			onSubmit:function(hsb,hex,rgb,el) {
				$(el).css('background','#'+hex);
				$(el).colpickHide();
				$(el).val(hex);
			}
		}).keyup(function(){
			$(this).colpickSetColor(this.value);
		}).each(function(){
			$(this).css('background-color','#'+$(this).val());
			$(this).colpickSetColor(this.value);
		});

		_el.find("._remove").click(function(){
			_el.fadeOut("fast",function(){
				_el.remove();
			});
		});

		_el.insertBefore( $("#jcm-reaction-text button._add") );

		return _el;
	}

	// events
	$("#jcm-reaction-text button._add").click(function() {
		addNewRow();
	});

	if (jsondata!=null)
	{
		$.each(jsondata, function(k,v){
			addNewRow(v);
		});
	}
	
	// check structure when submit
	Joomla.submitbutton=(function(task) {
		if (task=='settings.save') {
			var error=false,
			regexColor=/^[A-Fa-f0-9]{6}$/;

			var enable = $('select[name="jform[jcm_reaction]"]').val();

			if (enable!="1")
			{
				Joomla.submitform(task);
				return;
			}

			$('.jcm-reaction-item input').each(function(){
				var vl=$(this).val();
				if ($.trim(vl)=='') {
					$(this).focus();
					alert("Field value not empty.");
					error=true;
					return;
				}
				
				// color
				if ($(this).hasClass('color')&&!regexColor.test(vl)) {
					$(this).focus();
					alert("Color format incorrect.");
					error=true;
					return;
				}
			});

			if (error) return false;
		}

		// output
		var outputs = [];
		$('.jcm-reaction-item').each(function(){
			var output = {};
			$(this).find("input").each(function(){
				output[$(this).attr("data-name")] = $(this).val();
			});
			outputs.push(output);
		});

		$("#jcm-reaction-data").val(JSON.stringify(outputs));

		Joomla.submitform(task);
	});
}