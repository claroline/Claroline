/**
 * Trust as HTML filter
 */
(function () {
  'use strict';

  angular.module('UtilsModule').filter('trustAsHtml', trustAsHtml);

  trustAsHtml.$inject = ['$sce'];
  function trustAsHtml ($sce) {
    return function (text) {
      return $sce.trustAsHtml(text);
    }
  }
})();