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
                 * Update the player
                 * @param player
                 * @returns 
                 */
                update : function (player){
                    var deferred = $q.defer();                    
                    // player constructor
                    function Player(player){
                        var ujm_player = {
                            name: player.name,
                            description: player.description,
                            startDate: new Date(player.startDate),
                            endDate: new Date(player.endDate)
                        };
                        
                        return ujm_player;
                    }
                    
                    var updated = new Player(player);
                   
                    $http
                        .put(
                            Routing.generate('ujm_player_update', { id : player.id }),
                            {
                                exercise_player_type: updated
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