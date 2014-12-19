(function() {
    'use strict';

    angular
        .module('app',
            [
                'ngSanitize',
                'mgcrea.ngStrap',
                'ui.resizer',
                'website.constants',
                window.menuNgPlugin
            ]
        );
})();