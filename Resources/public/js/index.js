(function() {
    $(function() {
        $('.delete').confirmModal();
        $('.import-dropdown').dropdown();
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

        var modal = window.Claroline.Modal;

        $("a.modal_action, #no_portfolio a").click(function (event) {
            event.preventDefault();
            modal.displayForm(
                event.delegateTarget.href,
                function(data, textStatus, jqXHR) {
                    console.log($("#list_content"));
                    //$("#list_content").html(data);
                },
                function(data) {
                    console.log('pouet');
                });
        });
    });
})();