$(document).ready(function() {
    $(document).find('table').find('tr').find('td').find('img').each(function() {
        resize($(this));
    });
    $(document).find('table').find('tr').find('td').find('iframe').each(function() {
       resize($(this));
    });
    function resize(element) {
        var maxWidth = 20;
        var maxHeight = 20;
        var ratio = 0;
        var width = element.width();
        var height = element.height();
 
        if(width > maxWidth){
            ratio = maxWidth / width;
            element.css("width", maxWidth);
            element.css("height", height * ratio);
            height = height * ratio;
        }
        var width = element.width();
        var height = element.height();
        if(height > maxHeight){
            ratio = maxHeight / height;
            element.css("height", maxHeight);
            element.css("width", width * ratio);
            width = width * ratio;
        }
    }
});


