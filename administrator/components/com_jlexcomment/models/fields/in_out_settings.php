<?php
/**
 * @package     JLex Comment
 * @version     1.0.0
 * @copyright   Copyright (C) 2013-2016 JLexArt Team (http://www.jlexart.com). All rights reserved.
 * @license     GNU/GPL or later
 * @author      JLexArt (support@jlexart.com)
 */

defined ( '_JEXEC' ) or die ();

class JFormFieldJCM_In_Out_Settings extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'JCM_In_Out_Settings';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$html='<div class="alert alert-danger" style="border-left: 1px solid; border-right: 1px solid;">
			<p>'.jtext::_('JCM_IN_OUT_GUIDE').'</p>
			<button id="jcm-import-settings" class="btn btn-small btn-sm btn-primary">
				<span class="icon-upload" aria-hidden="true"></span>
				<span class="t">'.jtext::_('JCM_IMPORT').'</span>
			</button>
			<button id="jcm-export-settings" class="btn btn-small btn-sm btn-danger">
				<span class="icon-download" aria-hidden="true"></span>
				<span class="t">'.jtext::_('JCM_EXPORT').'</span>
			</button>
		</div>';

		$js= '(function($){
			$(document).ready(function(){
				$("#jcm-import-settings").click(function(e){
					e.preventDefault();
					var btn=$(this);
					var el=$("<input type=\"file\" />");

					el.change(function(evt){
						if(!evt.target.files.length) return;
						var file=evt.target.files[0],
							reader = new FileReader()
						
						reader.onload=function(e){
							try {
								var config=$.parseJSON(e.target.result);
							} catch(err){alert("Error: "+err);}

							if(typeof config=="object"){
								var otext=btn.find(".t").text();
								btn.find(".t").text("Please wait...");
								$.post("'.JUri::base(true).'/index.php?option=com_jlexcomment&task=settings.import", {dt:e.target.result}, function(d){
									btn.find(".t").text(otext);
									if(d.status==200){
										alert("Success!");
										window.location.reload();
									} else {
										alert(d.error);
									}
								}, "json");
							}
						};
						reader.readAsText(file);
					});

					el.trigger("click");
				});

				$("#jcm-export-settings").click(function(e){
					e.preventDefault();
					var btn=$(this);

					var otext=btn.find(".t").text();
						btn.find(".t").text("Please wait...");

					$.get("'.JUri::base(true).'/index.php?option=com_jlexcomment&task=settings.export", function(d){
						btn.find(".t").text(otext);
						if(d.status==200){
							var el=document.createElement("a");
							el.setAttribute("href", "data:text/plain;charset=utf-8,"+encodeURIComponent(JSON.stringify(d.data)));
							el.setAttribute("download", "jlexcomment.json");
							el.style.display="none";

							document.body.appendChild(el);
							el.click();
							document.body.removeChild(el);
						} else {
							alert("Error");
						}
					}, "json");
				});
			});
		})(jQuery);';

		JFactory::getDocument()->addScriptDeclaration($js);

		return $html;
	}
}
