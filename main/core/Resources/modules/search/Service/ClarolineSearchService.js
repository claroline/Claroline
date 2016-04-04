export default class ClarolineSearchService {
    constructor() {
        this.enablePager = true
        this.searchParam = {}
    }

    mergeObject(obj1, obj2) {

        for (let attrname in obj2) {
            obj1[attrname] = obj2[attrname]
        }

        return obj1
    }

    disablePager() {
        this.enablePager = false;
    }

    $get($http) {
        return {
            find: (route, searches, page, limit, additionalParameters = {}) => {
                let params = this.enablePager ? {'page': page, 'limit': limit}: {}
                params = angular.extend(params, additionalParameters)
                let qs = '?'
                for (let i = 0; i < searches.length; i++) {
                    qs += searches[i].field +'[]=' + searches[i].value + '&'
                }

                params = this.mergeObject(params, this.searchParam)
                var route = Routing.generate(route, params) + qs

                return $http.get(route)
            }
        }
    }
}
