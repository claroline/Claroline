(function () {
    var manager = this.ClaroResourceManager = {};
    var jsonmenu = {};
    var construct = {};
    var pasteIds = {};
    //0 = cut; 1 = copy
    var cpd = null;
    var activePagerItem = 1;

    manager.init = function(
        div,
        prefix,
        divForm,
        selectType,
        submitButton,
        downloadButton,
        cutButton,
        copyButton,
        pasteButton,
        closeButton,
        flatChkBox
        ) {
        construct.div = div;
        construct.prefix = prefix;
        construct.divForm = divForm;
        construct.selectType = selectType;
        construct.submitButton = submitButton;
        construct.downloadButton = downloadButton;
        construct.cutButton = cutButton;
        construct.copyButton = copyButton;
        construct.pasteButton = pasteButton;
        construct.closeButton = closeButton;
        construct.flatChkBox = flatChkBox;

        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_menus'),
            function(data) {
                jsonmenu = JSON.parse(data);
                for (var menu in jsonmenu) {
                    delete jsonmenu[menu].items['new'];
                }
            },
            function() {
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_renders_thumbnail', {
                        'prefix': prefix
                    }),
                    function(data){
                        appendThumbnails(div, data);
                    }
                )
            });

        $('.link-navigate-instance').live('click', function(e){
            navigate(e.currentTarget.parentElement.parentElement.dataset.key);
        });

        $('.'+prefix+'-breadcrum-link').live('click', function(e){
            navigate(e.currentTarget.dataset.key);
        })

        window.onresize = function(e) {
            resizeBreadcrums();
        }

        submitButton.on('click', function(e){
            ClaroUtils.sendRequest(
                Routing.generate('claro_resource_creation_form', {
                    'resourceType': selectType.val()
                }),
                function(data) {
                    divForm.empty().append(data);
                    divForm.find('form').submit(function(e) {
                        e.preventDefault();
                        var parameters = {};
                        parameters.key = $("."+construct.prefix+"-breadcrum-link").last().attr('data-key');
                        var action = divForm.find('form').attr('action');
                        action = action.replace('_instanceId', parameters.key)
                        var id = divForm.find('form').attr('id');
                        ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                            submissionHandler(xhr, parameters);
                        });
                    })
                })
        });

        downloadButton.on('click', function(e){
            var ids = getSelectedItems();
            window.location = Routing.generate('claro_multi_export', ids);
        })

        cutButton.on('click', function(e){
            pasteIds = {};
            pasteIds = getSelectedItems();
            setLayout();
            cpd = 0;
        })

        copyButton.on('click', function(e){
            pasteIds = {};
            pasteIds = getSelectedItems();
            setLayout();
            cpd = 1;
        })

        pasteButton.on('click', function(e){
            var params = {};
            var route = '';
            params = pasteIds;
            if (cpd == 0) {
                params.newParentId = $("."+construct.prefix+"-breadcrum-link").last().attr('data-key');
                route = Routing.generate('claro_resource_multimove', params);
                ClaroUtils.sendRequest(route, function(){manager.reload()});
            } else {
                params.instanceDestinationId = $("."+construct.prefix+"-breadcrum-link").last().attr('data-key');
                route = Routing.generate('claro_resource_multi_add_workspace', params);
                ClaroUtils.sendRequest(route, function(){manager.reload()});
            }
        })

        closeButton.on('click', function(e){
            construct.divForm.empty();
        })

        flatChkBox.on('change', function(e){
            if(e.target.checked) {
                setLayout();
                var route = Routing.generate('claro_resource_count_instances');
                ClaroUtils.sendRequest(route,
                function(count){
                    construct.div.empty();
                    var paginator = buildPaginator(count);
                    div.append(paginator);

                    route = Routing.generate('claro_resource_flat_view_page', {
                        'page':activePagerItem,
                        'prefix':construct.prefix
                    });
                    ClaroUtils.sendRequest(route, function(data){
                        div.prepend(data);
                        setMenu();
                        $(".res-name").each(function(){formatResName($(this), 2, 20)});
                    })

                    $('.instance-paginator-item').on('click', function(e){
                        activePagerItem = e.target.innerHTML;
                        rendersFlatPaginatedThumbnails(activePagerItem);
                    });

                    $('.instance-paginator-next-item').on('click', function(e){
                        activePagerItem++;
                        rendersFlatPaginatedThumbnails(activePagerItem);
                    })

                    $('.instance-paginator-prev-item').on('click', function(e){
                        activePagerItem--;
                        rendersFlatPaginatedThumbnails(activePagerItem);
                    })
                });
            } else {
                ClaroUtils.sendRequest(
                    Routing.generate('claro_resource_renders_thumbnail', {
                        'prefix': construct.prefix
                    }),
                    function(data){
                        appendThumbnails(construct.div, data);
                    }
                )
            }
        })
    }

    manager.reload = function() {
        var key = $("."+construct.prefix+"-breadcrum-link").last().attr('data-key');
        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_renders_thumbnail', {
                'parentId': key,
                'prefix':construct.prefix
            }),
            function(data){
                appendThumbnails(construct.div, data);
            });
    }

    function navigate(key) {
        construct.divForm.empty();
        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_renders_thumbnail', {
                'parentId': key,
                'prefix':construct.prefix
            }),
            function(data){
                appendThumbnails(construct.div, data);
            });
    }

    function rendersFlatPaginatedThumbnails(page) {
        activePage(page);
        var route = Routing.generate('claro_resource_flat_view_page', {
            'page':page,
            'prefix':construct.prefix
        });
        ClaroUtils.sendRequest(route, function(data){
            $('.res-block').remove();
            construct.div.prepend(data);
            setMenu();
            $(".res-name").each(function(){formatResName($(this), 2, 20)});
        })
    }

    function activePage(item)
    {
        $('.instance-paginator-item').each(function(index, element){
            element.parentElement.className = '';
        })

        var searched = $('li[data-page="'+item+'"]');
        searched.first().addClass('active');
    }

    function buildPaginator(nbPage)
    {
        var paginator = '';
        paginator += '<div id="instances-paginator" class="pagination"><ul><li><a class="instance-paginator-prev-item" href="#">Prev</a></li>'
        for (var i = 0; i < nbPage;) {
            i++;
            if (i==1) {
                paginator += '<li data-page="'+i+'" class="active"><a class="instance-paginator_--item" href="#">'+i+'</a></li>';
            } else {
                paginator += '<li data-page="'+i+'"><a class="instance-paginator-item" href="#">'+i+'</a></li>';
            }
        }
        paginator += '<li><a href="#" class="instance-paginator-next-item">Next</a></li></ul></div>';

        return paginator;
    }

    function getSelectedItems()
    {
        var ids = {};
        var i = 0;
        $('.'+construct.prefix+'-chk-instance:checked').each(function(index, element){
            ids[i] = element.value;
            i++;
        })

        return ids;
    }

    function setMenu()
    {
        var parameters = {};
        $('.resource-menu').each(function(index, element){
            parameters.key = element.dataset.key;
            parameters.resourceId = element.dataset.resourceId;
            parameters.type = element.dataset.type;
            bindContextMenu(parameters, element, 'left');
        });

        $('.'+construct.prefix+'-instance-img').each(function(index, element){
            bindContextMenu(parameters, element, 'right');
        });
    }

    /* Cut the name of the resource if its length is more than maxLength,
     * adding '...' at the end. And cut multilines, trying to cut between words when possible. */
    function formatResName(element, maxLines, maxLengthPerLine) {
        maxLines = typeof maxLines !== 'undefined' ? maxLines : 2;
        maxLengthPerLine = typeof maxLengthPerLine !== 'undefined' ? maxLengthPerLine : 20;
        if (typeof element !== 'undefined' && element.text() !== 'undefined'
            && element.text().length > maxLengthPerLine) {
            var newText = new Array(maxLines);
            var curLine = 0;
            var curText = element.text();
            while (curText.length > 0 && curLine < maxLines) {
                newText[curLine] = curText.substr(0, maxLengthPerLine);
                if (curLine == maxLines-1) {
                } else {
                    var i = newText[curLine].length;
                    while (i>0) {
                        var c = newText[curLine].charAt(i-1);
                        if ( !((c>='a' && c<='z') || (c>='A' && c<='Z') || (c>='0' && c<='9')) )
                            break;
                        i--;
                    }
                    if (i > 0)
                        newText[curLine] = newText[curLine].substr(0,i);
                    curText = curText.substr(newText[curLine].length, curText.length);
                    newText[curLine] = newText[curLine]+"<br>";
                }
                curLine++;
            }
            if (curText.length > 0) {
                if (newText[curLine-1].length > maxLengthPerLine-3) {
                    newText[curLine-1] = newText[curLine-1].substr(0, maxLengthPerLine-3);
                    newText[curLine-1] = newText[curLine-1]+"...";
                }
            }
            element.html(newText.join(""));
        }
    };

    function appendThumbnails(div, data) {
        div.empty();
        div.append(data);
        setMenu();
        setLayout();
        resizeBreadcrums();
        $(".res-name").each(function(){formatResName($(this), 2, 20)});
    }

    function bindContextMenu(parameters, menuElement, trigger) {
        var type = parameters.type;
        var menuDefaultOptions = {
            selector: '#'+menuElement.id,
            trigger: trigger,
            //See the contextual menu documentation.
            callback: function(key, options) {
                //Finds and executes the action for the right menu item.
                findMenuObject(jsonmenu[type], parameters, key);
            }
        };

        menuDefaultOptions.items = jsonmenu[type].items;
        $.contextMenu(menuDefaultOptions);
        //Left click menu.
    };

    //Finds wich menu was fired for a node.
    //@params items is the menu object used.
    function findMenuObject(items, parameters, menuItem)
    {
        for (var property in items.items) {
            if (property === menuItem) {
                executeMenuActions(items.items[property], parameters);
            } else {
                if (items.items[property].hasOwnProperty('items')) {
                    findMenuObject(items.items[property], parameters, menuItem);
                }
            }
        }
    };

    function executeMenuActions (obj, parameters)
    {
        //Removes the placeholders in the route
        var route = obj.route;
        var compiledRoute = route.replace('_instanceId', parameters.key);
        compiledRoute = compiledRoute.replace('_resourceId', parameters.resourceId);
        obj.async ? executeAsync(obj, parameters, compiledRoute) : window.location = compiledRoute;
    }

    function executeAsync(obj, parameters, route) {

        //Delete was a special case as every node can be removed.
        (obj.name === 'delete') ? removeNode(parameters, route) : executeRequest(parameters, route);
    };

    function removeNode(parameters, route) {
        ClaroUtils.sendRequest(route, function(data, textStatus, jqXHR) {
            if (204 === jqXHR.status) {
                $('#'+construct.prefix+"_instance_"+parameters.key).remove();
            }
        });
    };

    function executeRequest(parameters, route) {

        ClaroUtils.sendRequest(route, function(data) {
            //If there is a form, the submission handler above is used.
            //There is no handler otherwise.
            construct.divForm.empty().append(data).find('form').submit(function(e) {
                e.preventDefault();
                var action = construct.divForm.find('form').attr('action');
                action = action.replace('_instanceId', parameters.key)
                var id = construct.divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters);
                });
            });
        });
    };

    function submissionHandler(xhr, parameters) {
        //If there is a json response, a node was returned.
        if (xhr.getResponseHeader('Content-Type') === 'application/json') {
            manager.reload();
        //If it's not a json response, we append the response at the top of the tree.
        } else {
            construct.divForm.empty().append(xhr.responseText).find('form').submit(function(e) {
                e.preventDefault();
                var action = construct.divForm.find('form').attr('action');
                //If it's a form, placeholders must be removed (the twig form doesn't know the instance parent,
                //that's why placeholders are used).'
                action = action.replace('_instanceId', parameters.key);
                action = action.replace('_resourceId', parameters.resourceId);
                var id = construct.divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters);
                });
            });
        }
    };

    function setLayout() {
        if(construct.flatChkBox.is(':checked')){
            construct.pasteButton.attr('disabled', 'disabled');
        } else {
            activePagerItem = 1;
            if($.isEmptyObject(pasteIds) || $("."+construct.prefix+"-breadcrum-link").size() == 1){
                construct.pasteButton.attr('disabled', 'disabled');
            } else {
                construct.pasteButton.removeAttr('disabled');
            }

            if ($("."+construct.prefix+"-breadcrum-link").size() == 1) {
                construct.selectType.hide();
                construct.submitButton.hide();
            } else {
                construct.selectType.show();
                construct.submitButton.show();
            }
        }
    }

    function resizeBreadcrums(){
        var resize = function(index, divSize) {
            var size = getCrumsSize();
            if(size > divSize && index >= 0) {
                var crumLink = (($("."+construct.prefix+"-breadcrum-link")).eq(index));
                formatResName(crumLink, 1, 9);
                index --;
                resize(index, divSize);
            }
        }

        var getCrumsSize = function(){
            var crumsSize = 0;
            $("."+construct.prefix+"-breadcrum-link").each(function(index, element){
                crumsSize += ($(this).width());
            })

            return crumsSize;
        }

        var makeCrums = function() {
            $("."+construct.prefix+"-breadcrum-link").each(function(index, element){
                element.innerHTML = " /"+element.title;
            })
        }

        makeCrums();
        var divSize = $('#'+construct.prefix+'-res-breadcrums').width();
        var crumsIndex = ($("."+construct.prefix+"-breadcrum-link")).size();

        resize(crumsIndex, divSize);
    }
})();
