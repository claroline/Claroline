var websiteApp = angular.module('websiteApp', ['resizerApp', 'uploaderApp', 'utilitiesApp', 'treeApp', 'ui.tree']);


websiteApp.controller('initController', function($scope, WebsiteOptions, Tree){
	$scope.options = new WebsiteOptions();
	$scope.optionsWindow = 'general';
	
	//Style of page elements
	$scope.bannerStyle = function(){
		return {height:$scope.options.bannerHeight+'px', 'background-color': $scope.options.bannerBackground.color, 'background-position' : $scope.options.bannerBackground.position,'background-repeat' : $scope.options.bannerBackground.repeat, 'background-image': 'url("'+$scope.options.bannerBackground.tmpImage+'")'};
	}
	$scope.bannerResizerStyle = function(){
		return {'background-color': $scope.options.bannerBackground.color};
	}
	
	$scope.menuHorizontalStyle = function(){
		return {'background-color' : $scope.options.menuBgColor, 'color' : $scope.options.menuFontColor, 'font-size' : $scope.options.menuFontSize+'px', 'font-family' : $scope.options.menuFontFamily}
	};
	$scope.menuVerticalStyle = function(){
		return {'min-height':$scope.contentHeight+'px', 'width':$scope.options.menuVerticalWidth+'px', 'background-color': $scope.options.menuBgColor, 'color': $scope.options.menuFontColor, 'font-size' : $scope.options.menuFontSize+'px', 'font-family' : $scope.options.menuFontFamily}
	};
	$scope.menuResizerStyle = function(){
		return {height:$scope.contentHeight+'px', 'background-color': $scope.options.menuBgColor};
	};	
	$scope.containerStyle = function(){
		return {'background-color': $scope.options.background.color, 'background-image':'url("'+$scope.options.background.tmpImage+'")', 'background-position' : $scope.options.background.position,'background-repeat' : $scope.options.background.repeat};
	};
	
	//Style of page elements
	$scope.footerStyle = function(){
		return {height: $scope.options.footerHeight+'px', 'background-color': $scope.options.footerBackground.color, 'background-position' : $scope.options.footerBackground.position,'background-repeat' : $scope.options.footerBackground.repeat, 'background-image': 'url("'+$scope.options.footerBackground.tmpImage+'")'};
	}
	$scope.footerResizerStyle = function(){
		return {'background-color': $scope.options.footerBackground.color};
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
});

websiteApp.factory('WebsiteOptions', function(BackgroundOptions){
	function  WebsiteOptions () {
		//General Options
		this.background = new BackgroundOptions();
		this.css = null;
		this.analyticsPlatform = null;
		this.analyticsId = null;
		this.copyright = false;
		
		//Menu options
		this.menuOrientation = 'vertical';
		this.menuVerticalWidth = 100;
		this.menuFontSize = 12;
		this.menuFontFamily = 'inherit';
		this.menuFontColor = '#ffffff';
		this.menuBgColor = '#777777';
		this.menuHoverColor = '#999999';
		this.menuSectionColor = '#444444';
		
		//Banner options
		this.banner = true;
		this.bannerHeight = 70;
		this.bannerBackground = new BackgroundOptions();
		this.bannerHtml = null;
		
		//Footer options
		this.footer = true;
		this.footerHeight = 70;
		this.footerBackground = new BackgroundOptions();
		this.footerHtml = null;
	}
	
	return WebsiteOptions;
});

websiteApp.factory('BackgroundOptions', function(){
	function BackgroundOptions () {
		this.color = '#ffffff';
		this.image = null;
		this.tmpImage = "";
		this.position = 'left top';
		this.repeat = 'no-repeat';
	}
	
	return BackgroundOptions;
});

websiteApp.controller('uploadController', function($scope, fileReader, UtilityFunctions){
	this.getFile = function(file, imageSrcVar){
		fileReader.readAsDataUrl(file, $scope)
                  .then(function(result) {
                    UtilityFunctions.deepSetValue($scope, imageSrcVar, result);
                  });
	};
});

websiteApp.directive("ngFileSelect",function(){
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
