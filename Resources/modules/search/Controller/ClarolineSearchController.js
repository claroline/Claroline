export default class ClarolineSearchController {
    
    constructor($http, SearchOptionsService) {
        this.$http = $http
        this.SearchOptionsService = SearchOptionsService
        this.selected = [];
        this.options  = [];
    }

    refreshOptions($select) {
        //I should not be doing this here. Probably in a directive would be better.
        this.options = this.SearchOptionsService.generateOptions(this.fields);

        for (var i = 0; i < this.options.length; i++) {
            this.options[i].name = this.SearchOptionsService.getOptionValue(this.options[i].field, $select.search);
            this.options[i].value = $select.search;
        }
    }

    onSelect($item, $model, $select) {
        //angular and its plugins does not make any sense to me.
        $select.selected.pop();
        const cloned = angular.copy($item);
        $select.selected.push(cloned);
        this.options.push(this.SearchOptionsService.getOptionValue($item.field));
        this.selected = angular.copy($select.selected);
    }

    onRemove($item, $model, $select) {
        this.selected = $select.selected;
    }

    search () {
        this.onSearch()(this.selected);
    }
}
