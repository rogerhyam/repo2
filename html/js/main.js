$( document ).ready(function() {
    
    console.log("page loaded");
    
    $('#repo-input-q').on('keyup change', function(){
        $('#repo-input-start').val(0);
    });    

    
});

