(function($){
    
    var pro_options = function(){
        var trigger = $('.cbc-pro-options-trigger'),
            data = $(trigger).data(),
            items = $( data.selector );
            
        $(document).on( 'click', '.cbc-pro-options-trigger', function(e){
            e.preventDefault();
            if( data.visible ){
                $(items).hide(300);
                data.visible = 0;
                $(trigger).text( data.text_off );
            }else{
                $(items).show(300);
                data.visible = 1;
                 $(trigger).text( data.text_on );
            }
        })    
        
    }
    
    var start = function(){
        pro_options();
    }

    $(document).ready(start);
})(jQuery);