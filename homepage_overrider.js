window.onload = function() {
	$(document).ready(function(){
		$('#region-main').append('<div id="crowd_container"> <div class="loading"> <img src="local/enlightencatalog/pix/loading.gif" /> </div> </div>')
		$.ajax({
			type: "POST",
			url: 'local/enlightencatalog/homepage.php',
			success: function(data) {
				$('#crowd_container').html(data);
				$('.tooltip').tooltipster({interactive: true, functionReady:crowd_init_links});


				$('.crowd_visible_category').click(function(){
					if ($('#crowd_parent_' + $(this).attr('pid')).is(':visible')) {
						$('.crowd_arrow_up0').fadeOut();
						/*
  						$('.crowd_hidden_box').fadeOut(); //envisiontel: replaced with below line
						 */
						$('#crowd_parent_' + 
								$(this).attr('pid')).addClass('fading').fadeOut(400, function() { $(this).removeClass('fading')});

						$('#crowd_parent_' + $(this).attr('pid')).removeClass('curSelected');
						$(this).removeClass('curSelected');
						
						var idVal=$(this).parent().attr("id");
						var parentId="crowd_parent_";
						if(idVal){							
							if(idVal.search(parentId)=="-1"){
								$.cookie('crowd_nav', "0");
							}
							else
								{
								curCookie=$.cookie('crowd_nav');								
								cookieSplit = curCookie.split('|');
								for (var i = 0; i < cookieSplit.length; i++) {
									if(cookieSplit[i]==$(this).attr('pid'))
										cookieSplit[i]="";
								}
								var rebuildCookie="";
								for (var i = 0; i < cookieSplit.length; i++) {
									rebuildCookie=rebuildCookie+cookieSplit[i]+'|';
								}
								$.cookie('crowd_nav', rebuildCookie);
								}
						}else
							{							
							$.cookie('crowd_nav', "0");
							}
					} else {						
						$('div').removeClass('curSelected');
						$('#crowd_parent_' + $(this).attr('pid')).addClass('curSelected');
						$('.crowd_category_box').removeClass('curSelected');
						$(this).addClass('curSelected');


						var elem = $(this);
						var box = $('#crowd_parent_' + $(this).attr('pid'));
						var box_parents = box.parents('.crowd_hidden_box');
						box_parents.addClass('crowd_do_not_hide');
						$.each($('.crowd_hidden_box'),function(){
							if (!$(this).hasClass('crowd_do_not_hide')) {
								$(this).addClass('fading').fadeOut(400,function() { $(this).removeClass('fading')});	
							} 
						});
						box_parents.removeClass('crowd_do_not_hide');
						box.fadeIn(function(){
							var p = elem.offset();
							var level = box_parents.length;
							$('.crowd_arrow_up' + level.toString()).show();
							$('.crowd_arrow_up' + level.toString()).offset({top:p.top+238, left:p.left+80});
						});



						var idVal=$(this).parent().attr("id");
						var parentId="crowd_parent_";
						if(idVal){
							if(idVal.search(parentId)=="0")
							{		
								a = $.cookie('crowd_nav')+'|'+$(this).attr('pid');
							}	
							else
							{
								a = $(this).attr('pid')+'|';
							}
						}else
						{
							a = $(this).attr('pid')+'|';
						}
						$.cookie('crowd_nav', a);
					}

					var selectedItems = "|";
					var selectedCourses = "|";
					$('.crowd_category_box').each(function (index) {
						if ($(this).is(':visible')) {
							if (!$(this).parent().hasClass('fading')) {
								if ($(this).attr('pid'))
									selectedItems = selectedItems + $(this).attr('pid') + '|';
								if ($(this).attr('cid'))
									selectedCourses = selectedCourses + $(this).attr('cid') + '|';
							}
							//	  	alert($(this).attr('pid'));
						}
					});	
					
				
					
					
//					$.cookie('crowd_nav_course', "3");


				});

//				$('.crowd_visible_program').click(function(){
//				if ($('#crowd_child_' + $(this).attr('prid')).is(':visible')) {
//				$('.crowd_arrow_up4').fadeOut();
//				/*
//				$('.crowd_hidden_box').fadeOut(); //envisiontel: replaced with below line
//				*/
//				$('#crowd_child_' + 
//				$(this).attr('prid')).addClass('fading').fadeOut(400, function() { $(this).removeClass('fading')});

//				$('#crowd_child_' + $(this).attr('prid')).removeClass('curSelected');
//				$(this).removeClass('curSelected');
//				} else {
//				$('div').removeClass('curSelected');
//				$('#crowd_child_' + $(this).attr('prid')).addClass('curSelected');
//				$('.crowd_category_box').removeClass('curSelected');
//				$(this).addClass('curSelected');


//				var elem = $(this);
//				var box = $('#crowd_child_' + $(this).attr('prid'));
//				var box_parents = box.parents('.crowd_hidden_box_program');
//				box_parents.addClass('crowd_do_not_hide');
//				$.each($('.crowd_hidden_box_program'),function(){
//				if (!$(this).hasClass('crowd_do_not_hide')) {
//				$(this).addClass('fading').fadeOut(400,function() { $(this).removeClass('fading')});	
//				} 
//				});
//				box_parents.removeClass('crowd_do_not_hide');
//				box.fadeIn(function(){
//				var p = elem.offset();
//				var level = box_parents.length;
//				$('.crowd_arrow_up4' + level.toString()).show();
//				$('.crowd_arrow_up4' + level.toString()).offset({top:p.top+238, left:p.left+80});
//				});
//				}

//				var selectedItems = "|";
//				var selectedCourses = "|";
//				$('.crowd_category_box').each(function (index) {
//				if ($(this).is(':visible')) {
//				if (!$(this).parent().hasClass('fading')) {
//				if ($(this).attr('prid'))
//				selectedItems = selectedItems + $(this).attr('prid') + '|';
//				if ($(this).attr('cid'))
//				selectedCourses = selectedCourses + $(this).attr('cid') + '|';
//				}
//				//	  	alert($(this).attr('pid'));
//				}
//				})	;		

//				$.cookie('crowd_nav', selectedItems);
//				$.cookie('crowd_nav_course', selectedCourses);

//				});









//				$('.crowd_visible_course_set').click(function(){

//				if ($('#crowd_child_courses_' + $(this).attr('csid')).is(':visible')) {
//				$('.crowd_arrow_up4').fadeOut();
//				/*
//				$('.crowd_hidden_box').fadeOut(); //envisiontel: replaced with below line
//				*/
//				$('#crowd_child_courses_' + 
//				$(this).attr('csid')).addClass('fading').fadeOut(400, function() { $(this).removeClass('fading')});

//				$('#crowd_child_courses_' + $(this).attr('csid')).removeClass('curSelected');
//				$(this).removeClass('curSelected');
//				} else {
//				$('div').removeClass('curSelected');
//				$('#crowd_child_courses_' + $(this).attr('csid')).addClass('curSelected');
//				$('.crowd_category_box').removeClass('curSelected');
//				$(this).addClass('curSelected');


//				var elem = $(this);
//				var box = $('#crowd_child_courses_' + $(this).attr('csid'));
//				var box_parents = box.parents('.crowd_hidden_box_courses');
//				box_parents.addClass('crowd_do_not_hide');
//				$.each($('.crowd_hidden_box_courses'),function(){
//				if (!$(this).hasClass('crowd_do_not_hide')) {
//				$(this).addClass('fading').fadeOut(400,function() { $(this).removeClass('fading')});	
//				} 
//				});
//				box_parents.removeClass('crowd_do_not_hide');
//				box.fadeIn(function(){
//				var p = elem.offset();
//				var level = box_parents.length;
//				$('.crowd_arrow_up4' + level.toString()).show();
//				$('.crowd_arrow_up4' + level.toString()).offset({top:p.top+238, left:p.left+80});
//				});
//				}

//				var selectedItems = "|";
//				var selectedCourses = "|";
//				$('.crowd_category_box').each(function (index) {
//				if ($(this).is(':visible')) {
//				if (!$(this).parent().hasClass('fading')) {
//				if ($(this).attr('csid'))
//				selectedItems = selectedItems + $(this).attr('csid') + '|';
////				if ($(this).attr('cid'))
////				selectedCourses = selectedCourses + $(this).attr('cid') + '|';
//				}
//				//	  	alert($(this).attr('pid'));
//				}
//				})	;		

//				$.cookie('crowd_nav', selectedItems);
//				$.cookie('crowd_nav_course', selectedCourses);

//				});

//				$('.crowd_visible_course_set_courses').click(function(){
//				window.location.href = $('#crowd_wwroot').val() + '/course/view.php?id=' + $(this).attr('csid');
//				});

				$('.crowd_visible_program').click(function(){
					window.location.href = $('#crowd_wwroot').val() + '/totara/program/view.php?id=' + $(this).attr('prid');
				});
				$('.crowd_visible_course').click(function(){
					window.location.href = $('#crowd_wwroot').val() + '/course/view.php?id=' + $(this).attr('cid');
				});
//				crowd_links();
//				crowd_link_courses();
				crowd_init_links();


				var cookieSplit=new Array();
				if (curCookie)
					cookieSplit = curCookie.split('|');
				for (var i = 0; i < cookieSplit.length; i++) {
					var thisObj = $('div[pid="'+cookieSplit[i]+'"]');
					$(thisObj).show();
					$(thisObj).parent().show();
					//$(thisObj).addClass('curSelected');
				}
				curCookie = $.cookie('crowd_nav_course');
				if (curCookie)
					cookieSplit = curCookie.split('|');
				else
					cookieSplit = new Array();
				for (var i = 0; i < cookieSplit.length; i++) {
					var thisObj = $('div[cid="'+cookieSplit[i]+'"]');
					$(thisObj).show();
					$(thisObj).parent().show();
					//$(thisObj).addClass('curSelected');
				}  				


//				alert($.cookie('crowd_nav'));

				if (!$.cookie('crowd_nav')) {
					$.cookie('crowd_nav','|', { expires:1 });
					$.cookie('crowd_nav_course','|', { expires:1 });
				}
				var curCookie = $.cookie('crowd_nav');
				
				cookieSplit = curCookie.split('|');				
				if(cookieSplit){
					for(var i = 0; i < cookieSplit.length;i++){
						$('#crowd_parent_'+cookieSplit[i]).show();
					}
				}
				if(cookieSplit=="," || cookieSplit=="")
				{
					$('.showhide_0').hide();
					$('.showhide_1').show();
				}
			}
		});

	})
}

