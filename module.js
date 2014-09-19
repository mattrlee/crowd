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
  $('#assign_prog_link').click(function(event){
	    event.preventDefault();
		$.colorbox({href:$('#assign_prog_link_val'+ $(this).attr('pid')).val(), onComplete:initform, onClosed:pagerefresh});
	  });
  
})

function pagerefresh() {
	    var value = document.getElementById("selectType").value;
	    var id = document.getElementById("id").value;
	    window.location.assign("http://murray.enlightenlms.com/local/enlightencatalog/assign_control.php?id="+id+"&choiceId="+value);
//	    window.location.append("http://murray.enlightenlms.com/local/enlightencatalog/assign_control.php?id=4&choiceId="+value);
//	display(document.getElementById("selectType").value);
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
	function displayDiv(){
		var val = document.getElementById("selectType").value;
		   if (val == "0"){
		    	document.getElementById('divCoursesAssignButton').style.display = 'none';
		    	document.getElementById('divAssignedCourses').style.display='none';
		    	document.getElementById('divCatAssignButton').style.display = 'none';
		    	document.getElementById('divAssignedCats').style.display='none';
		    	document.getElementById('divProgramsAssignButton').style.display = 'none';
		    	document.getElementById('divAssignedPrograms').style.display='none';
		    }
		
		    if (val == "1"){
		    	document.getElementById('divCoursesAssignButton').style.display = 'block';
		    	document.getElementById('divAssignedCourses').style.display='block';
		    	document.getElementById('divCatAssignButton').style.display = 'none';
		    	document.getElementById('divAssignedCats').style.display='none';
		    	document.getElementById('divProgramsAssignButton').style.display = 'none';
		    	document.getElementById('divAssignedPrograms').style.display='none';
		   }
			    
		   if (val == "2"){
			    document.getElementById('divCoursesAssignButton').style.display = 'none';
			    document.getElementById('divAssignedCourses').style.display='none';
		    	document.getElementById('divCatAssignButton').style.display = 'block';
		    	document.getElementById('divAssignedCats').style.display='block';
		    	document.getElementById('divProgramsAssignButton').style.display = 'none';
		    	document.getElementById('divAssignedPrograms').style.display='none';
		    }

			if (val == "3"){
		    	document.getElementById('divProgramsAssignButton').style.display = 'block';
		    	document.getElementById('divAssignedPrograms').style.display='block';
		    	document.getElementById('divCoursesAssignButton').style.display = 'none';
				document.getElementById('divAssignedCourses').style.display='none';
			    document.getElementById('divCatAssignButton').style.display = 'none';
			    document.getElementById('divAssignedCats').style.display='none';
		    }
		
	}
	
	
