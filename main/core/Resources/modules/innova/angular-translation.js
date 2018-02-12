'use strict';

angular.module('ui.translation', [])
    .filter('trans', function () {
        return function (text, placeholder, domain) {
            var translated = null;

            if (typeof text !== 'string') {
                console.error('Translation filter only works on strings.');
                translated = text;
            }

            if (typeof Translator !== 'object') {
                console.error('Translator object not found.');
                translated = text;
            }

            if (typeof placeholder !== 'object') {
                placeholder = {};
            }

            if (typeof domain === 'string' && domain.length !== 0) {
                translated = Translator.trans(text, placeholder, domain);
            } else {
                translated = Translator.trans(text, placeholder);
            }

            return translated;
        };
    })
    .filter('transChoice', function () {
        return function (text, count, placeholder, domain) {
            var translated = null;

            if (typeof text !== 'string') {
                console.error('Translation filter only works on strings.');
                translated = text;
            }

            if (typeof Translator !== 'object') {
                console.error('Translator object not found.');
                translated = text;
            }

            if (typeof count !== 'number' && parseInt(count) != count) {
                count = 0
            }

            if (typeof placeholder !== 'object') {
                placeholder = {};
            }

            if (typeof domain === 'string' && domain.length !== 0) {
                translated = Translator.transChoice(text, count, placeholder, domain);
            } else {
                translated = Translator.transChoice(text, count, placeholder);
            }

            return translated;
        };
    });
