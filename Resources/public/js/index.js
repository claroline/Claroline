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
            modal.fromUrl(
                event.delegateTarget.href,
                function(modalElement) {
                    var modalForm = $("form", modalElement);

                    modalElement.on('click', 'button[type="submit"]', function (event) {
                        event.preventDefault();
                        submitForm(modalElement, modalForm);
                    });

                    modalElement.on('keypress', function (event) {
                        if (event.keyCode === 13 && e.target.nodeName !== 'TEXTAREA') {
                            event.preventDefault();
                            submitForm(modalElement, modalForm);
                        }
                    });
                }
            );
        });

        function submitForm(modalElement, form) {
            $.ajax({
                url: form.attr('action'),
                data: form.serializeArray(),
                type: 'POST',
                success: function(data, textStatus, jqXHR) {
                    $("#list_content").html(data);
                    modalElement.modal('hide');
                }
            });
        }
    });
})();