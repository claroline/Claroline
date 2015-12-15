(function() {
    'use strict';

    angular
        .module('app')
        .controller('MainController', MainController);

    MainController.$inject = ['websiteOptions', 'websiteTree', 'tinyMceConfig'];

    function MainController(websiteOptions, websiteTree, tinyMceConfig){
        var vm = this;
        vm.options = websiteOptions;
        vm.menu = websiteTree;
        vm.tinymceConfig = tinyMceConfig;
        vm.uploadImage = function($files, imageStr) {
            vm.options.proceedImageUpload($files, imageStr);
        }

        vm.updateImagePath = function(imageStr, isReset) {
            var newPath = null;
            if (!isReset) {
                newPath = vm.options.temp[imageStr+'Path'];
            }
            vm.options.proceedImagePathUpdate(newPath, imageStr);
        }

        //Style of page elements
        vm.bannerStyle = function(){
            return {height:vm.options.bannerHeight+'px', 'background-color': vm.options.bannerBgColor, 'background-position' : vm.options.bannerBgPosition,'background-repeat' : vm.options.bannerBgRepeat, 'background-image': this.options.getImageStyleText('bannerBgImage')};
        }
        vm.menuHorizontalStyle = function(){
            if (vm.options.menuOrientation=='horizontal') {
                return {'background-color' : vm.options.menuBgColor, 'color' : vm.options.menuFontColor, 'font-size' : vm.options.menuFontSize+'px', 'font-family' : vm.options.menuFontFamily}
            } else {
                return {};
            }
        };
        vm.menuVerticalStyle = function(){
            if (vm.options.menuOrientation=='vertical') {
                return {'min-height':vm.contentHeight+'px', 'width':vm.options.menuWidth+'px', 'background-color': vm.options.menuBgColor, 'color': vm.options.menuFontColor, 'font-size' : vm.options.menuFontSize+'px', 'font-family' : vm.options.menuFontFamily}
            } else {
                return {};
            }
        };
        vm.menuButtonStyle = function(node){
            var backgroundColor = vm.options.menuBgColor;
            var fontColor = vm.options.menuFontColor;
            if(node.isSection) {
                backgroundColor = vm.options.sectionBgColor;
                fontColor = vm.options.sectionFontColor;
            }
            return {'background-color':backgroundColor, 'color':fontColor, 'border-color':vm.options.menuBorderColor};
        }
        vm.menuResizerStyle = function(){
            if (vm.options.menuOrientation=='vertical') {
                return {height:vm.contentHeight+'px'};
            } else {
                return {};
            }
        };
        vm.websitePreviewStyle = function(){
            return {'background-color': vm.options.bgColor, 'background-image':this.options.getImageStyleText('bgImage'), 'background-position' : vm.options.bgPosition,'background-repeat' : vm.options.bgRepeat};
        };
        vm.websitePreviewInnerStyle = function(){
            var style = {};
            if (!vm.options.isFullScreen) {
                style['width'] = vm.options.totalWidth+'px';
                style['margin-left'] = 'auto';
                style['margin-right'] = 'auto';
            }
            return style;
        }
        //Style of page elements
        vm.footerStyle = function(){
            return {height: vm.options.footerHeight+'px', 'background-color': vm.options.footerBgColor, 'background-position' : vm.options.footerBgPosition,'background-repeat' : vm.options.footerBgRepeat, 'background-image': this.options.getImageStyleText('footerBgImage')};
        }
        vm.treeOptions = {
            beforeDrop: function (event) {
                //event.source.itemScope.modelValue.status = event.dest.sortableScope.$parent.column.name;
                var sourceNode = (event.source.nodesScope.$nodeScope!==null)?event.source.nodesScope.$nodeScope.$modelValue:null;
                var sourceIndex = event.source.index;
                var destNode = (event.dest.nodesScope.$nodeScope!==null)?event.dest.nodesScope.$nodeScope.$modelValue:null;
                var destIndex = event.dest.index;
                var currentNode = event.source.nodeScope.$modelValue;

                if (sourceNode != destNode || sourceIndex != destIndex) {
                    vm.menu.movePageNode(currentNode, sourceNode, destNode, sourceIndex, destIndex);
                }

                //return false;
            }
        };

        vm.confirmNodeDelete = function(node) {
            vm.menu.confirmNodeDelete(node);
        };
        vm.removeCurrentPageNode = function() {
            vm.menu.removeCurrentPageNode();
        };
        vm.cancelPageNodeDelete = function() {
            vm.menu.confirmDelete = false;
        };
        vm.createNewPageForm = function(node) {
            vm.menu.createEmptyPageNode(node);
        };
        vm.saveWebsitePage = function() {
            vm.menu.saveCurrentEditPageNode();
        };
        vm.setAsHomepage = function() {
            vm.menu.currentEditPageNode.setHomepage();
        }

        vm.currentClickedItem = vm.menu.root;
        vm.onItemClick = function(event, item) {
            event.preventDefault();
            vm.currentClickedItem = item;
        };

        //Resource picker configuration
        vm.resourcePickerConfig = {
            isPickerMultiSelectAllowed: false,
            callback: function (nodes) {
                angular.forEach(nodes, function (element, index) {
                    var currentPageNode = vm.menu.currentEditPageNode;
                    currentPageNode.resourceNode = index;
                    currentPageNode.title = element[0];
                    currentPageNode.resourceNodeType = element[1];
                    currentPageNode.resourceNodeName = element[0];
                    currentPageNode.resourceNodeWorkspace = null;
                });
            }
        };

        vm.pushMenuOptions = {
            containersToPush: null,
            wrapperClass: 'multilevelpushmenu_wrapper',
            menuInactiveClass: 'multilevelpushmenu_inactive',
            menuWidth: vm.options.menuWidth,
            direction: 'rtl',
            backItemIcon: 'fa fa-angle-left',
            groupIcon: 'fa fa-angle-right',
            backText: 'Back',
            mode: 'cover',
            overlapWidth: 0,
            onItemClick: vm.onItemClick
        };
        vm.flexnavOptions = {
            onItemClick: vm.onItemClick,
            breakpoint: 800
        };
    };
})();