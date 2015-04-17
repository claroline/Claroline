(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.UserPicker = Picker;

    function Picker() {
        this.$pickerModal = null;
        this.areListenersInitialized = false;
        this.userTypeahead = this._makeTypeahead('user');
        this.groupTypeahead = this._makeTypeahead('group');
    }

    /**
     * Opens the picker.
     */
    Picker.prototype.open = function () {
        this._initState(); // reset in case picker has been opened before
        if (!this.areListenersInitialized) this._initListeners();
        this.$pickerModal = Claroline.Modal.create(Twig.render(UserPickerForm));
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
        this.currentStep = 1;
        this.selection = {
            target: 'user',
            id: null
        }
    };

    Picker.prototype._makeTypeahead = function (target) {
        return new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: Routing.generate('hevinci_suggest_' + target, { query: 'QUERY' }),
                wildcard: 'QUERY'
            }
        });
    };

    Picker.prototype._initListeners = function () {
        $(document).on('change', 'div.user-picker input[type=radio]', this._onTargetChange.bind(this));
        $(document).on('click', 'div.user-picker button[type=submit]', this._onSubmit.bind(this));
        this.areListenersInitialized = true;
    };

    Picker.prototype._onTargetChange = function (event) {
        this.selection.target = event.currentTarget.value;
    };

    Picker.prototype._onSubmit = function (event) {
        event.preventDefault();

        if (this.currentStep === 1) {
            this.$pickerModal.find('div.step-1').css('display', 'none');
            this.$pickerModal.find('div.step-2').css('display', 'block');
            var $input = this.$pickerModal.find('input[type=text]');
            var self = this;

            $input.on('typeahead:selected', function ($event, suggestion) {
                console.log(suggestion);
                // update selection.target
                // if key press, invalidate
            });

            var targetTypeahead = this.selection.target ? this.userTypeahead : this.groupTypeahead;

            targetTypeahead.initialize(true).done(function () {
                // without this, bloodhound keep suggesting already
                // added entries without making a new http request
                targetTypeahead.clearRemoteCache();
                $input.typeahead(
                    {
                        minLength: 1,
                        highlight: true
                    },
                    {
                        name: self.selection.target,
                        displayKey: 'name',
                        source: targetTypeahead.ttAdapter()
                    }
                );
            });

            event.currentTarget.disabled = true;
        }
    };
})();