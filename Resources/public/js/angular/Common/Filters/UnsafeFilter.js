var UnsafeFilter = function UnsafeFilter($sce) {
    return $sce.trustAsHtml;
};

// Set up dependency injection
UnsafeFilter.$inject = [ '$sce' ];

// Register filter into Angular JS
angular
    .module('Common')
    .filter('unsafe', UnsafeFilter);

