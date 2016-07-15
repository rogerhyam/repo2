$( document ).ready(function() {
    
    console.log("page loaded");
    
    $('#repo-input-q').on('keyup change', function(){
        $('#repo-input-start').val(0);
    });    

    $('.repo-search-result-top').on('click', function(){
        
        console.log($(this).parent().children('.repo-search-result-bottom').toggle('slow'));
        
        console.log('banana');
    
    });

    
});

