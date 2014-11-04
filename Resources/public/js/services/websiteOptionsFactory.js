'use strict';
(function () {
    angular.module('websiteApp').factory('WebsiteOptions', ['$http', '$q', '$upload', '$sce', 'UtilityFunctions', function($http, $q, $upload, $sce, UtilityFunctions){
        function  WebsiteOptions (websiteOptions) {
            //General Options
            this.bgColor = websiteOptions.bgColor;
            this.bgImage = websiteOptions.bgImage;
            this.bgPosition = websiteOptions.bgPosition;
            this.bgRepeat = websiteOptions.bgRepeat;
            this.cssCode = websiteOptions.cssCode;
            this.analyticsProvider = (websiteOptions.analyticsProvider!=null && websiteOptions.analyticsProvider!='')?websiteOptions.analyticsProvider:'none';
            this.analyticsAccountId = websiteOptions.analyticsAccountId;
            this.copyrightEnabled = websiteOptions.copyrightEnabled;
            this.copyrightText = websiteOptions.copyrightText;
            this.totalWidth = (websiteOptions.totalWidth==0||websiteOptions.totalWidth==null)?800:websiteOptions.totalWidth;
            this.isFullScreen = websiteOptions.totalWidth==0||websiteOptions.totalWidth==null;

            //Menu options
            this.menuOrientation = websiteOptions.menuOrientation;
            this.menuBorderColor = websiteOptions.menuBorderColor;
            this.menuWidth = websiteOptions.menuWidth;
            this.menuFontSize = websiteOptions.menuFontSize;
            this.menuFontFamily = websiteOptions.menuFontFamily;
            this.menuFontStyle = websiteOptions.menuFontStyle;
            this.menuFontWeight = websiteOptions.menuFontWeight;
            this.menuFontColor = websiteOptions.menuFontColor;
            this.menuBgColor = websiteOptions.menuBgColor;
            this.menuHoverColor = websiteOptions.menuHoverColor;
            this.sectionBgColor = websiteOptions.sectionBgColor;
            this.sectionFontColor = websiteOptions.sectionFontColor;

            //Banner options
            this.bannerEnabled = websiteOptions.bannerEnabled;
            this.bannerHeight = websiteOptions.bannerHeight;
            this.bannerBgColor = websiteOptions.bannerBgColor;
            this.bannerBgImage = websiteOptions.bannerBgImage;
            this.bannerBgPosition = websiteOptions.bannerBgPosition;
            this.bannerBgRepeat = websiteOptions.bannerBgRepeat;
            this.bannerText = websiteOptions.bannerText;
            this.bannerEditorActive = false;

            //Footer options
            this.footerEnabled = websiteOptions.footerEnabled;
            this.footerHeight = websiteOptions.footerHeight;
            this.footerBgColor = websiteOptions.footerBgColor;
            this.footerBgImage = websiteOptions.footerBgImage;
            this.footerBgPosition = websiteOptions.footerBgPosition;
            this.footerBgRepeat = websiteOptions.footerBgRepeat;
            this.footerText = websiteOptions.footerText;
            this.footerEditorActive = false;
            this.temp = {
                bgImagePath : null,
                bannerBgImagePath: null,
                footerBgImagePath: null
            }
        }
        WebsiteOptions.prototype.getImageStyleText = function(imageStr) {
            if (UtilityFunctions.isNotBlank(this[imageStr])) {
                var imageURL = this[imageStr];
                if (!UtilityFunctions.validURL(imageURL)) {
                    imageURL = window.basePath+imageURL;
                }
                return 'url("'+imageURL+'")';
            } else {
                return 'none';
            }
        }
        WebsiteOptions.prototype.saveOptions = function() {
            return $http.put(Routing.generate('icap_website_options_update', {websiteId: websiteId}), this.jsonSerialize())
                .then(function(response) {
                   if(typeof response.data === 'object'){
                       return response.data;
                   } else {
                       return $q.reject(response.data);
                   }
                }, function(response) {
                    return $q.reject(response.data);
                });
        }
        WebsiteOptions.prototype.proceedImageUpload = function($files, imageStr) {
            var options = this;
            return $upload.upload({
                url : Routing.generate('icap_website_options_image_upload', {websiteId: websiteId, imageStr: imageStr}),
                method: 'POST',
                fileFormDataName: 'imageFile',
                file: $files[0]
            }).success(function(response) {
                options[imageStr] = response[imageStr];
                return response;
            }).error(function(response){
                return $q.reject(response);
            });
        }
        WebsiteOptions.prototype.proceedImagePathUpdate = function(newPath, imageStr) {
            var options = this;
            return $http.put(Routing.generate('icap_website_options_image_update', {websiteId: websiteId, imageStr: imageStr}), {"newPath" : newPath})
                .then(function(response) {
                    if(typeof response.data === 'object'){
                        options[imageStr] = response.data[imageStr];
                        return response;
                    } else {
                        return $q.reject(response);
                    }
                }, function(response) {
                   return $q.reject(response);
                });
        }
        WebsiteOptions.prototype.toggleBold = function() {
            if (this.menuFontWeight == 'bold') this.menuFontWeight = 'normal';
            else this.menuFontWeight = 'bold';
        }
        WebsiteOptions.prototype.toggleItalic = function() {
            if (this.menuFontStyle == 'italic') this.menuFontStyle = 'normal';
            else this.menuFontStyle = 'italic';
        }
        WebsiteOptions.prototype.toggleFullScreen = function() {
            if (this.isFullScreen) this.isFullScreen = false;
            else this.isFullScreen = true;
        }
        WebsiteOptions.prototype.toggleBannerEnabled = function() {
            if (this.bannerEnabled) this.bannerEnabled = false;
            else this.bannerEnabled = true;
        }
        WebsiteOptions.prototype.toggleFooterEnabled = function() {
            if (this.footerEnabled) this.footerEnabled = false;
            else this.footerEnabled = true;
        }
        WebsiteOptions.prototype.toggleCopyrightEnabled = function() {
            if (this.copyrightEnabled) this.copyrightEnabled = false;
            else this.copyrightEnabled = true;
        }
        WebsiteOptions.prototype.isBgPositionButtonActive = function(bgStr, position) {
            var currentPosition = this[bgStr+'Position'];
            var currentRepeat = this[bgStr+'Repeat'];
            if (position == currentPosition || currentRepeat == 'repeat') return true;
            else if (currentRepeat == 'repeat-x') {
                var positionArray = position.split(" ");
                var currentPositionArray = currentPosition.split(" ");
                if (positionArray[1]==currentPositionArray[1]) return true;
            } else if (currentRepeat == 'repeat-y'){
                var positionArray = position.split(" ");
                var currentPositionArray = currentPosition.split(" ");
                if (positionArray[0]==currentPositionArray[0]) return true;
            }
            return false;
        }
        WebsiteOptions.prototype.deliberatelyTrustSnippet = function(strVar) {
            return $sce.trustAsHtml(this[strVar]);
        }

        WebsiteOptions.prototype.jsonSerialize = function() {
            return {
                bgColor : this.bgColor,
                bgPosition : this.bgPosition,
                bgRepeat : this.bgRepeat,
                cssCode : this.cssCode,
                analyticsProvider : this.analyticsProvider,
                analyticsAccountId : this.analyticsAccountId,
                copyrightEnabled : this.copyrightEnabled,
                copyrightText : this.copyrightText,
                totalWidth : this.isFullScreen?0:this.totalWidth,
                menuOrientation : this.menuOrientation,
                menuWidth : this.menuWidth,
                menuFontSize : this.menuFontSize,
                menuFontFamily : this.menuFontFamily,
                menuFontStyle : this.menuFontStyle,
                menuFontWeight : this.menuFontWeight,
                menuFontColor : this.menuFontColor,
                menuBgColor : this.menuBgColor,
                menuBorderColor: this.menuBorderColor,
                menuHoverColor : this.menuHoverColor,
                sectionBgColor : this.sectionBgColor,
                sectionFontColor : this.sectionFontColor,
                bannerEnabled : this.bannerEnabled,
                bannerHeight : this.bannerHeight,
                bannerBgColor : this.bannerBgColor,
                bannerBgPosition : this.bannerBgPosition,
                bannerBgRepeat : this.bannerBgRepeat,
                bannerText : this.bannerText,
                footerEnabled : this.footerEnabled,
                footerHeight : this.footerHeight,
                footerBgColor : this.footerBgColor,
                footerBgPosition : this.footerBgPosition,
                footerBgRepeat : this.footerBgRepeat,
                footerText : this.footerText
            }
        }

        return WebsiteOptions;
    }]);
})();