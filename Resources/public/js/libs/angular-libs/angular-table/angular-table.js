// author:   Samuel Mueller 
// version: 1.0.6 
// license:  MIT 
// homepage: http://github.com/samu/angular-table 
(function () {
    var ColumnConfiguration, PageSequence, PaginatedSetup, ScopeConfigWrapper, Setup, StandardSetup, Table, TableConfiguration, configurationVariableNames, paginationTemplate,
            __hasProp = {}.hasOwnProperty,
            __extends = function (child, parent) {
                for (var key in parent) {
                    if (__hasProp.call(parent, key))
                        child[key] = parent[key];
                }
                function ctor() {
                    this.constructor = child;
                }
                ctor.prototype = parent.prototype;
                child.prototype = new ctor();
                child.__super__ = parent.prototype;
                return child;
            };

    angular.module("angular-table", []);

    ColumnConfiguration = (function () {
        function ColumnConfiguration(bodyMarkup, headerMarkup) {
            this.attribute = bodyMarkup.attribute;
            this.title = bodyMarkup.title;
            this.sortable = bodyMarkup.sortable;
            this.width = bodyMarkup.width;
            this.initialSorting = bodyMarkup.initialSorting;
            if (headerMarkup) {
                this.customContent = headerMarkup.customContent;
                this.attributes = headerMarkup.attributes;
            }
        }

        ColumnConfiguration.prototype.createElement = function () {
            var th;
            return th = angular.element(document.createElement("th"));
        };

        ColumnConfiguration.prototype.renderTitle = function (element) {
            return element.html(this.customContent || this.title);
        };

        ColumnConfiguration.prototype.renderAttributes = function (element) {
            var attribute, _i, _len, _ref, _results;
            if (this.customContent) {
                _ref = this.attributes;
                _results = [];
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    attribute = _ref[_i];
                    _results.push(element.attr(attribute.name, attribute.value));
                }
                return _results;
            }
        };

        ColumnConfiguration.prototype.renderSorting = function (element) {
            var icon;
            if (this.sortable) {
                element.attr("ng-click", "predicate = '" + this.attribute + "'; descending = !descending;");
                icon = angular.element("<i style='margin-left: 10px;cursor:pointer;'></i>");
                icon.attr("ng-class", "getSortIcon('" + this.attribute + "', predicate)");
                return element.append(icon);
            }
        };

        ColumnConfiguration.prototype.renderWidth = function (element) {
            return element.attr("width", this.width);
        };

        ColumnConfiguration.prototype.renderHtml = function () {
            var th;
            th = this.createElement();
            this.renderTitle(th);
            this.renderAttributes(th);
            this.renderSorting(th);
            this.renderWidth(th);
            return th;
        };

        return ColumnConfiguration;

    })();

    configurationVariableNames = (function () {
        function configurationVariableNames(configObjectName) {
            this.configObjectName = configObjectName;
            this.itemsPerPage = "" + this.configObjectName + ".itemsPerPage";
            this.sortContext = "" + this.configObjectName + ".sortContext";
            this.fillLastPage = "" + this.configObjectName + ".fillLastPage";
            this.maxPages = "" + this.configObjectName + ".maxPages";
            this.currentPage = "" + this.configObjectName + ".currentPage";
            this.orderBy = "" + this.configObjectName + ".orderBy";
            this.paginatorLabels = "" + this.configObjectName + ".paginatorLabels";
        }

        return configurationVariableNames;

    })();

    ScopeConfigWrapper = (function () {
        function ScopeConfigWrapper(scope, configurationVariableNames, listName) {
            this.scope = scope;
            this.configurationVariableNames = configurationVariableNames;
            this.listName = listName;
        }

        ScopeConfigWrapper.prototype.getList = function () {
            return this.scope.$eval(this.listName);
        };

        ScopeConfigWrapper.prototype.getItemsPerPage = function () {
            return this.scope.$eval(this.configurationVariableNames.itemsPerPage) || 10;
        };

        ScopeConfigWrapper.prototype.getCurrentPage = function () {
            return this.scope.$eval(this.configurationVariableNames.currentPage) || 0;
        };

        ScopeConfigWrapper.prototype.getMaxPages = function () {
            return this.scope.$eval(this.configurationVariableNames.maxPages) || void 0;
        };

        ScopeConfigWrapper.prototype.getSortContext = function () {
            return this.scope.$eval(this.configurationVariableNames.sortContext) || 'global';
        };

        ScopeConfigWrapper.prototype.setCurrentPage = function (currentPage) {
            return this.scope.$eval("" + this.configurationVariableNames.currentPage + "=" + currentPage);
        };

        ScopeConfigWrapper.prototype.getOrderBy = function () {
            return this.scope.$eval(this.configurationVariableNames.orderBy) || 'orderBy';
        };

        ScopeConfigWrapper.prototype.getPaginatorLabels = function () {
            var paginatorLabelsDefault;
            paginatorLabelsDefault = {
                stepBack: '‹',
                stepAhead: '›',
                jumpBack: '«',
                jumpAhead: '»',
                first: 'First',
                last: 'Last'
            };
            return this.scope.$eval(this.configurationVariableNames.paginatorLabels) || paginatorLabelsDefault;
        };

        return ScopeConfigWrapper;

    })();

    TableConfiguration = (function () {
        function TableConfiguration(tableElement, attributes) {
            this.tableElement = tableElement;
            this.attributes = attributes;
            this.id = this.attributes.id;
            this.config = this.attributes.atConfig;
            this.paginated = this.attributes.atPaginated != null;
            this.list = this.attributes.atList;
            this.createColumnConfigurations();
        }

        TableConfiguration.prototype.capitaliseFirstLetter = function (string) {
            if (string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            } else {
                return "";
            }
        };

        TableConfiguration.prototype.extractWidth = function (classes) {
            var width;
            width = /([0-9]+px)/i.exec(classes);
            if (width) {
                return width[0];
            } else {
                return "";
            }
        };

        TableConfiguration.prototype.isSortable = function (classes) {
            var sortable;
            sortable = /(sortable)/i.exec(classes);
            if (sortable) {
                return true;
            } else {
                return false;
            }
        };

        TableConfiguration.prototype.getInitialSorting = function (td) {
            var initialSorting;
            initialSorting = td.attr("at-initial-sorting");
            if (initialSorting) {
                if (initialSorting === "asc" || initialSorting === "desc") {
                    return initialSorting;
                }
                throw "Invalid value for initial-sorting: " + initialSorting + ". Allowed values are 'asc' or 'desc'.";
            }
            return void 0;
        };

        TableConfiguration.prototype.collectHeaderMarkup = function (table) {
            var customHeaderMarkups, th, tr, _i, _len, _ref;
            customHeaderMarkups = {};
            tr = table.find("tr");
            _ref = tr.find("th");
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                th = _ref[_i];
                th = angular.element(th);
                customHeaderMarkups[th.attr("at-attribute")] = {
                    customContent: th.html(),
                    attributes: th[0].attributes
                };
            }
            return customHeaderMarkups;
        };

        TableConfiguration.prototype.collectBodyMarkup = function (table) {
            var attribute, bodyDefinition, initialSorting, sortable, td, title, width, _i, _len, _ref;
            bodyDefinition = [];
            _ref = table.find("td");
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                td = _ref[_i];
                td = angular.element(td);
                attribute = td.attr("at-attribute");
                title = td.attr("at-title") || this.capitaliseFirstLetter(td.attr("at-attribute"));
                sortable = td.attr("at-sortable") !== void 0 || this.isSortable(td.attr("class"));
                width = this.extractWidth(td.attr("class"));
                initialSorting = this.getInitialSorting(td);
                bodyDefinition.push({
                    attribute: attribute,
                    title: title,
                    sortable: sortable,
                    width: width,
                    initialSorting: initialSorting
                });
            }
            return bodyDefinition;
        };

        TableConfiguration.prototype.createColumnConfigurations = function () {
            var bodyMarkup, headerMarkup, i, _i, _len;
            headerMarkup = this.collectHeaderMarkup(this.tableElement);
            bodyMarkup = this.collectBodyMarkup(this.tableElement);
            this.columnConfigurations = [];
            for (_i = 0, _len = bodyMarkup.length; _i < _len; _i++) {
                i = bodyMarkup[_i];
                this.columnConfigurations.push(new ColumnConfiguration(i, headerMarkup[i.attribute]));
            }
        };

        return TableConfiguration;

    })();

    Setup = (function () {
        function Setup() {
        }

        Setup.prototype.setupTr = function (element, repeatString) {
            var tbody, tr;
            tbody = element.find("tbody");
            tr = tbody.find("tr");
            tr.attr("ng-repeat", repeatString);
            return tbody;
        };

        return Setup;

    })();

    StandardSetup = (function (_super) {
        __extends(StandardSetup, _super);

        function StandardSetup(configurationVariableNames, list) {
            this.list = list;
            this.repeatString = "item in " + this.list + " | orderBy:predicate:descending";
        }

        StandardSetup.prototype.compile = function (element, attributes, transclude) {
            return this.setupTr(element, this.repeatString);
        };

        StandardSetup.prototype.link = function () {
        };

        return StandardSetup;

    })(Setup);

    PaginatedSetup = (function (_super) {
        __extends(PaginatedSetup, _super);

        function PaginatedSetup(configurationVariableNames) {
            this.configurationVariableNames = configurationVariableNames;
            this.repeatString = "item in sortedAndPaginatedList";
        }

        PaginatedSetup.prototype.compile = function (element) {
            var fillerTr, tbody, td, tdString, tds, _i, _len;
            tbody = this.setupTr(element, this.repeatString);
            tds = element.find("td");
            tdString = "";
            for (_i = 0, _len = tds.length; _i < _len; _i++) {
                td = tds[_i];
                tdString += "<td><span>&nbsp;</span></td>";
            }
            fillerTr = angular.element(document.createElement("tr"));
            fillerTr.attr("ng-show", this.configurationVariableNames.fillLastPage);
            fillerTr.html(tdString);
            fillerTr.attr("ng-repeat", "item in fillerArray");
            tbody.append(fillerTr);
        };

        PaginatedSetup.prototype.link = function ($scope, $element, $attributes, $filter) {
            var cvn, getFillerArray, getSortedAndPaginatedList, update, w;
            cvn = this.configurationVariableNames;
            w = new ScopeConfigWrapper($scope, cvn, $attributes.atList);
            getSortedAndPaginatedList = function (list, currentPage, itemsPerPage, orderBy, sortContext, predicate, descending, $filter) {
                var fromPage, val;
                if (list) {
                    val = list;
                    fromPage = itemsPerPage * currentPage - list.length;
                    if (sortContext === "global") {
                        val = $filter(orderBy)(val, predicate, descending);
                        val = $filter("limitTo")(val, fromPage);
                        val = $filter("limitTo")(val, itemsPerPage);
                    } else {
                        val = $filter("limitTo")(val, fromPage);
                        val = $filter("limitTo")(val, itemsPerPage);
                        val = $filter(orderBy)(val, predicate, descending);
                    }
                    return val;
                } else {
                    return [];
                }
            };
            getFillerArray = function (list, currentPage, numberOfPages, itemsPerPage) {
                var fillerLength, itemCountOnLastPage, x, _i, _j, _ref, _ref1, _ref2, _results, _results1;
                itemsPerPage = parseInt(itemsPerPage);
                if (list.length <= 0) {
                    _results = [];
                    for (x = _i = 0, _ref = itemsPerPage - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; x = 0 <= _ref ? ++_i : --_i) {
                        _results.push(x);
                    }
                    return _results;
                } else if (currentPage === numberOfPages - 1) {
                    itemCountOnLastPage = list.length % itemsPerPage;
                    if (itemCountOnLastPage !== 0) {
                        fillerLength = itemsPerPage - itemCountOnLastPage - 1;
                        _results1 = [];
                        for (x = _j = _ref1 = list.length, _ref2 = list.length + fillerLength; _ref1 <= _ref2 ? _j <= _ref2 : _j >= _ref2; x = _ref1 <= _ref2 ? ++_j : --_j) {
                            _results1.push(x);
                        }
                        return _results1;
                    } else {
                        return [];
                    }
                }
            };
            update = function () {
                var nop;
                $scope.sortedAndPaginatedList = getSortedAndPaginatedList(w.getList(), w.getCurrentPage(), w.getItemsPerPage(), w.getOrderBy(), w.getSortContext(), $scope.predicate, $scope.descending, $filter);
                nop = Math.ceil(w.getList().length / w.getItemsPerPage());
                return $scope.fillerArray = getFillerArray(w.getList(), w.getCurrentPage(), nop, w.getItemsPerPage());
            };
            $scope.$watch(cvn.currentPage, function () {
                return update();
            });
            $scope.$watch(cvn.itemsPerPage, function () {
                return update();
            });
            $scope.$watch(cvn.sortContext, function () {
                return update();
            });
            $scope.$watchCollection($attributes.atList, function () {
                return update();
            });
            $scope.$watch("" + $attributes.atList + ".length", function () {
                $scope.numberOfPages = Math.ceil(w.getList().length / w.getItemsPerPage());
                return update();
            });
            $scope.$watch("predicate", function () {
                return update();
            });
            return $scope.$watch("descending", function () {
                return update();
            });
        };

        return PaginatedSetup;

    })(Setup);

    Table = (function () {
        function Table(element, tableConfiguration, configurationVariableNames) {
            this.element = element;
            this.tableConfiguration = tableConfiguration;
            this.configurationVariableNames = configurationVariableNames;
        }

        Table.prototype.constructHeader = function () {
            var i, tr, _i, _len, _ref;
            tr = angular.element(document.createElement("tr"));
            _ref = this.tableConfiguration.columnConfigurations;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                i = _ref[_i];
                tr.append(i.renderHtml());
            }
            return tr;
        };

        Table.prototype.setupHeader = function () {
            var header, thead, tr;
            thead = this.element.find("thead");
            if (thead) {
                header = this.constructHeader();
                tr = angular.element(thead).find("tr");
                tr.remove();
                return thead.append(header);
            }
        };

        Table.prototype.getSetup = function () {
            if (this.tableConfiguration.paginated) {
                return new PaginatedSetup(this.configurationVariableNames);
            } else {
                return new StandardSetup(this.configurationVariableNames, this.tableConfiguration.list);
            }
        };

        Table.prototype.compile = function () {
            this.setupHeader();
            this.setup = this.getSetup();
            return this.setup.compile(this.element);
        };

        Table.prototype.setupInitialSorting = function ($scope) {
            var bd, _i, _len, _ref, _results;
            _ref = this.tableConfiguration.columnConfigurations;
            _results = [];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                bd = _ref[_i];
                if (bd.initialSorting) {
                    if (!bd.attribute) {
                        throw "initial-sorting specified without attribute.";
                    }
                    $scope.predicate = bd.attribute;
                    _results.push($scope.descending = bd.initialSorting === "desc");
                } else {
                    _results.push(void 0);
                }
            }
            return _results;
        };

        Table.prototype.post = function ($scope, $element, $attributes, $filter) {
            this.setupInitialSorting($scope);
            if (!$scope.getSortIcon) {
                $scope.getSortIcon = function (predicate, currentPredicate) {
                    if (predicate !== $scope.predicate) {
                        return "fa fa-minus";
                    }
                    if ($scope.descending) {
                        return "fa fa-chevron-up";
                    } else {
                        return "fa fa-chevron-down";
                    }
                };
            }
            return this.setup.link($scope, $element, $attributes, $filter);
        };

        return Table;

    })();

    PageSequence = (function () {
        function PageSequence(lowerBound, upperBound, start, length) {
            this.lowerBound = lowerBound != null ? lowerBound : 0;
            this.upperBound = upperBound != null ? upperBound : 1;
            if (start == null) {
                start = 0;
            }
            this.length = length != null ? length : 1;
            if (this.length > (this.upperBound - this.lowerBound)) {
                throw "sequence is too long";
            }
            this.data = this.generate(start);
        }

        PageSequence.prototype.generate = function (start) {
            var x, _i, _ref, _results;
            if (start > (this.upperBound - this.length)) {
                start = this.upperBound - this.length;
            } else if (start < this.lowerBound) {
                start = this.lowerBound;
            }
            _results = [];
            for (x = _i = start, _ref = parseInt(start) + parseInt(this.length) - 1; start <= _ref ? _i <= _ref : _i >= _ref; x = start <= _ref ? ++_i : --_i) {
                _results.push(x);
            }
            return _results;
        };

        PageSequence.prototype.resetParameters = function (lowerBound, upperBound, length) {
            this.lowerBound = lowerBound;
            this.upperBound = upperBound;
            this.length = length;
            if (this.length > (this.upperBound - this.lowerBound)) {
                throw "sequence is too long";
            }
            return this.data = this.generate(this.data[0]);
        };

        PageSequence.prototype.relocate = function (distance) {
            var newStart;
            newStart = this.data[0] + distance;
            return this.data = this.generate(newStart, newStart + this.length);
        };

        PageSequence.prototype.realignGreedy = function (page) {
            var newStart;
            if (page < this.data[0]) {
                newStart = page;
                return this.data = this.generate(newStart);
            } else if (page > this.data[this.length - 1]) {
                newStart = page - (this.length - 1);
                return this.data = this.generate(newStart);
            }
        };

        PageSequence.prototype.realignGenerous = function (page) {
        };

        return PageSequence;

    })();

    paginationTemplate = "<div style='margin: 0px;'> <ul class='pagination'> <li ng-class='{disabled: getCurrentPage() <= 0}'> <a href='' ng-click='stepPage(-numberOfPages)'>{{getPaginatorLabels().first}}</a> </li> <li ng-show='showSectioning()' ng-class='{disabled: getCurrentPage() <= 0}'> <a href='' ng-click='jumpBack()'>{{getPaginatorLabels().jumpBack}}</a> </li> <li ng-class='{disabled: getCurrentPage() <= 0}'> <a href='' ng-click='stepPage(-1)'>{{getPaginatorLabels().stepBack}}</a> </li> <li ng-class='{active: getCurrentPage() == page}' ng-repeat='page in pageSequence.data'> <a href='' ng-click='goToPage(page)' ng-bind='page + 1'></a> </li> <li ng-class='{disabled: getCurrentPage() >= numberOfPages - 1}'> <a href='' ng-click='stepPage(1)'>{{getPaginatorLabels().stepAhead}}</a> </li> <li ng-show='showSectioning()' ng-class='{disabled: getCurrentPage() >= numberOfPages - 1}'> <a href='' ng-click='jumpAhead()'>{{getPaginatorLabels().jumpAhead}}</a> </li> <li ng-class='{disabled: getCurrentPage() >= numberOfPages - 1}'> <a href='' ng-click='stepPage(numberOfPages)'>{{getPaginatorLabels().last}}</a> </li> </ul> </div>";

    angular.module("angular-table").directive("atTable", [
        "$filter", function ($filter) {
            return {
                restrict: "AC",
                scope: true,
                compile: function (element, attributes, transclude) {
                    var cvn, table, tc;
                    tc = new TableConfiguration(element, attributes);
                    cvn = new configurationVariableNames(attributes.atConfig);
                    table = new Table(element, tc, cvn);
                    table.compile();
                    return {
                        post: function ($scope, $element, $attributes) {
                            return table.post($scope, $element, $attributes, $filter);
                        }
                    };
                }
            };
        }
    ]);

    angular.module("angular-table").directive("atPagination", [
        function () {
            return {
                restrict: "E",
                scope: true,
                replace: true,
                template: paginationTemplate,
                link: function ($scope, $element, $attributes) {
                    var cvn, getNumberOfPages, keepInBounds, setNumberOfPages, update, w;
                    cvn = new configurationVariableNames($attributes.atConfig);
                    w = new ScopeConfigWrapper($scope, cvn, $attributes.atList);
                    keepInBounds = function (val, min, max) {
                        val = Math.max(min, val);
                        return Math.min(max, val);
                    };
                    getNumberOfPages = function () {
                        return $scope.numberOfPages;
                    };
                    setNumberOfPages = function (numberOfPages) {
                        return $scope.numberOfPages = numberOfPages;
                    };
                    update = function (reset) {
                        var newNumberOfPages, pagesToDisplay;
                        if (w.getList()) {
                            if (w.getList().length > 0) {
                                newNumberOfPages = Math.ceil(w.getList().length / w.getItemsPerPage());
                                setNumberOfPages(newNumberOfPages);
                                if ($scope.showSectioning()) {
                                    pagesToDisplay = w.getMaxPages();
                                } else {
                                    pagesToDisplay = newNumberOfPages;
                                }
                                $scope.pageSequence.resetParameters(0, newNumberOfPages, pagesToDisplay);
                                w.setCurrentPage(keepInBounds(w.getCurrentPage(), 0, getNumberOfPages() - 1));
                                return $scope.pageSequence.realignGreedy(w.getCurrentPage());
                            } else {
                                setNumberOfPages(1);
                                $scope.pageSequence.resetParameters(0, 1, 1);
                                w.setCurrentPage(0);
                                return $scope.pageSequence.realignGreedy(0);
                            }
                        }
                    };
                    $scope.showSectioning = function () {
                        return w.getMaxPages() && getNumberOfPages() > w.getMaxPages();
                    };
                    $scope.getCurrentPage = function () {
                        return w.getCurrentPage();
                    };
                    $scope.getPaginatorLabels = function () {
                        return w.getPaginatorLabels();
                    };
                    $scope.stepPage = function (step) {
                        step = parseInt(step);
                        w.setCurrentPage(keepInBounds(w.getCurrentPage() + step, 0, getNumberOfPages() - 1));
                        return $scope.pageSequence.realignGreedy(w.getCurrentPage());
                    };
                    $scope.goToPage = function (page) {
                        return w.setCurrentPage(page);
                    };
                    $scope.jumpBack = function () {
                        return $scope.stepPage(-w.getMaxPages());
                    };
                    $scope.jumpAhead = function () {
                        return $scope.stepPage(w.getMaxPages());
                    };
                    $scope.pageSequence = new PageSequence();
                    $scope.$watch(cvn.itemsPerPage, function () {
                        return update();
                    });
                    $scope.$watch(cvn.maxPages, function () {
                        return update();
                    });
                    $scope.$watch($attributes.atList, function () {
                        return update();
                    });
                    $scope.$watch("" + $attributes.atList + ".length", function () {
                        return update();
                    });
                    return update();
                }
            };
        }
    ]);

    angular.module("angular-table").directive("atImplicit", [
        function () {
            return {
                restrict: "AC",
                compile: function (element, attributes, transclude) {
                    var attribute;
                    attribute = element.attr("at-attribute");
                    if (!attribute) {
                        throw "at-implicit specified without at-attribute: " + (element.html());
                    }
                    return element.append("<span ng-bind='item." + attribute + "'></span>");
                }
            };
        }
    ]);

}).call(this);
