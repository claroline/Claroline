/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global Translator */
/* global ModalWindow */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = Claroline.ResourceManager.Views;

    views.Master = Backbone.View.extend({
        outerEvents: {
            'directory-data': 'render',
            'open-picker': 'openAsPicker',
            'close-picker': 'closeAsPicker'
        },
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.wrapper = null;
            this.isAppended = false;
            this.pickerDirectoryId = null;
            this.buildElement();
            _.each(this.outerEvents, function (method, event) {
                this.dispatcher.on(event + '-' + this.parameters.viewName, this[method], this);
            }, this);
            this.dispatcher.on('save-picker-directory', function (event) {
                this.pickerDirectoryId = event.directoryId;
            }, this);
            this.dispatcher.on('open-directory', function (event) {
                this.parameters.currentDirectoryId = event.nodeId;
            }, this);
        },
        buildElement: function () {
            this.el.className = 'main resource-manager tabpanel';
            this.wrapper = this.$el;
            this.subViews = {
                tabpanes: new views.Tabpanes(this.parameters, this.dispatcher)
            };

            if (this.parameters.isPickerMode) {
                this.el.className = 'picker resource-manager';
                this.el.id = 'picker-' + this.parameters.viewName;
                this.$el.html(Twig.render(ModalWindow, {
                    'modalId': 'picker-' + this.parameters.viewName,
                    'header' : Translator.trans('resource_picker', {}, 'platform'),
                    'body': '',
                    'style': 'z-index: 2000;'
                }));
                this.wrapper = this.$('.modal-body');
            } else {
                this.subViews.form = new views.Form(this.dispatcher);
                this.subViews.rights = new views.Rights(this.dispatcher);
                this.subViews.confirm = new views.Confirm(this.dispatcher);
            }

            if (this.parameters.isTinyMce) {
                this.subViews.tabs = new views.Tabs(this.parameters, this.dispatcher);
            }
        },
        openAsPicker: function () {
            this.dispatcher.trigger('open-directory', {
                nodeId: this.pickerDirectoryId || this.parameters.directoryId,
                view: this.parameters.viewName,
                fromPicker: true
            });
        },
        closeAsPicker: function () {
            if (this.parameters.isPickerMode && this.isAppended) {
                this.$('.modal').modal('hide');
            }
        },
        render: function () {
            if (!this.isAppended) {
                this.parameters.parentElement.append(this.$el);

                if (this.parameters.isTinyMce) {
                    this.wrapper.append(this.subViews.tabs.el);
                }

                this.wrapper.append(this.subViews.tabpanes.el);

                if (!this.parameters.isPickerMode) {
                    this.wrapper.append(this.subViews.form.el);
                    this.wrapper.append(this.subViews.rights.el);
                    this.wrapper.append(this.subViews.confirm.el);
                }

                this.isAppended = true;
            }

            if (this.parameters.isPickerMode) {
                this.$('.modal').modal('show');
            }
        }
    });
})();
