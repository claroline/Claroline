websiteApp.directive("ngFileSelect", function(){
    return {
        controller: 'uploadController',
        link: function($scope, $el, $attrs, ctrl){
            $el.bind("change", function(e){
                var file = (e.srcElement || e.target).files[0];
                ctrl.getFile(file, $attrs.imageSrcVariable);
            });
        }
    }
});
