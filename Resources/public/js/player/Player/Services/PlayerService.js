/**
 * Exercise player service
 */
(function () {
    'use strict';

    angular.module('Player').factory('PlayerService', [
        '$http',
        '$filter',
        '$q',
        function PlayerService($http, $filter, $q) {
           
           

            return {
                /**
                 * Test method
                 *
                 * @param   {string} [name]
                 * @returns {string}
                 */
                hello: function (name) {
                    return 'Hello ' + name;
                },
                /**
                 * Update the player
                 * @param {type} player
                 * @returns {undefined}
                 */
                update : function (player){
                    var deferred = $q.defer();
                    
                    // player constructor
                    function Player(player){
                        var ujm_player = {
                            name:player.name,
                            description: player.description,
                            startDate: player.startDate,
                            endDate: player.endDate,
                            modification: new Date()
                        };
                        
                        return ujm_player;
                    }
                    
                    var updated = new Player(player);
                    $http
                        .put(
                            Routing.generate('ujm_player_update', { playerId : player.id }),
                            {
                                player: updated
                            }
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('player service, update method error');
                            console.log(status);
                            console.log(data);
                        });
                }
            };
        }
    ]);
})();