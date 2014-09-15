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
  				  	})	;		
  				  	
  				  	$.cookie('crowd_nav', selectedItems);
  				  	$.cookie('crowd_nav_course', selectedCourses);
  					
  				});
  				$('.crowd_visible_course').click(function(){
  
  					window.location.href = $('#crowd_wwroot').val() + '/course/view.php?id=' + $(this).attr('cid');
  				});
  				crowd_init_links();
  				
  				var curCookie = $.cookie('crowd_nav');
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
  				

//  				alert($.cookie('crowd_nav'));

				if (!$.cookie('crowd_nav')) {
	  				$.cookie('crowd_nav','|', { expires:1 });
	  				$.cookie('crowd_nav_course','|', { expires:1 });
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
}
