(function(){
    'use strict';

    angular
        .module('ui.flexnav')
        .value('flexnav.options', {
            'menuButtonName': 'Menu',
            'buttonClass': 'menu-button',
            'calcItemWidths': false,
            'fullScreen': true,
            'breakpoint': 800,
            'onItemClick': function() {}
        });
})();