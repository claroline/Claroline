/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ModalWindow */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};

    Claroline.ResourceManager.Views.Breadcrumbs = Backbone.View.extend({
        tagName: 'div',
        events: {
            'click a': function (event) {
                event.preventDefault();
                this.dispatcher.trigger('breadcrumb-click', {
                    nodeId: event.currentTarget.getAttribute('data-node-id'),
                    isPickerMode: this.parameters.isPickerMode
                });
            }
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
        },
        render: function (nodes) {
            if (!this.parameters.isPickerMode) {
                //determine if is workspace mode and remove a part of elements in the breadcrumb
                if (this.parameters.isWorkspace) {
                    $('ul.breadcrumb li').slice(2).remove();
                } else {
                    $('ul.breadcrumb li:not(:first)').remove();
                }

                $('ul.breadcrumb').append(Twig.render(ResourceManagerBreadcrumbs, {'nodes': nodes}));
                $('body').on('click', 'ul.breadcrumb li a', function () {
                    event.preventDefault();
                    window.Claroline.ResourceManager.Controller.dispatcher.trigger('breadcrumb-click', {
                        nodeId: $(this).data('node-id'),
                        isPickerMode: false,
                        el: $(this)
                    });
                });

                // add current folder to the title of the panel
                if (nodes.length > 1) {
                    $('.panel .panel-heading .panel-title span').html(' - ' + $('ul.breadcrumb li').last().text());
                } else {
                    $('.panel .panel-heading .panel-title span').html('');
                }
            } else {
                $(this.el).addClass('breadcrumb');
                $(this.el).html(Twig.render(ResourceManagerBreadcrumbs, {
                    'nodes': nodes
                }));
            }
        }
    });
})();
