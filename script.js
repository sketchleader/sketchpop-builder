/**
 * SketchPop Builder Logic
 * Version: 5.1.1
 */
jQuery(document).ready(function($) {
    
    // 1. Show the popup after a 1.5 second delay
    if ($('#skpop-wrapper').length > 0) {
        setTimeout(function() {
            $('#skpop-wrapper').css('display', 'flex').hide().fadeIn(400);
        }, 1500);
    }

    // 2. Logic to close the popup
    function closeSkPop() {
        $('#skpop-wrapper').fadeOut(300);
    }

    // Close on 'X' button click
    $(document).on('click', '.skpop-close-btn', function() {
        closeSkPop();
    });

    // Close on clicking the backdrop (only if Close button exists)
    $(document).on('click', '.skpop-overlay', function() {
        if ($('.skpop-close-btn').length > 0) {
            closeSkPop();
        }
    });

});
