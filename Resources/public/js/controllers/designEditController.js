websiteApp.controller('designEditController', ['$location', '$scope', '$alert', 'WebsiteOptions','Tree', 'GlobalRequestHandler', function($location, $scope, $alert, WebsiteOptions, Tree, RequestHandler){
    $scope.options = new WebsiteOptions(window.websiteOptions);
    $scope.optionsWindow = window.optionTabs[0];

    var requestErrorHandler = function(rejection) {
        var statusCode = rejection.status;
        if(statusCode!=511 && statusCode!=401 && statusCode!=417) statusCode = 500;
        $alert({
            title: Translator.get('icap_website:error'),
            content: Translator.get('icap_website:error_'+statusCode),
            placement: 'top',
            type: 'danger',
            duration: 3,
            show: true
        });
    };
    var requestSuccessHandler = function(response) {
        if(response.config.url.indexOf('.tpl')==-1){
            $alert({
                title: Translator.get('icap_website:success'),
                content: Translator.get('icap_website:success_message'),
                placement: 'top',
                type: 'success',
                duration: 3,
                show: true
            });
        }
    };
    RequestHandler.onRequestSuccess($scope, requestSuccessHandler);
    RequestHandler.onRequestError($scope, requestErrorHandler);

    $scope.$on('$locationChangeSuccess', function () {
        var page = $location.url().substr(1);
        if (window.optionTabs.indexOf(page)!=-1) {
            $scope.optionsWindow = page;
        }
    });

    $scope.uploadImage = function($files, imageStr) {
        $scope.options.proceedImageUpload($files, imageStr);
    }

    $scope.updateImagePath = function(imageStr, isReset) {
        var newPath = null;
        if (!isReset) {
            newPath = $scope.options.temp[imageStr+'Path'];
        }
        $scope.options.proceedImagePathUpdate(newPath, imageStr);
    }

    //Style of page elements
    $scope.bannerStyle = function(){
        return {height:$scope.options.bannerHeight+'px', 'background-color': $scope.options.bannerBgColor, 'background-position' : $scope.options.bannerBgPosition,'background-repeat' : $scope.options.bannerBgRepeat, 'background-image': this.options.getImageStyleText('bannerBgImage')};
    }
    $scope.bannerResizerStyle = function(){
        return {'background-color': $scope.options.bannerBgColor};
    };
    $scope.menuHorizontalStyle = function(){
        if ($scope.options.menuOrientation=='horizontal') {
            return {'background-color' : $scope.options.menuBgColor, 'color' : $scope.options.menuFontColor, 'font-size' : $scope.options.menuFontSize+'px', 'font-family' : $scope.options.menuFontFamily}
        } else {
            return {};
        }
    };
    $scope.menuVerticalStyle = function(){
        if ($scope.options.menuOrientation=='vertical') {
            return {'min-height':$scope.contentHeight+'px', 'width':$scope.options.menuWidth+'px', 'background-color': $scope.options.menuBgColor, 'color': $scope.options.menuFontColor, 'font-size' : $scope.options.menuFontSize+'px', 'font-family' : $scope.options.menuFontFamily}
        } else {
            return {};
        }
    };
    $scope.menuButtonStyle = function(node){
        var backgroundColor = $scope.options.menuBgColor;
        var fontColor = $scope.options.menuFontColor;
        if(node.isSection) {
            backgroundColor = $scope.options.sectionBgColor;
            fontColor = $scope.options.sectionFontColor;
        }
        return {'background-color':backgroundColor, 'color':fontColor, 'border-color':$scope.options.menuBorderColor};
    }
    $scope.menuResizerStyle = function(){
        if ($scope.options.menuOrientation=='vertical') {
            return {height:$scope.contentHeight+'px', 'background-color': $scope.options.menuBgColor};
        } else {
            return {};
        }
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

    $scope.treeOptions = {
        //restrict move across columns. move only within column.
        /*accept: function (sourceItemHandleScope, destSortableScope) {
         return sourceItemHandleScope.itemScope.sortableScope.$id !== destSortableScope.$id;
         },*/
        beforeDrop: function (event) {
            //event.source.itemScope.modelValue.status = event.dest.sortableScope.$parent.column.name;
            var sourceNode = angular.isDefined(event.source.nodesScope.node)?event.source.nodesScope.node:null;
            var sourceIndex = event.source.index;
            var destNode = angular.isDefined(event.dest.nodeScope.node)?event.dest.nodeScope.node:null;
            var destIndex = event.dest.index;
            var currentNode = event.source.nodeScope.node;

            if (sourceNode != destNode || sourceIndex != destIndex) {
                $scope.menu.movePageNode(currentNode, sourceNode, destNode, sourceIndex, destIndex);
            }

            return false;
        }
    };

    $scope.confirmNodeDelete = function(node) {
        $scope.menu.confirmNodeDelete(node);
    };
    $scope.removeCurrentPageNode = function() {
        $scope.menu.removeCurrentPageNode();
    };
    $scope.cancelPageNodeDelete = function() {
        $scope.menu.confirmDelete = false;
    };
    $scope.createNewPageForm = function(node) {
        $scope.menu.createEmptyPageNode(node);
    };
    $scope.saveWebsitePage = function() {
        $scope.menu.saveCurrentEditPageNode();
    };

    $scope.menu = new Tree(window.pages[0]);
    $scope.currentClickedItem = $scope.menu.root;
    $scope.onItemClick = function(event, item) {
        event.preventDefault();
        $scope.currentClickedItem = item;
    };

    //Resource picker configuration
    $scope.resourcePickerConfig = {
        isPickerMultiSelectAllowed: false,
        callback: function (nodes) {
            angular.forEach(nodes, function (element, index) {
                var currentPageNode = $scope.menu.currentEditPageNode;
                currentPageNode.resourceNode = index;
                currentPageNode.title = element[0];
                currentPageNode.resourceNodeType = element[1];
            });
            $scope.$apply();
        }
    };

    $scope.pushMenuOptions = {
        containersToPush: null,
        wrapperClass: 'multilevelpushmenu_wrapper',
        menuInactiveClass: 'multilevelpushmenu_inactive',
        menuWidth: $scope.options.menuWidth,
        direction: 'rtl',
        backItemIcon: 'fa fa-angle-left',
        groupIcon: 'fa fa-angle-right',
        backText: 'Back',
        mode: 'cover',
        overlapWidth: 0,
        onItemClick: $scope.onItemClick
    };
    $scope.flexnavOptions = {
        onItemClick: $scope.onItemClick
    };
    $scope.breakPoint = 800;
}]);