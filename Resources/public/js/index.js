(function() {
    $(function() {
        var locationhash = window.location.hash;
        if (locationhash.substr(0,2) == "#!") {
            $("#portfolio_space_tabs a[href='#" + locationhash.substr(2) + "']").tab("show");
        }

        toastr.options = {
            "closeButton": true,
            "positionClass": "toast-top-center",
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        var modal = window.Claroline.Modal;

        var elementsToModalized = [
            {
                target: "a.modal_action",
                message: Translator.trans('portfolio_added_ajax_notification', {}, 'icap_portfolio')
            },
            {
                target: "#no_portfolio a",
                message: Translator.trans('portfolio_added_ajax_notification', {}, 'icap_portfolio')
            },
            {
                target: "a.rename_link",
                message: Translator.trans('portfolio_renamed_ajax_notification', {}, 'icap_portfolio')
            },
            {
                target: "a.update_visibility_link",
                message: Translator.trans('portfolio_update_visibility_ajax_notification', {}, 'icap_portfolio')
            },
            {
                target: "a.update_guides_link",
                message: Translator.trans('portfolio_update_guides_ajax_notification', {}, 'icap_portfolio')
            }];
        elementsToModalized.forEach(function (element, index) {
            modalized(element);
        });

        $('#list_content').on('click', '.exchange_link', function(event) {
            var scope = angular.element($("#exchange_space_container")).scope();
            scope.$apply(function(){
                var portfolioId = $(event.currentTarget).val();
                if (portfolioId !== scope.selectedPortfolioId) {

                    scope.clickOnPortolio(portfolioId);
                }
                $('#portfolio_space_tabs a[href="#exchange_space"]').tab('show');
            });
        });

        $('#list_content').on('click', '.delete', function(event) {
            event.preventDefault();
            var deleteLink = $(event.target);
            deleteLink.confirmModal({
                confirmCallback: deleteConfirmCallback,
                confirmDismiss: false,
                confirmAutoOpen: true
            });
        });

        $('#list_content').on('click', '.pagination a', function(event) {
            event.preventDefault();
            $.ajax({
                url: event.target.href,
                success: function(data, textStatus, jqXHR) {
                    updateListContent(data);
                }
            });
        });

        $('#import_action a').click(function(event) {
            event.preventDefault();
            modal.displayForm(
                event.target.href,
                function(data) {
                    $.ajax({
                        url: window.location,
                        success: function(data, textStatus, jqXHR) {
                            updateListContent(data);
                            toastr.success(Translator.trans('portfolio_imported_ajax_notification', {}, 'icap_portfolio'));
                        }
                    });
                },
                function(data) {
                },
                'portfolio_import'
            )
        });

        function deleteConfirmCallback(target, modal) {
            $.ajax({
                url: $(target).attr('href'),
                success: function(data, textStatus, jqXHR) {
                    updateListContent(data);
                    toastr.success(Translator.trans('portfolio_deleted_ajax_notification', {}, 'icap_portfolio'));
                    modal.modal('hide');
                }
            });
        }

        function submitForm(modalElement, form, message) {
            $.ajax({
                url: form.attr('action'),
                data: form.serializeArray(),
                type: 'POST',
                success: function(data, textStatus, jqXHR) {
                    updateListContent(data);
                    modalElement.modal('hide');
                    toastr.success(message);
                }
            });
        }

        function modalized(element) {
            $('#portfolio_list').on('click', element.target, function (event) {
                event.preventDefault();
                modal.fromUrl(
                    event.target.href,
                    function(modalElement) {
                        var modalForm = $("form", modalElement);

                        modalElement.on('click', 'button[type="submit"]', function (event) {
                            event.preventDefault();
                            submitForm(modalElement, modalForm, element.message);
                        });

                        modalElement.on('keypress', function (event) {
                            if (event.keyCode === 13 && event.target.nodeName !== 'TEXTAREA') {
                                event.preventDefault();
                                submitForm(modalElement, modalForm);
                            }
                        });
                    }
                );
            });
        }

        function updateListContent(content) {
            $("#list_content").html(content);
        }
    });
})();