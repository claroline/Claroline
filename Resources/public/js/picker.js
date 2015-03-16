(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.CompetencyPicker = Picker;

    /**
     * Initializes a competency picker with a hash of options:
     *
     * - callback:  function to be executed when a competency is selected.
     *              It will be passed an object containing the id an type
     *              (ability/competency) of the target
     *
     * @param options Object
     * @constructor
     */
    function Picker(options) {
        options = options || {};
        this.callback = options.callback || function () {};
        this.$currentSelection = null;
        this.$pickerModal = null;
        this.areListenersInitialized = false;
    }

    /**
     * Opens the picker.
     */
    Picker.prototype.open = function () {
        this.$currentSelection = null; // reset in case picker has been opened before

        if (!this.areListenersInitialized) {
            this._initListeners();
        }

        Claroline.Modal.fromUrl(
            Routing.generate('hevinci_pick_framework'),
            function (modal) {
                this.$pickerModal = modal;
            }.bind(this)
        );
    };

    /**
     * Closes the picker.
     */
    Picker.prototype.close = function () {
        if (this.$pickerModal) {
            this.$pickerModal.modal('hide');
            this.$pickerModal = null;
        }
    };

    Picker.prototype._initListeners = function () {
        $(document).on('click', 'div.modal a.framework', this._onFrameworkSelection.bind(this));
        $(document).on('click', 'div.modal ul.framework li span.node-name', this._onCompetencySelection.bind(this));
        $(document).on('click', 'div.modal button#save', this._onValidation.bind(this));
        this.areListenersInitialized = true;
    };

    Picker.prototype._onFrameworkSelection = function (event) {
        event.preventDefault();
        var id = event.currentTarget.dataset.id;
        $.ajax(Routing.generate('hevinci_pick_competency', { id: id }))
            .done(function (data) {
                this.$pickerModal.find('.modal-body').html(data);
            }.bind(this))
            .error(function () {
                Claroline.Modal.error();
            });
    };

    Picker.prototype._onCompetencySelection = function (event) {
        if (this.$currentSelection) {
            this.$currentSelection.removeClass('selected');
        } else {
            this.$pickerModal.find('button#save').removeClass('disabled');
        }

        this.$currentSelection = $(event.currentTarget);
        this.$currentSelection.addClass('selected');
    };

    Picker.prototype._onValidation = function () {
        var $item = this.$currentSelection.parent();
        this.callback({
            id: $item.data('id'),
            type: $item.data('type')
        });
    };
})();