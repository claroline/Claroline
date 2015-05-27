(function() {
    $(function() {
        $('.delete').confirmModal();
        $('.import-dropdown').dropdown();
        $('.import-dropdown').tooltip({
            container: "body",
            placement: "top",
            title: "{{ 'choose_import_portfolio_format'|trans({}, 'icap_portfolio')|raw }}"
        });
        $('.exchange_link').click(function(event) {
            var scope = angular.element($("#exchange_space_container")).scope();
            scope.$apply(function(){
                var portfolioId = $(event.delegateTarget).val();
                if (portfolioId !== scope.selectedPortfolioId) {

                    scope.clickOnPortolio(portfolioId);
                }
                $('#portfolio_space_tabs a[href="#exchange_space"]').tab('show');
            });
        });
        var locationhash = window.location.hash;
        if (locationhash.substr(0,2) == "#!") {
            $("#portfolio_space_tabs a[href='#" + locationhash.substr(2) + "']").tab("show");
        }
    });
})();