function crowd_init_links() {
	$('.crowd_hidden').click(function(){
		$.colorbox({html:'Sorry, you do not currently have access to this content.<br/> Contact your system administrator if you think this is an error'});
	});
	$('.crowd_tooltip_botom_link_categories').click(function(){
		if ($('#crowd_parent_' + $(this).attr('pid')).is(':visible')) {
			$('.crowd_arrow_up0').fadeOut();
			$('.crowd_hidden_box').fadeOut();
		} else {
			var elem = $(this);
			$.each($('.crowd_hidden_box'),function(){
				$(this).fadeOut();
			});
			$('#crowd_parent_' + $(this).attr('pid')).fadeIn(function(){
				var p = elem.offset();
				$('.crowd_arrow_up0').show();
				$('.crowd_arrow_up0').offset({top:p.top+238, left:p.left+80});
			});
		}
	});
	$('.crowd_tooltip_botom_link_course').click(function(){
		window.location.href = $('#crowd_wwroot').val() + '/course/view.php?id=' + $(this).attr('pid');
	});
	$('.crowd_tooltip_botom_link_program').click(function(){
		window.location.href = $('#crowd_wwroot').val() + '/totara/program/view.php?id=' + $(this).attr('prid');
	});
}




//function crowd_links() {
//$('.crowd_hidden').click(function(){
//$.colorbox({html:'Sorry, you do not currently have access to this content.<br/> Contact your system administrator if you think this is an error'});
//});
//$('.crowd_tooltip_botom_link_categories').click(function(){
//if ($('#crowd_child_' + $(this).attr('prid')).is(':visible')) {
//$('.crowd_arrow_up4').fadeOut();
//$('.crowd_hidden_box_program').fadeOut();
//} else {
//var elem = $(this);
//$.each($('.crowd_hidden_box_program'),function(){
//$(this).fadeOut();
//});
//$('#crowd_child_' + $(this).attr('prid')).fadeIn(function(){
//var p = elem.offset();
//$('.crowd_arrow_up4').show();
//$('.crowd_arrow_up4').offset({top:p.top+238, left:p.left+80});
//});
//}
//});
//$('.crowd_tooltip_botom_link_course').click(function(){
//window.location.href = $('#crowd_wwroot').val() + '/course/view.php?id=' + $(this).attr('pid');
//});
//}




//function crowd_link_courses(){

//$('.crowd_hidden').click(function(){
//$.colorbox({html:'Sorry, you do not currently have access to this content.<br/> Contact your system administrator if you think this is an error'});
//});
//$('.crowd_tooltip_botom_link_categories').click(function(){
//if ($('#crowd_child_courses_' + $(this).attr('csid')).is(':visible')) {
//$('.crowd_arrow_up4').fadeOut();
//$('.crowd_hidden_box_courses').fadeOut();
//} else {
//var elem = $(this);
//$.each($('.crowd_hidden_box_courses'),function(){
//$(this).fadeOut();
//});
//$('#crowd_child_courses_' + $(this).attr('csid')).fadeIn(function(){
//var p = elem.offset();
//$('.crowd_arrow_up4').show();
//$('.crowd_arrow_up4').offset({top:p.top+238, left:p.left+80});
//});
//}
//});
//$('.crowd_tooltip_botom_link_course').click(function(){
//window.location.href = $('#crowd_wwroot').val() + '/course/view.php?id=' + $(this).attr('pid');
//});

//}