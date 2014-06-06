'use strict';

portfolioApp
    .factory('translationService', function(){
        return {
            trans: function(key) {
                return Translator.get('icap_portfolio' + ':' + key);
            }
        };
    });