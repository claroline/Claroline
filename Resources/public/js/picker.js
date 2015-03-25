(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.CompetencyPicker = Picker;

    /**
     * Initializes a competency picker with a hash of options:
     *
     * - includeAbilities:  whether abilities should be included when a framework
     *                      is displayed
     *
     *                      (defaults to true)
     *
     * - includeLevel:      whether a level selection form should be displayed as
     *                      the final step of the picking process
     *
     *                      (defaults to false)
     *
     * - callback:          function to be executed when picking is complete.
     *                      It will be passed an object with the following attributes:
     *                        - frameworkId (integer)
     *                        - targetId    (integer)
     *                        - targetType  (string: "ability"|"competency")
     *                        - levelId     (mixed: integer|null)
     *
     *                      (defaults to empty function)
     *
     * @param options Object
     * @constructor
     */
    function Picker(options) {
        options = options || {};
        this.includeAbilities = options.includeAbilities !== undefined ? !!options.includeAbilities : true;
        this.includeLevel = options.includeLevel || false;
        this.callback = options.callback || function () {};
        this.areListenersInitialized = false;
        this._initState();
    }

    /**
     * Opens the picker.
     */
    Picker.prototype.open = function () {
        this._initState(); // reset in case picker has been opened before

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

    Picker.prototype._initState = function () {
        this.$pickerModal = null;
        this.$currentCompetency = null;
        this.currentStep = 'framework';
        this.selection = {
            frameworkId: null,
            targetId: null,
            targetType: null,
            levelId: null
        };
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
        var params = { id: id, loadAbilities: this.includeAbilities ? 1 : 0 };
        $.ajax(Routing.generate('hevinci_pick_competency', params))
            .done(function (data) {
                this.$pickerModal.find('.modal-body').html(data);
                this.selection.frameworkId = id;
                this.currentStep = 'competency';
            }.bind(this))
            .error(function () {
                Claroline.Modal.error();
            });
    };

    Picker.prototype._onCompetencySelection = function (event) {
        var $target;

        if (this.$currentCompetency) {
            this.$currentCompetency.removeClass('selected');
        } else {
            this.$pickerModal.find('button#save').removeClass('disabled');
        }

        this.$currentCompetency = $(event.currentTarget);
        this.$currentCompetency.addClass('selected');
        $target = this.$currentCompetency.parent();
        this.selection.targetId = $target.data('id');
        this.selection.targetType = $target.data('type')

    };

    Picker.prototype._onValidation = function () {
        if (this.currentStep === 'competency' && this.includeLevel) {
            $.ajax(Routing.generate('hevinci_pick_level', { id: this.selection.frameworkId }))
                .done(function (data) {
                    this.$pickerModal.find('.modal-body').html(data);
                    this.currentStep = 'level';
                }.bind(this))
                .error(function () {
                    Claroline.Modal.error();
                });
        } else {
            if (this.currentStep === 'level') {
                this.selection.levelId = this.$pickerModal
                    .find('select.scale-levels option:selected')
                    .data('id');
            }

            this.callback(this.selection);
        }
    };
})();