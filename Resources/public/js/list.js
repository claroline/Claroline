$(function(){
    var locationhash = window.location.hash;
    if (locationhash.substr(0,2) == "#!") {
        $("a[href='#" + locationhash.substr(2) + "']").tab("show");
    }
});