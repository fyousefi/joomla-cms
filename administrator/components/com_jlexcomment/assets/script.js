function JLexCommentAdmin($, baseUrl)
{
	var _this = this;

	this.dashboard = function(statistic, others)
	{
		var otherHtml = function(data)
			{
				// latest comment
				var latestComments = '<ul class="jcm-db-cms jcm-list-unstyled">';
				$.each (data.latest_comments, function(k,v){
					latestComments+= '<li class="jcm-db-cm">';
						if (v.author_url!=null)
						{
							latestComments+= '<a class="jcm-bold" href="'+v.author_url+'">'+v.author+'</a>';
						} else {
							latestComments+= '<b class="jcm-bold">'+v.author+'</b>';
						}
						latestComments+= ' comment in <a class="jcm-bold" href="'+v.object_url+'" target="_blank">'+v.object_name+'</a>';	
						latestComments+= '<div class="jcm-cm-preview">'+v.comment+'</div>';
						latestComments+= '<span class="jcm-date">'+v.created_time+'</span>';
					latestComments+= '</li>';
				});
				latestComments+= '</ul>';

				if (! data.latest_comments.length)
				{
					latestComments = 'No comments.';
				}

				$('#jcm-dt-comments').empty().append (latestComments);

				// top items
				var topItems = '<ul class="jcm-list-unstyled jcm-db-items">';
				$.each (data.top_items, function(k,v){
					topItems+= '<li>';
						topItems+= '<span class="jcm-number">'+(k+1)+'</span>';
						topItems+= '<a class="jcm-bold" href="'+v.url+'" target="_blank">'+v.title+'</a>';
						topItems+= '<span class="item-cm-count"><b>'+v.cm_count_period+'</b>/<a href="#">'+v.cm_count+'</a></span>';
					topItems+= '</li>';
				});
				topItems+= '</ul>';

				if(!data.top_items.length)
				{
					topItems = 'No items';
				}

				$('#jcm-dt-topitems').empty().append (topItems);

				// new items
				var newItems = '<ul class="jcm-list-unstyled jcm-db-items">';
				$.each (data.new_items, function(k,v){
					newItems+= '<li>';
						newItems+= '<span class="jcm-number">'+(k+1)+'</span>';
						newItems+= '<a class="jcm-bold" href="'+v.url+'" target="_blank">'+v.title+'</a>';
					newItems+= '</li>';
				});
				newItems+= '</ul>';

				if(!data.new_items.length)
				{
					newItems = 'No items';
				}

				$('#jcm-dt-newitems').empty().append(newItems);
			},
			history = {};

		if(typeof others=='object')
		{
			otherHtml(others);
			history[7] = others;
		}

		$('.jcm-db-others-refresh').click(function(){
			var _el = $(this),
				period = _el.attr('data-val')*1;

			_el.addClass('active');
			$('.jcm-db-others-refresh').not(_el).removeClass('active');

			if (history.hasOwnProperty(period))
			{
				otherHtml(history[period]);
			} else {
				$.get(baseUrl+'/index.php?option=com_jlexcomment&task=dashboard.others&p='+period, function(d){
					history[period] = d;
					otherHtml (d);
				}, 'json');
			}
		});

		// chart map
		var lineChart = null,
			historyChart = {},
			sticHtml = function (d)
			{
				var monthNames = [
				  "Jan", "Feb", "Mar",
				  "April", "May", "June", "July",
				  "Aug", "Sep", "Oct",
				  "Nov", "Dec"
				];

				var data = {
					    labels: [],
					    datasets: [
					        {
					        	label: "Comment",
					        	fill: true,
					            backgroundColor: "rgba(255, 99, 132, 0.2)",
					            borderColor: "rgba(255, 99, 132, 1)",
					            pointBackgroundColor: "rgba(255, 99, 132, 1)",
					            pointBorderColor: "#fff",
					            /*lineTension:0,*/
					            data: []
					        },
					        {
					        	label: "Reporting",
					            fillColor: "rgba(54, 162, 235, 0.2)",
					            strokeColor: "rgba(54, 162, 235, 1)",
					            pointColor: "rgba(54, 162, 235, 1)",
					            pointStrokeColor: "#fff",
					            pointHighlightFill: "#fff",
					            pointHighlightStroke: "rgba(54, 162, 235, 1)",
					            data: []
					        },
					        {
					        	label: "Subscriber",
					            fillColor: "rgba(255, 206, 86, 0.2)",
					            strokeColor: "rgba(255, 206, 86, 1)",
					            pointColor: "rgba(255, 206, 86, 1)",
					            pointStrokeColor: "#fff",
					            pointHighlightFill: "#fff",
					            pointHighlightStroke: "rgba(255, 206, 86, 1)",
					            data: []
					        },
					    ]
					};

				$.each(d,function(k,v)
				{
					if ( k==0 )
					{
						// not using for data
						data.datasets[0].label = v[1];
						data.datasets[1].label = v[2];
						data.datasets[2].label = v[3];
					} else {
						var date = v[0].split('-');
						if (date.length==3)
						{
							data.labels.push(monthNames[ date[1]-1 ]+' '+date[0]);
						} else {
							data.labels.push(date[0]+'/'+date[1]);
						}
						
						data.datasets[0].data.push(v[1]);
						data.datasets[1].data.push(v[2]);
						data.datasets[2].data.push(v[3]);
					}
				});



				if(lineChart!=null)
				{
					lineChart.destroy();
					$('#jcm-chart').removeAttr('height');
					$('#jcm-chart').removeAttr('width');
				}

				var ctx = $('#jcm-chart')[0].getContext("2d");
				lineChart = Chart.Line(ctx,
						{
							data: data,
							options: {
								responsive: true,
								maintainAspectRatio: false,
								scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero : true,
						                    stepSize : null
						                }
						            }]
						        }
							}
						}
					);
			};

		if (typeof statistic=='object')
		{
			sticHtml(statistic);
			historyChart["7"] = statistic;
		}

		$('.jcm-db-stic-reload').click(function(){
			var _el = $(this),
				period = _el.attr('data-val');

			_el.addClass('active');
			$('.jcm-db-stic-reload').not(_el).removeClass('active');

			if (historyChart.hasOwnProperty(period))
			{
				sticHtml(historyChart[period]);
			} else {
				$.get(baseUrl+'/index.php?option=com_jlexcomment&task=dashboard.statistics&p='+period, function(d){
					historyChart[period] = d;
					sticHtml (d);
				}, 'json');
			}
		});

		// check version
		var version_compare = function(a, b){
			    if(a===b) return 0;

			    var a_components = a.split(".");
			    var b_components = b.split(".");

			    var len = Math.min(a_components.length, b_components.length);

			    // loop while the components are equal
			    for(var i = 0; i < len; i++)
			    {
			        // A bigger than B
			        if(parseInt(a_components[i])>parseInt(b_components[i])) {
			            return 1;
			        }

			        // B bigger than A
			        if(parseInt(a_components[i])<parseInt(b_components[i])) {
			            return -1;
			        }
			    }

			    // If one's a prefix of the other, the longer one is greater.
			    if(a_components.length>b_components.length) {
			        return 1;
			    }

			    if(a_components.length<b_components.length) {
			        return -1;
			    }

			    // Otherwise they are the same.
			    return 0;
			};

		$.get(baseUrl+'/index.php?option=com_jlexcomment&task=update.check_version', function(d){
			$('#jcm-version-local').text(d.local);
			$('#jcm-version-latest').text(d.server);

			if(d.hasOwnProperty('error'))
			{
				$('#jcm-db .v').addClass('out');
				$('#jcm-db .v ._msg').text(d.error);
			} else {
				$('#jcm-update-act').find('._versionq').text (d.server);
				$('#jcm-update-act').find('._released').text (d.released);

				$('#jcm-db .v').addClass(version_compare(d.server,d.local)==1?'out':'valid');

				if($('#jcm-db .v').hasClass('out'))
				{
					$('#jcm-db .v ._msg').html('The latest version has been released in <u>'+d.released+'</u>, <a href="https://www.jlexart.com/downloads" target="_blank">Update Now!</a>');
				} else {
					$('#jcm-db .v ._msg').html('<a href="https://www.jlexart.com/" target="_blank">Official Website</a>');
				}
			}
		}, 'json');

		$("#jcm-upgrade").click(function(){
			var _el 	= $(this),
				oldText = _el.text();

			_el.attr("disabled", "disabled");
			_el.text("Updating...");

			$.post(baseUrl+'/index.php?option=com_jlexcomment&task=update.install', function(d){
				if(d.status==400)
				{
					_el.text(oldText).removeAttr("disabled");
					alert(d.error);
				} else {
					_el.text("Updated. The page will refresh at momment.");
					window.top.location = window.top.location;
				}
			},'json');
		});

		// toolbar
		$(".header").css('padding', '25px');
	};

	this.comments = function()
	{
		$('.jcm-cm-quick').click(function(){
			$(this).parents('td').find('.jcm-cm-full').toggleClass('active');
			$('body').toggleClass('disOverflow');
		});

		$('.jcm-full-exist').click(function(e){
			e.preventDefault ();
			$(this).closest('.jcm-cm-full').removeClass('active');
			$('body').removeClass('disOverflow');
		});

		$('#sortbyfull').change(function(){
			var sort = $(this).val(),
				order = sort.split(' ')[0],
				order_dir = sort.split(' ')[1];

			$('#adminForm input[name="filter_order"]').val(order);
			$('#adminForm input[name="filter_order_Dir"]').val(order_dir);

			Joomla.submitform();
		});
	};

	this.comment = function()
	{
		// file
		var fileTpl = function(data){
			var html = '<li class="jcm-media-item '+(data.status=='completed'?'completed':'')+'">';
					html+= '<div>';
						html+= '<label class="f-label">Name</label>';
						html+= '<span class="f-value _name_stic">'+data.name+'</span>';
					html+= '</div>';

					html+= '<div>';
						html+= '<label class="f-label">Description</label>';
						html+= '<span class="f-value _desc_stic">'+(/^\s*$/.test(data.description)?'No description':data.description)+'</span>';
					html+= '</div>';

					if(data.status=='completed')
					{
						html+= '<div>';
							if(data.hasOwnProperty('download')){
								html+= '<label class="f-label">Download</label>';
								html+= '<span class="f-value"><a href="'+data.download+'" target="_blank">GET</a></span>';
							}

							if(data.hasOwnProperty('preview')){
								html+= '<label class="f-label">Preview</label>';
								html+= '<span class="f-value jcm_sticker"><img src="'+data.preview+'"></span>';
							}
						html+= '</div>';
						html+= '<input type="hidden" class="media_id" name="media_cid[]" value="'+data.id+'">';
					}else if(data.status=='process'){
						html+= '<div>';
							html+= '<label class="f-label">Size</label>';
							html+= '<span class="f-value">'+data.fileSize+'</span>';
						html+= '</div>';
						html+= '<input type="hidden" class="media_id" name="media_cid[]" value="0" />';

						html+= '<div class="_error"></div>';

						html+= '<div class="jcm-progress-bar">';
							html+= '<div class="jcm-progress-result"><span class="jcm-progress-text">0</span>%</div>'
						html+= '</div>';
					}

					html+= '<div class="jcm-media-edit">';
						html+= '<label>Name</label>';
						html+= '<input type="text" class="_name input-block-level form-control" value="'+data.name+'" />';
						html+= '<label>Description</label>';
						html+= '<textarea class="_desc input-block-level form-control">'+data.description+'</textarea>';
						html+= '<div class="text-right">';
							html+= '<button type="button" class="m-btn task-update">Update</button>';
							html+= '<button type="button" class="m-btn task-cancel">Cancel</button>';
						html+= '</div>';
					html+= '</div>';

					html+= '<div class="jcm-media-btn text-right" style="padding-top:5px">';
						html+= '<button type="button" class="m-btn task-edit">Edit</button>';
						html+= '<button type="button" class="m-btn task-remove">Remove</button>';
					html+= '</div>';
				html+= '</li>';

			var _el = $(html);

			_el.on('click', '.task-cancel', function(){
				_el.find ('.jcm-media-edit').removeClass ('active');
			})
			.on('click', '.task-edit', function(){
				_el.find ('.jcm-media-edit').addClass ('active');
			})
			.on('click', '.task-remove', function(){
				var cf = confirm("Are you sure want to remove this file ?");
				if (cf!==true)
				{
					return;
				}

				_el.fadeOut('fast', function(){
					_el.remove();
				});
			})
			.on('click', '.task-update', function (){
				var id = _el.find ('input.media_id').val ();
				if (typeof id == 'undefined')
				{
					return false;
				}

				var request = {
					id : id,
					name : _el.find('._name').val(),
					description : _el.find('._desc').val()
				};

				if (/^\s*$/.test(request.name))
				{
					_el.find('._name').focus ();
					return false;
				}

				_el.find('.jcm-media-edit').find('input,button,textarea').attr('disabled','disabled');

				$.post (baseUrl+'/index.php?option=com_jlexcomment&task=media.update', request, function(d){
					_el.find('.jcm-media-edit').find('input,button,textarea').removeAttr('disabled');
					if (d.status==400)
					{
						alert (d.error);
						return false;
					}

					_el.find('._name_stic').text (request.name);
					_el.find('._desc_stic').text (request.description);
					_el.find ('.jcm-media-edit').removeClass ('active');
				},'json');
			});

			_el.appendTo ( $('#list-files') );

			return _el;
		};

		if ( typeof window.jcm_media=='object')
		{
			$.each (window.jcm_media, function(k,v){
				v.status = 'completed';
				fileTpl(v);
			});
		};

		$('#jcm-file-attached').change(function(evt){
			var _ob = $(this);

			if (! evt.target.files.length) return false;

			$.each(evt.target.files, function(k, file){
				var data = new FormData(),
					formatSize = function(size) {
						if(size>1000*1000*1000){
							size=parseInt(size/(1000*1000*1000)) + ' Gb';
						}else if(size>1000*1000){
							size=parseInt(size/(1000*1000)) + ' Mb';
						}else{
							size=parseInt(size/1000)+' Kb';
						}
						return size;
					},
					filePreview = fileTpl ({
						name : file.name,
						description : '',
						fileSize : formatSize(file.size),
						status : 'process'
					});

				if ( typeof FormData=='undefined')
				{
					// not support HTML 5
					alert ("Your brownser very low to task this feature.");
				} else {
					data.append('file', file);

					window.setTimeout (function(){
						var xhr = $.ajax({
							url: baseUrl+'/index.php?option=com_jlexcomment&task=media.upload',
							data: data,
							processData: false,
							contentType: false,
							dataType: 'json',
							xhr: function() {
								var xhrobj = $.ajaxSettings.xhr();
								if (xhrobj.upload) {
							        //obj.addClass('loading');
							        xhrobj.upload.addEventListener('progress', function(event) {
							            var percent = 0;
							            var position = event.loaded || event.position;
							            var total = event.total;
							            if (event.lengthComputable) {
							                percent = Math.ceil(position / total * 100);
							            }
							            //Set progress
							            filePreview.find('.jcm-progress-result').css('width',percent+'%');
							            filePreview.find('.jcm-progress-text').text(percent);
							        }, false);
							    }
								return xhrobj;
							},
							type: 'POST',
							success: function(d){
								if (d.status==200) {
									filePreview.find ('.media_id').val (d.id);
									filePreview.addClass ('completed');
								} else {
									filePreview.find ('._error').text (d.error);
								}
							}
						});

						/*filePreview.on ('click','._cancel', function(){
							if (typeof xhr!='undefined') xhr.abort();
							filePreview.fadeOut (function(){
								$(this).remove ();
							});
						});*/
					}, k*500);
				}
			});

			window.setTimeout (function(){
				_ob.val ('');
			},2000);
		});
	};

	this.blacklist = function(){};

	this.events = function ()
	{
		$(document).on('click', '.jcm-tab-menu .ie', function(e){
			e.preventDefault ();
			var _el = $(this),
				_dest = _el.attr ('data-id'),
				_tab = _el.closest ('.jcm-tabs-box');

			if(_el.hasClass('active')) return;

			_el.addClass('active').siblings().removeClass ('active');
			_tab.find('#'+_dest).addClass('active').siblings().removeClass('active');
		});
	};

	this.users = function()
	{
		$(".jcm-change-thumb").click(function(){
			var _el = $('<input class="jcm-thumb-file" name="file" type="file" style="visibility:hidden;">'),
				uid = $(this).attr ('data-uid');

			$("#jlexcomment form input.jcm-thumb-file").remove ();

			_el.wrap('<div class="jcm-thumb-file-box"></div>');
			_el.appendTo("#jlexcomment form");

			_el.change(function(){
				$("#jlexcomment form").find('[name=task]').val ('users.upload');
				$("#jlexcomment form").append('<input type="hidden" name="id" value="'+uid+'" />');
				Joomla.submitform();
			});

			_el.trigger("click");
		});
	};

	this.import = function()
	{
		$("#import-btn").click(function(){
			var migrator = $("select[name=migrator]").val(),
				total    = 0,
				success  = 0;

			$(this).fadeOut("fast");
			$(this).next(".progress").fadeIn("fast");

			// begin import
			var importComplted = function(total)
				{
					var url = baseUrl+"/index.php?option=com_jlexcomment&task=import.migrator&done=1&total="+total;
					window.location = url;
				},
				importBegin = function(offset)
				{
					var request = {
							option: "com_jlexcomment",
							task:"import.migrator",
							migrator:migrator,
							offset:offset
						};

					if(offset==0)
					{
						request['gtotal'] = 1;
					}

					$.post(baseUrl+"/index.php", request, function(d){
						if (typeof d!="object")
						{
							alert ("Couldn't import data from "+migrator+" . Contact JLexArt to solve this issue.");
							return false;
						}

						if (offset==0)
						{
							if (d.total==0)
							{
								importComplted(0);
								return false;
							}

							total = d.total*1;
						}
						
						success+= d.success;

						var percent = (success/total)*100;
							percent = percent>100?100:percent;

						if (d.success==-1)
						{
							importComplted(success+1);
							return;
						} else {
							$(".progress .bar").css("width", percent+"%");
							$(".progress .bar-value").text(percent.toFixed(2));
						}

						window.setTimeout(function(){
							importBegin(offset+50);
						},1000);
					}, "json");
				};

			importBegin(0);
		});
	};

	this.integration = function(){}

	this.style = function()
	{
		var _css = $('#jform_css'),
			_el  = $('#jcm-style-preview');

			_el.attr('style',_css.val());

		_css.bind('input propertychange',function(){
			_el.attr('style', $(this).val());
		});

		$('.style-code').each(function(){
			var _css = $(this).text();
				$(this).prev('.style-preview').attr('style', _css);
		});
	}

	this.j3 = function(menus, active){
		$('.row-fluid.row').removeClass('row');
		
		var h='<div id="jcm-menu">';
			h+='<div class="circle-ripple"></div>';
			h+='<button type="button"></button>';
			h+='<div class="ls"><ul>';
		$.each(menus, function(k,v){
			h+='<li'+(k==active?' class="active"':'')+'><a href="'+baseUrl+'/index.php?option=com_jlexcomment&view='+k+'">'+v+'</a></li>';
		});
		h+='</ul></div></div>';

		var $h=$(h);
		$h.on('click', 'button', function(){
			$h.find('.ls, button').toggleClass('active');
		});

		$h.appendTo('body');
		window.setTimeout(function(){
			$('#jcm-menu .circle-ripple').remove();
		}, 7000);
	}

	this.events();
}
