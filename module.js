$(document).ready(function(){
  $('.assign_cohort_link').click(function(event){
    event.preventDefault();
	$.colorbox({href:$('#assign_cohort_link_val'+ $(this).attr('pid')).val(), onComplete:initform, onClosed:pagerefresh});
  });
  $('#assign_course_link').click(function(event){
    event.preventDefault();
	$.colorbox({href:$('#assign_course_link_val'+ $(this).attr('pid')).val(), onComplete:initform, onClosed:pagerefresh});
  });
  $('#assign_cat_link').click(function(event){
    event.preventDefault();
	$.colorbox({href:$('#assign_cat_link_val'+ $(this).attr('pid')).val(), onComplete:initform, onClosed:pagerefresh});
  });
})

function pagerefresh() {
	location.reload();
}

function initform() {

/* 
	envisiontel - update November 2013 - jeff king
	The "event.originalEvent.explicitOriginalTarget.id" works only in Firefox (Gecko) browsers.
	I had to change the method of capturing submit clicks.

*/
	$('#assignform').delegate(':submit','click', function (event) {
	  	event.preventDefault();
	
		var theId = event.currentTarget.id;
		
		if (theId=='back_to_list') {
		  $.colorbox.close();
		  return 0;
		}

	  	$.ajax({
	  		type: "POST",
	  		url: $('#assignform').attr('action'),
	  		data: $('#assignform').serialize() + '&' + theId + '=' + theId,
	  		success: function(data) {
	  			$('#cboxLoadedContent').html(data);
	  			initform();
	  		}
		});
  });


/*
  $('#assignform').submit(function(event){
  	event.preventDefault();

//alert(event.originalEvent.explicitOriginalTarget.id );
  	$.ajax({
  		type: "POST",
  		url: $(this).attr('action'),
  		data: $(this).serialize() + '&' + event.originalEvent.explicitOriginalTarget.id + '=' + event.originalEvent.explicitOriginalTarget.id,
  		success: function(data) {
  			$('#cboxLoadedContent').html(data);
  			initform();
  		}
	});
  });
  $('#back_to_list').click(function(){
	  $.colorbox.close();
  })
  */

}