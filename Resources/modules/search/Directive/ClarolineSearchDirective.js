import ClarolineSearchController from '../Controller/ClarolineSearchController'

export default class ClarolineSearchDirective {
    constructor() {
        this.scope = {}
        this.restrict = 'E'
        this.template = require('../Partial/search.html')
        this.replace = false
        this.controller = ClarolineSearchController
        this.controllerAs = 'cs'
        this.bindToController = {
            onSearch: '&',
            fields: '='
        };
    }
}

ClarolineSearchController.$inject = ['$http', 'SearchOptionsService']

