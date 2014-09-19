websiteApp.controller('uploadController', ['$scope', 'fileReader', 'UtilityFunctions', function($scope, fileReader, UtilityFunctions){
    this.getFile = function(file, imageSrcVar){
        fileReader.readAsDataUrl(file, $scope)
            .then(function(result) {
                UtilityFunctions.deepSetValue($scope, imageSrcVar, result);
            });
    };
}]);
