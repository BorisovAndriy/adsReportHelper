$(document).ready(function(){
    // Анімація фокусу для інпутів
    $('.input-3d').on('focus', function() {
        $(this).css('transform', 'scale(1.05)');
    }).on('blur', function() {
        $(this).css('transform', 'scale(1)');
    });

    // Ефект натискання на кнопку
    $('.btn-3d').on('mousedown', function(){
        $(this).css('transform', 'scale(0.95)');
    }).on('mouseup', function(){
        $(this).css('transform', 'scale(1)');
    });
});

