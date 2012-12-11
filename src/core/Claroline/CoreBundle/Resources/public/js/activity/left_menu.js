(function(){
    var currentStep = 1;
    var currentItem = 1;

    $('#item-'+1).css('font-weight', 'bold');
    var totalSteps = document.getElementById('twig-attributes').getAttribute('data-total-steps');

    $('#progress-bar').html(currentStep+'/'+totalSteps);

    $('.icon-arrow-right').live('click', function(){
        currentItem++;
        loadRightFrame(currentItem);
    });

    $('.icon-arrow-left').live('click', function(){
        currentItem--;
        loadRightFrame(currentItem);
    });

    var loadRightFrame = function(item){
        currentItem = item;
//        $('#progress-bar').html(currentStep+'/'+totalSteps);
        $('#item-'+item).css('font-weight', 'bold');
        var route =  $('#item-'+item).attr('href');
        window.parent.document.getElementById('right-frame').src = route;
    }
})();



