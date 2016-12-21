$( document ).ready(function() {
    
    $("#tabs").tabs();
    
    // populate the recent tab when they click on it.
    $( "#tabs-recent-button").click(function(event){
        $('#recent-files').load('recent_files.php');
    });
    
    
    // Handle the drop of files in to the files box
    $( "#repo-file-upload").on('drop', function(e){
        $('#upload-file').removeClass('repo-drop-target');
    });
    $('#repo-file-upload, #repo-file-upload *').on(
        'dragenter',
        function(e) {
            $('#repo-file-upload').addClass('repo-drop-target');
            e.preventDefault();
            e.stopPropagation();
        }
    );
    $('#tabs-upload').on(
        'dragenter',
        function(e) {
            $('#repo-file-upload').removeClass('repo-drop-target');
            e.preventDefault();
            e.stopPropagation();
        }
    );
    
    $( ".repo-date-field" ).datepicker({ dateFormat: "yy-mm-dd" });
    
    $(document).on('click', '.repo-field-add-button', function(event){
       
        // we know the row we are looking for
        var row = $(this).parent().parent();
        
        // see if we are adding or deleting
        if($(this).html() == '-'){
            row.remove();
            return;
        }
        
        // get the parent the
        var newRow = row.clone();
        var newInput = newRow.find('input');
        //var oldInput = row.find('input');
        
        newRow.find('button').html('-');
        newRow.insertAfter(row);
        
        newInput.val('');
        newInput.attr('id', newInput.attr('id') + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1));
                
        //repo.bindAutoComplete(oldInput);
        repo.bindAutoComplete(newInput);
        
        // stop form submit
        event.preventDefault();
        
        
    });
    
    
    /* GOOGLE MAP POPUP */
    $("#repo-map-dialogue").dialog({
      autoOpen: false,
      close: function( event, ui ) { repo_country_check(); }
    });
    $("#repo-map-dialogue-eye").on('click', function(event, ui){

        $("#repo-map-dialogue").dialog('open');
        $("#repo-map-dialogue").dialog( "option", "height", 800);
        $("#repo-map-dialogue").dialog( "option", "width", $(window).width() - 80 );
        $("#map").css( "width", '100%' );
        $("#map").css( "height", '733px' );

        // start in Edinburgh
        var centerOfMap = new google.maps.LatLng(55.9533, -3.1883);
        var zoomLevel = 4;
        
        // if it is already set in the field put it on the map
        var previous = $('#geolocation').val();
        if(previous){
            var ll = previous.split(',');
            centerOfMap = new google.maps.LatLng(ll[0].trim(), ll[1].trim());
        }
        
        // accuracy can override zoom level on a rule of thumb basis
        // would be difficult to do this otherwise not knowing the monitor or area of the world
        var accuracy = $('#geolocation_accuracy_i').val();
        if(accuracy){
            if(accuracy < 10000) zoomLevel = 9;
            if(accuracy < 1000) zoomLevel = 11;
            if(accuracy < 100) zoomLevel = 13;
            if(accuracy < 10) zoomLevel = 15;
            if(accuracy < 1) zoomLevel = 17;
        }

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: zoomLevel,
            center: centerOfMap,
            mapTypeId: 'hybrid'
            
        });
        
        // add the maker in if there is a previous
        // has to be done after we have the map to add it to
        if(previous){
            var marker = new google.maps.Marker({
                   position: centerOfMap, 
                   map: map
            });
            $("#map").data('marker', marker);
        }
        
        google.maps.event.addListener(map, 'click', function(event) {
           
           // change the position of the old marker
           if($("#map").data('marker')){
               $("#map").data('marker').setPosition(event.latLng);
           }else{
               var marker = new google.maps.Marker({
                   position: event.latLng, 
                   map: map
                });
                $("#map").data('marker', marker); 
           }
           
           // work out some accuracy and precision
           var top = map.getBounds().getNorthEast().lat();
           var bottom = map.getBounds().getSouthWest().lat();
           var dist = top - bottom;
           var dist_m = dist * 111 * 1000; // approx 111km to 1 degree latitude
           accuracy = Math.round(dist_m / 100); // guess they can judge 100th of map image height
           
           console.log('top = ' + top);
           console.log('bottom = ' + bottom);
           console.log('distance degrees = ' + dist);
           console.log('distance km = ' + dist_m / 1000);
           console.log('accuracy m = ' + accuracy);
           console.log('precision = ' + (111*1000)/accuracy );
           
           // Roughly speaking you need 6 decimal place precision to do 1m accuarcy 5 to do 10, 4 to do 100, 3, 1000
           var precision = 3; // we always give 3dp precision or it gets silly
           if(accuracy < 10000) precision = 4;
           if(accuracy < 1000) precision = 5;
           if(accuracy < 100) precision = 6;
           if(accuracy < 10) precision = 7;
           if(accuracy < 1) precision = 8;           
           
           // add it to the field
           $('#geolocation').val(event.latLng.lat().toFixed(precision) + ',' + event.latLng.lng().toFixed(precision));
           $('#geolocation_accuracy_i').val(accuracy);
           
        });

        
    });
    
    /* FORM VALIDATION */
    $('#repo-commit-button').on('click', function(event){
    
        var hunkyDory = true;
    
        // work through all the inputs in the form and validate them against their regex
        $(event.currentTarget.form).find('input,select,textarea').each(function(){
            
        //    console.log($(this));

            if($(this).data('repo-regex')){
                var re = new RegExp($(this).data('repo-regex'));
    
                var val = $(this).val();
                
                if(!val.match(re)){
                    $(this).addClass('repo-failed');
                    hunkyDory = false;
                }else{
                    $(this).removeClass('repo-failed');                    
                }
            }
            
        });
        
        if(!hunkyDory){
            event.preventDefault();
            alert('Please review highlighted fields and documentation');
        }
    
    });

    // trigger upload when a file is selected
    $(document).on('change', '#repo-file-upload', function(event){
        $('#repo-file-upload-form').submit();
    });
    
    // watch for changes in the coordinates and update the country
    $(document).on('change', '#geolocation', function(event){
        repo_country_check();
    });
    
    $(document).on('change', '#country_iso', function(event){
        repo_country_check();
    });
    
    // check country when the page loads - may have coords in it
    repo_country_check();
    
    // disable fields if there is no file present
    if($('#repo-file-absent').length > 0){
        $('.repo-needs-file').prop('disabled', true);
    }else{
        $('.repo-needs-file').prop('disabled', false);
    }
    
    

}); // end doc ready

var repo_country_check = function(){
    
    var coords = $('#geolocation').val();
    if(!coords){
        // if there are no coords set make the selects default colours
        $("#country_iso").css('color', 'black');
        $("#country_iso").find('option').css('color', 'black');
        $("#geolocation_accuracy_i").val('');
    }else{
        var json = $.getJSON('get_country.php?coords=' + coords, function(data){
            if(data.country_iso){
                $("#country_iso").val(data.country_iso);
                $("#country_iso").css('color', 'green');
                $("#country_iso").find('option').css('color', 'gray');
                $("#country_iso").find('option:selected').css('color', 'green');
            }
            //console.log(data);
        });
    }
    
}