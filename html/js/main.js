$( document ).ready(function() {
    
    console.log("page loaded");
    
    $('#repo-input-q').on('keyup change', function(){
        $('#repo-input-start').val(0);
    });    

    $('.repo-search-result-top').on('click', function(){

        // if we have already loaded the body just show/hide it.
        if($(this).parent().find('.repo-search-result-bottom').length > 0){
            $(this).parent().find('.repo-search-result-bottom').toggle('slow');
            return;
        }
 
        var docId = $(this).parent().data('repo-doc-id');
        
        var uri = 'search_result_body.php?id=' + encodeURIComponent(docId);
        
        $(this).parent().find('.repo-bottom-placeholder').load(uri, function(){
            $(this).parent().find('.repo-search-result-bottom').show('slow');
        });
        

    });
    
    // follow links to items in list
    $('.repo-search-result').on('click', '.repo-search-result-bottom .repo-item-list li', function() {
        window.location.href = $(this).data('repo-doc-uri');
    });
    
    // display the debug code
    $('.repo-search-result').on('click', '.repo-search-result-bottom-footer', function() {
        $(this).find('pre').show();
    }); 
    
    
    /* populate image placeholders */
    $('.repo-image-placeholder').each(function(){
        
        var placeholder = $(this);
        var imageKind = placeholder.data('repo-image-kind');
        var docId = placeholder.data('repo-doc-id');
        var imagesServiceUri = 'images_service.php?kind=' + imageKind + '&id=' + encodeURIComponent(docId);
        
        if(placeholder.data('repo-image-height')) placeholder.css('height', placeholder.data('repo-image-height') + 'px' );
        
        // load it into the placeholder
        placeholder.load(imagesServiceUri, function(){
            
            var wrapper = placeholder.find('.repo-image-wrapper').first();
        
            if(placeholder.data('repo-image-height')) wrapper.css('min-height', placeholder.data('repo-image-height') + 'px' );
            
            // when it is loaded we look to see how many images there are.
            var numberOfImages = placeholder.find('img').length;
            console.log(numberOfImages);
            
            // if no images hide it.
            if(numberOfImages == 0){
               // placeholder.hide();
                return;
            }
            
            // if one image make it visible
            if(numberOfImages == 1){
                placeholder.find('img').show();
                return;
            }
            
            // show the first in the stack
            wrapper.find('img').first().fadeIn();

            // we have multiple images so animate them as a slideshow
            setInterval(function(){
                
                // get the last image
                var last = wrapper.find('img').last();
                        
                // make sure it is hidden
                last.fadeOut();

                // move it to the top of the stack
                last.prependTo(wrapper);
                
                // show it
                last.fadeIn('slow');
                last.next().fadeOut('slow');
                
            }, 2000);
            
        });
        

        
        
    }); 

    $('.repo-image-wrapper img').show('slow');
    
});

