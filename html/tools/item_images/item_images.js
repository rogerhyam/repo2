$( document ).ready(function() {
    
    $("#tabs").tabs();
    $( "button", "#formPanel" ).button();
    $( "button", ".imagePreview" ).button();
    $( "button", "#loginDialogue" ).button();
    $( ".dateField" ).datepicker({ dateFormat: "yy-mm-dd" });
    
    
    $( "#accessionNumberInput" ).autocomplete({
        source: "/tools/item_images/suggest.php",
        minLength: 2,
        select: function( event, ui ) {
            // set the value before calling the lookup
            $( "#accessionNumberInput" ).val(ui.item.value);
            lookupAccessionNumber();
            //event.preventDefault();
        }
    });
    
    $("#accessionNumberLookupButton").click(function(event){
        event.preventDefault();
        lookupAccessionNumber();
        return false;
    });
    
    $('#uploadButton').click(function(event){
        
        // check they have added files before they try and upload
        var filesToGo = $('#fileUploadField').val();
        
        if(filesToGo.length < 1){
            alert("You must select some files to upload first.");
            event.preventDefault();
            return false;
        } 
        
    });
    
    $("#commitButton").click(function(event){
	    event.preventDefault();
	    
	    if(!okToSubmit())return;
	    
	    var uri = 'commit.php'
	        + "?id=" + $("#accessionNumberInput") .val()
	        + "&photographer=" + $("#photographerInput") .val();
	    
	    $.getJSON(uri, function(response, textStatus){

             console.log(textStatus);
             
             if(response.errors == false){
                 alert(response.message);
                 window.location = 'index.php?accessionNumberInput=' + $("#accessionNumberInput") .val() + "&photographerInput=" + $("#photographerInput") .val(); 
             }else{
                 alert(response.message);
             }

         });
         
	});
	
	// if we have just loaded and there is a value in the input field then
	// we should look it up.
	if($("#accessionNumberInput").val()){
	    lookupAccessionNumber();
	}
	
	$( ".rotateLeftImageButton" ).click(function(event){
	    
	    event.preventDefault();
	    
	    var uri = 'rotate_image.php' + "?direction=LEFT&id=" + event.target.parentNode.parentNode.id.replace('-', '.');
	        
	    $.getJSON(uri, function(response, textStatus){
             
             if(response.errors == false){
                 var img = $('#' + response.message.replace('.', '-') + ' img');
                 img.attr('src', img.attr('src')+'&'+Math.random());
             }else{
                 alert(response.message);
             }

         });
	    
	    
    });
    
	$( ".rotateRightImageButton" ).click(function(event){
	    
	    event.preventDefault();
	    
	    var uri = 'rotate_image.php' + "?direction=RIGHT&id=" + event.target.parentNode.parentNode.id.replace('-', '.');
	        
	    $.getJSON(uri, function(response, textStatus){
             
             if(response.errors == false){
                 var img = $('#' + response.message.replace('.', '-') + ' img');
                 img.attr('src', img.attr('src')+'&'+Math.random());
             }else{
                 alert(response.message);
             }

         });
    });
    
    $( ".deleteImageButton " ).click(function(event){
	    
	    event.preventDefault();
	    
	    var uri = 'delete_image.php' + "?id=" + event.target.parentNode.parentNode.id.replace('-', '.');
	        
	    $.getJSON(uri, function(response, textStatus){
             
             if(response.errors == false){
                 $('#' + response.message.replace('.', '-')).remove();
                 // alert(response.message);
                 // window.location = 'index.php?accessionNumberInput=' + $("#accessionNumberInput") .val() + "&photographerInput=" + $("#photographerInput") .val(); 
             }else{
                 alert(response.message);
             }

         });
    });
    
    
    $( "#tabs-2-button").click(function(event){
        $('#recent-images').load('recent_images.php');
    });

}); // end doc ready

function changeDate(newDate, imageName){
    $.get('change_date.php?image=' + imageName + "&date=" + newDate);
}

function lookupAccessionNumber(){

    // clear the form
    $('#identifierLabel').html('loading ... ');
    $('#speciesNameLabel').html('loading ... ');
    $('#familyLabel').html('loading ... ');
    $('#collectorLabel').html('loading ... ');
    $('#collDateLabel').html('loading ... ');


     var id = $('#accessionNumberInput').val();
     var uri = '/tools/item_images/lookup.php?id=' + id;
     // call the ajax
     $.getJSON(uri, function(data, textStatus){

         console.log(textStatus);

         $('#identifierLabel').html(data.id);
         $('#speciesNameLabel').html(data.ScientificName);
         $('#familyLabel').html(data.Family);
         $('#collectorLabel').html(data.Collector);
         $('#collDateLabel').html(data.LatestDateCollected);

        $('#identifierLabel').html();

     });
}

function okToSubmit(){
    
    if(typeof $('#photographerInput').val() === 'undefined' || $('#photographerInput').val().length < 2 ){
         alert("You must set a photographer's name");
         return false;
     }
  
    if($('#identifierLabel').html().length < 6 ){
        alert("You must set a valid accession number or barcode");
        return false;
    }
    
    if($('.imagePreview').length < 1){
        alert("You must upload some photographs");
        return false;
    }
    
    return true;
    
}

