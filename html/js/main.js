$( document ).ready(function() {
    
    $('#repo-input-q').on('keyup change', function(){
        $('#repo-input-start').val(0);
    });    

    $('.repo-search-result-top').on('click', function(){
        repo.showSearchResultBottom($(this).parent());
    });
    
    // if there is only one row then we open it
    $('#repo-single-result-flag').each(function(){
        $('.repo-search-result').each(function(){
            repo.showSearchResultBottom($(this));
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
    
    
    /* populate image placeholders in the main page*/
    $('.repo-image-placeholder').each(function(){
            repo.loadImages($(this));    
    }); 

});

var repo = {};

repo.showSearchResultBottom = function(searchResult){
    
    // if we have already loaded the body just show/hide it.
    if(searchResult.find('.repo-search-result-bottom').length > 0){
        searchResult.find('.repo-search-result-bottom').toggle('slow');
        return;
    }

    var docId = searchResult.data('repo-doc-id');
    
    var uri = 'search_result_body.php?id=' + encodeURIComponent(docId);
    
    searchResult.find('.repo-bottom-placeholder').load(uri, function(){
        
        // load the images in the new code
        $(this).parent().find('.repo-search-result-bottom .repo-image-placeholder').each(function(){
            repo.loadImages($(this));
        });
        
        // hide any empty sections
        $(this).find('.repo-field-list').each(function(){
            if($(this).find('li').length == 0){
                $(this).hide();
                $(this).prev().hide();
            }

            console.log(this);
        });
        
        // bind to the sharing button
        $('.repo-search-result').on('click', '.repo-sharing-button', function(event) {
            event.preventDefault();
            
            var path = $(this).data('repo-path');
            var days = $(this).data('repo-days');
            
            $.get( "get_sharing_link.php?repo_path=" + path + "&days=" + days, function( data ) {
            
               if(days == -1){
                   var message = 'This link will allow external download. It does not expire.';
               }else{
                   var message = 'This link will allow external download for the next ' + days + ' days'
               }
               prompt(message, data);
            
            });
            
           
        });
        
        // show it
        $(this).parent().find('.repo-search-result-bottom').show('slow');
    
    });
    
    
}

repo.loadImages = function(placeholder){
    
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
    
}

repo.filterChange = function(filterInput){
    
    // if they are filtering on an empty search box then
    // make the search box look for everything and hide the content
    // q=_text_:*&repo_type=hidden
    var form = filterInput.form;
    if(!$('#repo-input-q').val()){
        $('#repo-input-q').val('_text_:*');
        $('#repo-input-repo-type').val('hidden');
    }
    
    // always start of the first page
    $('#repo-input-start').val('0');
    
    // go for it
    form.submit();
    
}

