websiteApp.controller('designController', ['$location', '$rootScope', '$scope', '$alert', 'WebsiteOptions','Tree', function($location, $rootScope, $scope, $alert, WebsiteOptions, Tree){
    $scope.options = new WebsiteOptions(window.websiteOptions);
    $scope.optionsWindow = window.optionTabs[0];

    $scope.$on('$locationChangeSuccess', function () {
        var page = $location.url().substr(1);
        if (window.optionTabs.indexOf(page)!=-1) {
            $scope.optionsWindow = page;
        }
    });

    $scope.uploadImage = function($files, imageStr){
        $scope.pageLoaded = false;
        $scope.options.proceedImageUpload($files, imageStr).then(
            function(result) {
                $scope.options[imageStr] = result.data[imageStr];
                $scope.pageLoaded = true;
                $alert({
                    title: 'Holy guacamole!',
                    content: 'Best check yo self, you re not looking too good.',
                    placement: 'top',
                    type: 'success',
                    duration: 3,
                    show: true
                });
            }, function(error) {
                $scope.pageLoaded = true;
                $alert({
                    "title": "Error!!! ",
                    "content": "Error saving options",
                    "type": "danger",
                    placement: 'top',
                    container: 'body',
                    duration: 3,
                    show: true
                });
            }
        )
    }

    $scope.updateImagePath = function(imageStr, isReset) {
        var newPath = null;
        if (!isReset) {
            newPath = $scope.options.temp[imageStr+'Path'];
        }
        $scope.pageLoaded = false;
        $scope.options.proceedImagePathUpdate(newPath, imageStr).then(
            function(result) {
                $scope.options[imageStr] = result.data[imageStr];
                $scope.pageLoaded = true;
                $alert({
                    title: 'Holy guacamole!',
                    content: 'Best check yo self, you re not looking too good.',
                    placement: 'top',
                    type: 'success',
                    duration: 3,
                    show: true
                });
            }, function(error) {
                $scope.pageLoaded = true;
                $alert({
                    "title": "Error!!! ",
                    "content": "Error saving options",
                    "type": "danger",
                    placement: 'top',
                    container: 'body',
                    duration: 3,
                    show: true
                });
            }
        )
    }

    //Style of page elements
    $scope.bannerStyle = function(){
        return {height:$scope.options.bannerHeight+'px', 'background-color': $scope.options.bannerBgColor, 'background-position' : $scope.options.bannerBgPosition,'background-repeat' : $scope.options.bannerBgRepeat, 'background-image': this.options.getImageStyleText('bannerBgImage')};
    }
    $scope.bannerResizerStyle = function(){
        return {'background-color': $scope.options.bannerBgColor};
    }

    $scope.menuHorizontalStyle = function(){
        return {'background-color' : $scope.options.menuBgColor, 'color' : $scope.options.menuFontColor, 'font-size' : $scope.options.menuFontSize+'px', 'font-family' : $scope.options.menuFontFamily}
    };
    $scope.menuVerticalStyle = function(){
        return {'min-height':$scope.contentHeight+'px', 'width':$scope.options.menuWidth+'px', 'background-color': $scope.options.menuBgColor, 'color': $scope.options.menuFontColor, 'font-size' : $scope.options.menuFontSize+'px', 'font-family' : $scope.options.menuFontFamily}
    };
    $scope.menuResizerStyle = function(){
        return {height:$scope.contentHeight+'px', 'background-color': $scope.options.menuBgColor};
    };
    $scope.websitePreviewStyle = function(){
        return {'background-color': $scope.options.bgColor, 'background-image':this.options.getImageStyleText('bgImage'), 'background-position' : $scope.options.bgPosition,'background-repeat' : $scope.options.bgRepeat};
    };
    $scope.websitePreviewInnerStyle = function(){
        var style = {};
        if (!$scope.options.isFullScreen) {
            style['width'] = $scope.options.totalWidth+'px';
            style['margin-left'] = 'auto';
            style['margin-right'] = 'auto';
        }
        return style;
    }

    //Style of page elements
    $scope.footerStyle = function(){
        return {height: $scope.options.footerHeight+'px', 'background-color': $scope.options.footerBgColor, 'background-position' : $scope.options.footerBgPosition,'background-repeat' : $scope.options.footerBgRepeat, 'background-image': this.options.getImageStyleText('footerBgImage')};
    }
    $scope.footerResizerStyle = function(){
        return {'background-color': $scope.options.footerBgColor};
    }

    $scope.sortOptions = {
        //restrict move across columns. move only within column.
        /*accept: function (sourceItemHandleScope, destSortableScope) {
         return sourceItemHandleScope.itemScope.sortableScope.$id !== destSortableScope.$id;
         },*/
        itemMoved: function (event) {
            //event.source.itemScope.modelValue.status = event.dest.sortableScope.$parent.column.name;
            console.log(event);
        },
        orderChanged: function (event) {
            console.log(event);
        },
        containment: '#edit-menu'
    };

    $scope.removeTreeNode = function(scope) {
        $scope.menu.removeNode(scope.node);
        scope.remove();
    };

    $scope.menu = new Tree([], 'menu', false);
    $scope.contentTab = 'view';
    $scope.addNewMenuItem = function(){
        $scope.menu.appendChildToCurrentNode($scope.menu.allNodes.length, $scope.newMenuItem, [], {});
        $scope.newMenuItem = null;
        //$scope.currentMenu = 0;
    }
}]);