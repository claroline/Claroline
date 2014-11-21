'use strict';

var appTranslation = angular.module('app.translation', []);

appTranslation
    .factory('translationService', function(){
        return {
            trans: function(key) {
                return Translator.get('icap_portfolio' + ':' + key);
            }
        };
    })
    .filter('trans', ["translationService", function(translationService) {
        return function(key) {
            return translationService.trans(key);
        };
    }]);