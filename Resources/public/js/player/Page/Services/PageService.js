/**
 * Page Service
 */
(function () {
    'use strict';

    angular.module('Page').factory('PageService', [
        '$http',
        '$filter',
        '$q',
        function PageService($http, $filter, $q) {
           
            return {
                
                
                addPage : function(){
                   function Page(){
                        var ujm_player = {
                            name: player.name,
                            description: player.description,
                            startDate: new Date(player.startDate),
                            endDate: new Date(player.endDate)
                        };
                        
                        return ujm_player;
                    }
                    
                    var updated = new Player(player);
                },
               
                /**
                 * Update exercise player pages
                 * @param player
                 * @returns 
                 */
                update : function (pages){
                    var deferred = $q.defer(); 
                    var playerId = pages[0].playerId;
                    $http
                        .post(
                            Routing.generate('ujm_pages_update', { id : playerId}), {pages: pages}
                        )
                        .success(function (response){
                            deferred.resolve(response);
                        })
                        .error(function(data, status){
                            console.log('Page service, update method error');
                            console.log(status);
                            console.log(data);
                        });
                        
                     return deferred.promise;
                }
            };
        }
    ]);
})();