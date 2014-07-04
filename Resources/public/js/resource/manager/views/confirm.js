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

    Claroline.ResourceManager.Views.Confirm = Backbone.View.extend({
        events: {
            'click #confirm-ok': 'confirm'
        },
        initialize: function (dispatcher) {
            this.dispatcher = dispatcher;
            this.callback = null;
            this.dispatcher.on('confirm', this.render, this);
            this.dispatcher.on('close-confirm', this.close, this);
        },
        confirm: function () {
            if (this.callback) {
                this.callback();
            }
        },
        close: function () {
            this.$('.modal').modal('hide');
        },
        render: function (event) {
            this.callback = event.callback;
            this.$el.html(Twig.render(ModalWindow, {
                header: event.header,
                body: event.body,
                confirmFooter: true,
                modalId: 'confirm-modal'
            }));
            this.$('.modal').modal('show');
        }
    });
})();
