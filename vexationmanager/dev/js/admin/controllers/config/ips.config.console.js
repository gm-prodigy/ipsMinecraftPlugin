;( function($, _, undefined){
    "use strict";
    
    $(function() {
        var $text = $('#console1'); 
        $text.scrollTop($text[0].scrollHeight); 
        console.log( "ready!" );
    });


    $(document).ready(function(){

            $("#console1").load();

    });


    ips.controller.register('vexationmanager.admin.vexationmanager.console', {


})
}(jQuery, _));