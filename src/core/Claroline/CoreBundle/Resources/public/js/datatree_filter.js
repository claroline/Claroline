(function () {

    var filter = this.ClaroFilter = {};

    /* private attributes */
    var jsonroots = {};
    var jsontypes = {};
    var countWaitingAjaxReq = 0;

    function filterBlobByType(searchArray) {
        $('.resource_figure').each(function(i) {
            if (0 > searchArray.indexOf(this.getAttribute('data-type'))) {
                $('#' + this.getAttribute('id')).hide();
            }
        });
    }

    function filterBlobFromDate(date) {
        $('.resource_figure').each(function(i) {
            if (this.getAttribute('data-date_instance_creation') < date) {
                $('#' + this.getAttribute('id')).hide();
            }
        });
    }

    function filterBlobToDate(date) {
        $('.resource_figure').each(function(i) {
            if (this.getAttribute('data-date_instance_creation') >= date) {
                $('#' + this.getAttribute('id')).hide();
            }
        });
    }

    function filterTreeByType(searchArray, targetNode) {
        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
        startNode.visit(function(node) {
            if (node.isVisible() && node.data.title) {
                if (searchArray.indexOf(node.data.type) >= 0 || node.data.type == 'directory') {

                } else {
                    $(node.li).hide();
                }
            }
        });
    };

    function filterTreeByWorkspace(searchArray, targetNode) {
        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
        startNode.visit(function(node) {
            if (node.isVisible() && node.data.title) {
                if (searchArray.indexOf(node.data.workspaceId) >= 0) {

                } else {
                    $(node.li).hide();
                }
            }
        });
    }

    function filterTreeFromDate(date, targetNode) {
        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
        startNode.visit(function(node) {
            if (node.isVisible() && node.data.title && node.data.dateInstanceCreation) {
                if (node.data.dateInstanceCreation >= date || node.data.type == 'directory') {

                } else {
                    $(node.li).hide();
                }
            }
        });
    }

    function filterTreeToDate(date, targetNode) {
        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
        startNode.visit(function(node) {
            if (node.isVisible() && node.data.title) {
                if (node.data.dateInstanceCreation <= date || node.data.type == 'directory') {

                } else {
                    $(node.li).hide();
                }
            }
        });
    }

    function showNodes(targetNode) {
        var startNode = targetNode || $('#source_tree').dynatree('getRoot');
        startNode.visit(function(node) {
            $(node.li).show();
        });
    };

    function showBlobs() {
        $('.resource_figure').show();
    }

    function buildFilter(div){
        var filter = Twig.render(resource_filter, {
            resourceTypes: jsontypes,
            workspaceroots: jsonroots
        });
        div.append(filter);
    }

    function requestRoots(div) {
        //Get the workspace roots for the current user.
        ClaroUtils.sendRequest(Routing.generate('claro_resource_roots'),
            function(data) {
                jsonroots = data;
                if (--countWaitingAjaxReq === 0)
                    buildFilter(div);
            });
    }

    function requestTypes(div) {
        //Gets the resource types (will be needed later for the linker mode & the filters).
        ClaroUtils.sendRequest(Routing.generate('claro_resource_types'),
            function(data) {
                jsontypes = data;
                if (--countWaitingAjaxReq === 0)
                    buildFilter(div);
            });
    }

    filter.build = function(div){
        countWaitingAjaxReq = 2;
        //Gets the json for contextual menus.
        requestRoots(div);
        requestTypes(div);

        $('#ct_filter').live('click', function() {
            showNodes();
            showBlobs();

            if ($('#ct_switch_mode').val() == 'classic') {
                if ($('#select_root').val() !== null) {
                    filterTreeByWorkspace(($('#select_root').val()));
                }

                if ($('#select_type').val() !== null) {
                    filterTreeByType($('#select_type').val());
                }

                if ($('#rf_date_from').val() !== '') {
                    filterTreeFromDate($('#rf_date_from').val());
                }

                if ($('#rf_date_to').val() !== '') {
                    filterTreeToDate($('#rf_date_to').val());
                }
            }

            if ($('#ct_switch_mode').val() == 'linker') {
                if ($('#select_root').val()) {
                    var types = $('#source_tree').dynatree('getRoot').getChildren();
                    for (var i in types) {
                        filterTreeByWorkspace($('#select_root').val(), types[i]);
                    }
                }

                if ($('#select_type').val() !== null) {
                    filterTreeByType($('#select_type').val());
                }

                if ($('#rf_date_from').val() !== '') {
                    filterTreeFromDate($('#rf_date_from').val());
                }

                if ($('#rf_date_to').val() !== '') {
                    filterTreeToDate($('#rf_date_to').val());
                }
            }

            if ($('#ct_switch_mode').val() == 'hybrid') {
                if ($('#select_root').val() !== null) {
                    filterTreeByWorkspace(($('#select_root').val()));
                }

                if ($('#select_type').val() !== null) {
                    filterBlobByType($('#select_type').val());
                }

                if ($('#rf_date_from').val() !== '') {
                    filterBlobFromDate($('#rf_date_from').val());
                }

                if ($('#rf_date_to').val() !== '') {
                    filterBlobToDate($('#rf_date_to').val());
                }
            }

        });
    }

    filter.getActiveFilters()
    {
        var activeFilters = {};
        activeFilters.root = $('#select_root').val();
        activeFilters.type = $('#select_type').val();
        activeFilters.dateSubmissionTo = $('#rf_date_from').val();
        activeFilters.dateSubmissionFrom = $('#rf_date_to').val();

        return activeFilters;
    }
})();