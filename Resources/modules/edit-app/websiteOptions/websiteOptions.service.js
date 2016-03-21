(function () {
  'use strict';

  angular
    .module('app')
    .factory('websiteOptions', websiteOptions);

  websiteOptions.$inject = [ '$http', '$q', 'Upload', '$sce', 'utilityFunctions', 'website.data' ];

  function websiteOptions($http, $q, Upload, $sce, utilityFunctions, websiteData) {
    var extendedOptions = {
      bannerEditorActive: false,
      footerEditorActive: false,
      isFullScreen: websiteData.options.totalWidth == 0 || websiteData.options.totalWidth == null,
      temp: {
        bgImagePath: null,
        bannerBgImagePath: null,
        footerBgImagePath: null,
        bgImageChoice: 'upload',
        bannerBgImageChoice: 'upload',
        footerBgImageChoice: 'upload',
        bgImageRepeat: {x: false, y: false},
        bannerBgImageRepeat: {x: false, y: false},
        footerBgImageRepeat: {x: false, y: false}
      },
      getImageStyleText: getImageStyleText,
      proceedImageUpload: proceedImageUpload,
      proceedImagePathUpdate: proceedImagePathUpdate,
      toggleBold: toggleBold,
      toggleItalic: toggleItalic,
      toggleFullScreen: toggleFullScreen,
      repeatChanged: repeatChanged,
      isBgPositionButtonActive: isBgPositionButtonActive,
      deliberatelyTrustSnippet: deliberatelyTrustSnippet,
      saveOptions: saveOptions
    };
    var service = angular.extend({}, websiteData.options, extendedOptions);

    init();

    return service;
    ////////////////////

    function init() {
      if (service.analyticsProvider == null || service.analyticsProvider == '') service.analyticsProvider = 'none';
      if (service.totalWidth == 0 || service.totalWidth == null) service.totalWidth = 800;
      initializeRepeatVars('bg');
      initializeRepeatVars('bannerBg');
      initializeRepeatVars('footerBg');
    }

    function saveOptions() {
      return $http.put(Routing.generate('icap_website_options_update', {websiteId: websiteData.id}), jsonSerialize())
        .then(function (response) {
          if (typeof response.data === 'object') {
            return response.data;
          } else {
            return $q.reject(response.data);
          }
        }, function (response) {
          return $q.reject(response.data);
        });
    }

    function getImageStyleText(imageStr) {
      if (utilityFunctions.isNotBlank(this[ imageStr ])) {
        var imageURL = this[ imageStr ];
        if (!utilityFunctions.validURL(imageURL)) {
          imageURL = websiteData.basePath + imageURL;
        }
        return 'url("' + imageURL + '")';
      } else {
        return 'none';
      }
    }

    function proceedImageUpload($file, imageStr) {
      var options = this;
      return Upload.upload({
        url: Routing.generate('icap_website_options_image_upload', {websiteId: websiteData.id, imageStr: imageStr}),
        method: 'POST',
        data: {'imageFile': $file}
      }).success(function (response) {
        options[ imageStr ] = response[ imageStr ];
        return response;
      }).error(function (response) {
        return $q.reject(response);
      });
    }

    function proceedImagePathUpdate(newPath, imageStr) {
      var options = this;
      return $http.put(Routing.generate('icap_website_options_image_update', {
        websiteId: websiteData.id,
        imageStr: imageStr
      }), {"newPath": newPath})
        .then(function (response) {
          if (typeof response.data === 'object') {
            options[ imageStr ] = response.data[ imageStr ];
            return response;
          } else {
            return $q.reject(response);
          }
        }, function (response) {
          return $q.reject(response);
        });
    }

    function toggleBold() {
      if (service.menuFontWeight == 'bold') service.menuFontWeight = 'normal';
      else service.menuFontWeight = 'bold';
    }

    function toggleItalic() {
      if (service.menuFontStyle == 'italic') service.menuFontStyle = 'normal';
      else service.menuFontStyle = 'italic';
    }

    function toggleFullScreen() {
      if (service.isFullScreen) service.isFullScreen = false;
      else service.isFullScreen = true;
    }

    function initializeRepeatVars(str) {
      var repeat = service[ str + "Repeat" ];

      if (repeat == "repeat") {
        service.temp[ str + "ImageRepeat" ][ "x" ] = true;
        service.temp[ str + "ImageRepeat" ][ "y" ] = true;
      } else if (repeat == "repeat-x") {
        service.temp[ str + "ImageRepeat" ][ "x" ] = true;
        service.temp[ str + "ImageRepeat" ][ "y" ] = false;
      } else if (repeat == "repeat-y") {
        service.temp[ str + "ImageRepeat" ][ "x" ] = false;
        service.temp[ str + "ImageRepeat" ][ "y" ] = true;
      }
    }

    function repeatChanged(str) {
      var x = service.temp[ str + "ImageRepeat" ][ "x" ];
      var y = service.temp[ str + "ImageRepeat" ][ "y" ];

      if (x && y) {
        service[ str + "Repeat" ] = 'repeat';
      } else if (!x && !y) {
        service[ str + "Repeat" ] = 'no-repeat';
      } else if (x && !y) {
        service[ str + "Repeat" ] = 'repeat-x';
      } else if (!x && y) {
        service[ str + "Repeat" ] = 'repeat-y';
      }
    }

    function isBgPositionButtonActive(bgStr, position) {
      var currentPosition = service[ bgStr + 'Position' ];
      var currentRepeat = service[ bgStr + 'Repeat' ];
      if (position == currentPosition || currentRepeat == 'repeat') return true;
      else if (currentRepeat == 'repeat-x') {
        var positionArray = position.split(" ");
        var currentPositionArray = currentPosition.split(" ");
        if (positionArray[ 1 ] == currentPositionArray[ 1 ]) return true;
      } else if (currentRepeat == 'repeat-y') {
        var positionArray = position.split(" ");
        var currentPositionArray = currentPosition.split(" ");
        if (positionArray[ 0 ] == currentPositionArray[ 0 ]) return true;
      }
      return false;
    }

    function deliberatelyTrustSnippet(strVar) {
      return $sce.trustAsHtml(service[ strVar ]);
    }

    function jsonSerialize() {
      return {
        bgColor: service.bgColor,
        bgPosition: service.bgPosition,
        bgRepeat: service.bgRepeat,
        cssCode: service.cssCode,
        analyticsProvider: service.analyticsProvider,
        analyticsAccountId: service.analyticsAccountId,
        copyrightEnabled: service.copyrightEnabled,
        copyrightText: service.copyrightText,
        totalWidth: service.isFullScreen ? 0 : service.totalWidth,
        menuOrientation: service.menuOrientation,
        menuWidth: service.menuWidth,
        menuFontSize: service.menuFontSize,
        menuFontFamily: service.menuFontFamily,
        menuFontStyle: service.menuFontStyle,
        menuFontWeight: service.menuFontWeight,
        menuFontColor: service.menuFontColor,
        menuBgColor: service.menuBgColor,
        menuBorderColor: service.menuBorderColor,
        menuHoverColor: service.menuHoverColor,
        sectionBgColor: service.sectionBgColor,
        sectionFontColor: service.sectionFontColor,
        bannerEnabled: service.bannerEnabled,
        bannerHeight: service.bannerHeight,
        bannerBgColor: service.bannerBgColor,
        bannerBgPosition: service.bannerBgPosition,
        bannerBgRepeat: service.bannerBgRepeat,
        bannerText: service.bannerText,
        footerEnabled: service.footerEnabled,
        footerHeight: service.footerHeight,
        footerBgColor: service.footerBgColor,
        footerBgPosition: service.footerBgPosition,
        footerBgRepeat: service.footerBgRepeat,
        footerText: service.footerText
      }
    }

  };
})();