/**
 * PlayerCommonService
 */
(function () {
    'use strict';
    angular.module('PlayerSharedServices').factory('PlayerDataSharing', [
        '$http',
        '$filter',
        '$q',
        function PlayerDataSharing($http, $filter, $q) {

            this.exercise = {};
            this.paper = {};
            this.user = {};

            return {
                /**
                 * Set the player exercise
                 * @param {object} sequence
                 * @returns {object}
                 */
                setExercise: function (exercise) {
                    this.exercise = exercise;
                    return this.exercise;
                },
                getExercise: function () {
                    return this.exercise;
                },                
                setPaper: function (paper) {
                    this.paper = paper;
                    return this.paper;
                },
                getPaper: function () {
                    return this.paper;
                },
                setUser: function (user) {
                    this.user = user;
                    return this.user;
                },
                getUser: function () {
                    return this.user;
                }
            };
        }
    ]);
})();


