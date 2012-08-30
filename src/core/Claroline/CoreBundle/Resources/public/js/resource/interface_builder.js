(function() {

    var builder = this.ClaroResourceInterfaceBuilder = {};

    ClaroResourceInterfaceBuilder.menu = {};

    builder.builder = function(
        div,
        prefix,
        divForm,
        selectType,
        submitButton,
        downloadButton,
        cutButton,
        copyButton,
        deleteButton,
        pasteButton,
        closeButton,
        flatChkBox,
        resourceGetter,
        resourceFilter
        )
        {
        var construct = {};

        construct.div = div;
        construct.prefix = prefix;
        construct.divForm = divForm;
        construct.selectType = selectType;
        construct.submitButton = submitButton;
        construct.downloadButton = downloadButton;
        construct.cutButton = cutButton;
        construct.copyButton = copyButton;
        construct.deleteButton = deleteButton;
        construct.pasteButton = pasteButton;
        construct.closeButton = closeButton;
        construct.flatChkBox = flatChkBox;
        construct.resourceGetter = resourceGetter;
        construct.resourceFilter = resourceFilter;
        construct.pasteIds = {};
        construct.cpd = null;
        construct.activePagerItem = 1;
        resourceGetter.setPrefix(prefix);

        //sets the resource filter callbacks
        resourceFilter.setCallBackToFilter(function(data){
            construct.div.empty();
//            construct.div.append(data);
        });

        resourceFilter.setCallResetFilter(function(data){
             resourceGetter.getRoots(function(data){appendThumbnails(data, construct)});
        })

        ClaroUtils.sendRequest(
            Routing.generate('claro_resource_menus'),
            function(data) {
                builder.menu = JSON.parse(data);
                for (var menu in builder.menu) {
                    delete builder.menu[menu].items['new'];
                }
            },
            function() {
                resourceGetter.getRoots(function(data){appendThumbnails(data, construct)});
            });

        $('.'+prefix+'-link-navigate-instance').live('click', function(e){
            navigate( $('#'+e.currentTarget.id).parents('.'+construct.prefix+'-res-block').attr('data-id'), construct);
        });

        $('.'+prefix+'-breadcrum-link').live('click', function(e){
            navigate($('#'+e.currentTarget.id).parents('.'+construct.prefix+'-res-block').attr('data-id'), construct);
        });

        window.onresize = function(e) {
            resizeBreadcrums(construct);
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
                        parameters.id = $("."+construct.prefix+"-breadcrum-link").last().attr('data-id');
                        var action = divForm.find('form').attr('action');
                        action = action.replace('_instanceId', parameters.id)
                        var id = divForm.find('form').attr('id');
                        ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                            submissionHandler(xhr, parameters, construct);
                        });
                    })
                })
        });

        cutButton.on('click', function(e){
            construct.pasteIds = {};
            construct.pasteIds = getSelectedItems(construct);
            setLayout(construct);
            construct.cpd = 0;
        })

        copyButton.on('click', function(e){
            construct.pasteIds = {};
            construct.pasteIds = getSelectedItems(construct);
            setLayout(construct);
            construct.cpd = 1;
        })

        downloadButton.on('click', function(e){
            var ids = getSelectedItems(construct);
            window.location = Routing.generate('claro_multi_export', ids);
        })

        deleteButton.on('click', function(e){
            var params = getSelectedItems(construct);
            var route = Routing.generate('claro_resource_multi_delete', params);
            ClaroUtils.sendRequest(route, function(data, textstatus, xhr){
                if (204 === xhr.status) {
                    for (var i in params) {
                        $('#'+construct.prefix+"-instance-"+params[i]).remove();
                    }
                }
            });
        })

        pasteButton.on('click', function(e){
            var params = {};
            var route = '';
            params = construct.pasteIds;
            if (construct.cpd == 0) {
                params.newParentId = $("."+construct.prefix+"-breadcrum-link").last().attr('data-id');
                route = Routing.generate('claro_resource_multimove', params);
                ClaroUtils.sendRequest(route, function(){
                    reload(construct);
                    });
            } else {
                params.instanceDestinationId = $("."+construct.prefix+"-breadcrum-link").last().attr('data-id');
                route = Routing.generate('claro_resource_multi_add_workspace', params);
                ClaroUtils.sendRequest(route, function(){
                    reload(construct);
                });
            }
        })

        closeButton.on('click', function(e){
            construct.divForm.empty();
        })

        flatChkBox.on('change', function(e){
            if(e.target.checked) {
                setLayout(construct);
                var route = Routing.generate('claro_resource_count_instances');
                ClaroUtils.sendRequest(route,
                    function(count){
                        construct.div.empty();
                        var paginator = buildPaginator(count);
                        div.append(paginator);
                        rendersFlatPaginatedThumbnails(construct);

                        $('.instance-paginator-item').on('click', function(e){
                            construct.activePagerItem = e.target.innerHTML;
                            rendersFlatPaginatedThumbnails(construct);
                        });

                        $('.instance-paginator-next-item').on('click', function(e){
                            construct.activePagerItem++;
                            rendersFlatPaginatedThumbnails(construct);
                        })

                        $('.instance-paginator-prev-item').on('click', function(e){
                            construct.activePagerItem--;
                            rendersFlatPaginatedThumbnails(construct);
                        })
                    });
            } else {
                resourceGetter.getRoots(function(data){appendThumbnails(data, construct)});
            }
        })

        return  {
            //return the construct object
            getBuilder:function() {
                return construct;
            }
        }
    }

    function navigate(id, construct) {
        construct.divForm.empty();
        construct.resourceGetter.getChildren(id, function(data){
              appendThumbnails(data, construct);
        })
    }

    function reload(construct){
        var id = $("."+construct.prefix+"-breadcrum-link").last().attr('data-id');
        navigate(id, construct);
    }

    function rendersFlatPaginatedThumbnails(construct) {
        activePage(construct.activePagerItem);
        construct.resourceGetter.getFlatPaginatedThumbnails(construct.activePagerItem, function(data){
            $('.res-block').remove();
            construct.div.prepend(data);
            setMenu(construct);
            $(".res-name").each(function(){
                formatResName($(this), 2, 20)
            });
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
            paginator += '<li data-page="'+i+'"><a class="instance-paginator-item" href="#">'+i+'</a></li>';
        }
        paginator += '<li><a href="#" class="instance-paginator-next-item">Next</a></li></ul></div>';

        return paginator;
    }

    function getSelectedItems(construct)
    {
        var ids = {};
        var i = 0;
        $('.'+construct.prefix+'-chk-instance:checked').each(function(index, element){
            ids[i] = element.value;
            i++;
        })

        return ids;
    }

    function setMenu(construct)
    {
        $('.'+construct.prefix+'-resource-menu-left').each(function(index, element){
            var resSpan =  $('#'+element.id).parents('.'+construct.prefix+'-res-block');
            var parameters = {};
            parameters.id = resSpan.attr('data-id')
            parameters.resourceId = resSpan.attr('data-resourceId');
            parameters.type = resSpan.attr('data-type');
            bindContextMenu(parameters, element, 'left', construct);
        });

        $('.'+construct.prefix+'-resource-menu-right').each(function(index, element){
            var resSpan =  $('#'+element.id).parents('.'+construct.prefix+'-res-block');
            var parameters = {};
            parameters.id = resSpan.attr('data-id');
            parameters.resourceId = resSpan.attr('data-resourceId');
            parameters.type = resSpan.attr('data-type');
            bindContextMenu(parameters, element, 'right', construct);
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

    function appendThumbnails(data, construct) {
        construct.div.empty();
        construct.div.append(data);
        setMenu(construct);
        setLayout(construct);
        resizeBreadcrums(construct);
        $(".res-name").each(function(){formatResName($(this), 2, 20)});
    }

    function bindContextMenu(parameters, menuElement, trigger, construct) {
        var type = parameters.type;
        var menuDefaultOptions = {
            selector: '#'+menuElement.id,
            trigger: trigger,
            //See the contextual menu documentation.
            callback: function(id, options) {
                //Finds and executes the action for the right menu item.
                findMenuObject(builder.menu[type], parameters, id, construct);
            }
        };

        menuDefaultOptions.items = builder.menu[type].items;
        $.contextMenu(menuDefaultOptions);
        //Left click menu.
    };

    //Finds wich menu was fired for a node.
    //@params items is the menu object used.
    function findMenuObject(items, parameters, menuItem, construct)
    {
        for (var property in items.items) {
            if (property === menuItem) {
                executeMenuActions(items.items[property], parameters, construct);
            } else {
                if (items.items[property].hasOwnProperty('items')) {
                    findMenuObject(items.items[property], parameters, menuItem, construct);
                }
            }
        }
    };

    function executeMenuActions (obj, parameters, construct)
    {
        //Removes the placeholders in the route
        var route = obj.route;
        var compiledRoute = route.replace('_instanceId', parameters.id);
        compiledRoute = compiledRoute.replace('_resourceId', parameters.resourceId);
        obj.async ? executeAsync(obj, parameters, compiledRoute, construct) : window.location = compiledRoute;
    }

    function executeAsync(obj, parameters, route, construct) {
        //Delete was a special case as every node can be removed.
        (obj.name === 'delete') ? removeNode(parameters, route, construct) : executeRequest(parameters, route, construct);
    };

    function removeNode(parameters, route, construct) {
        ClaroUtils.sendRequest(route, function(data, textStatus, jqXHR) {
            if (204 === jqXHR.status) {
                $('#'+construct.prefix+"-instance-"+parameters.id).remove();
            }
        });
    };

    function executeRequest(parameters, route, construct) {

        ClaroUtils.sendRequest(route, function(data) {
            //If there is a form, the submission handler above is used.
            //There is no handler otherwise.
            construct.divForm.empty().append(data).find('form').submit(function(e) {
                e.preventDefault();
                var action = construct.divForm.find('form').attr('action');
                action = action.replace('_instanceId', parameters.id)
                var id = construct.divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters, construct);
                });
            });
        });
    };

    function submissionHandler(xhr, parameters, construct) {
        //If there is a json response, a node was returned.
        if (xhr.getResponseHeader('Content-Type') === 'application/json') {
            reload(construct);
            console.debug(construct);
            construct.divForm.empty();
        //If it's not a json response, we append the response at the top of the tree.
        } else {
            construct.divForm.empty().append(xhr.responseText).find('form').submit(function(e) {
                e.preventDefault();
                var action = construct.divForm.find('form').attr('action');
                //If it's a form, placeholders must be removed (the twig form doesn't know the instance parent,
                //that's why placeholders are used).'
                action = action.replace('_instanceId', parameters.id);
                action = action.replace('_resourceId', parameters.resourceId);
                var id = construct.divForm.find('form').attr('id');
                ClaroUtils.sendForm(action, document.getElementById(id), function(xhr){
                    submissionHandler(xhr, parameters, construct);
                });
            });
        }
    };

    function setLayout(construct) {
        if(construct.flatChkBox.is(':checked')){
            construct.pasteButton.attr('disabled', 'disabled');
        } else {
            construct.activePagerItem = 1;
            if($.isEmptyObject(construct.pasteIds) || $("."+construct.prefix+"-breadcrum-link").size() == 1){
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

    function resizeBreadcrums(construct){
        var resize = function(index, divSize) {
            var size = getCrumsSize(construct);
            if(size > divSize && index >= 0) {
                var crumLink = (($("."+construct.prefix+"-breadcrum-link")).eq(index));
                formatResName(crumLink, 1, 9);
                index --;
                resize(index, divSize, construct);
            }
        }

        var getCrumsSize = function(construct){
            var crumsSize = 0;
            $("."+construct.prefix+"-breadcrum-link").each(function(index, element){
                crumsSize += ($(this).width());
            })

            return crumsSize;
        }

        var makeCrums = function(construct) {
            $("."+construct.prefix+"-breadcrum-link").each(function(index, element){
                element.innerHTML = " /"+element.title;
            })
        }

        makeCrums(construct);
        var divSize = $('#'+construct.prefix+'-res-breadcrums').width();
        var crumsIndex = ($("."+construct.prefix+"-breadcrum-link")).size();

        resize(crumsIndex, divSize, construct);
    }

    function findResourceSpan(elementId, construct) {
        var parent = $('#'+elementId).parents('.'+construct.prefix+'-res-block');
        return parent;
    }
})()

