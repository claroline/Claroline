'use strict';

portfolioApp
    .filter('trans', function() {
        return function(key) {
            return Translator.get('icap_portfolio' + ':' + key);
        };
    });