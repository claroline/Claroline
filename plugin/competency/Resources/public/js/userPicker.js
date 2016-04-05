(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.UserPicker = Picker;

    /**
     * Initializes a user picker with a hash of options:
     *
     * - title:     title of the modal
     *
     *              (defaults to generic title)
     *
     * - callback:  function to be executed when picking is complete.
     *              It will be passed an object with the following attributes:
     *                  - target    (string: "user"|"group")
     *                  - id        (integer)
     *
     *              (defaults to empty function)
     *
     * @param options Object
     * @constructor
     */
    function Picker(options) {
        options = options || {};
        this.title = options.title;
        this.callback = options.callback || function () {};
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
        this.$pickerModal = Claroline.Modal.create(
            Twig.render(UserPickerForm, this.title ? { title: this.title } : {})
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
            var self = this;
            var $input = this.$pickerModal.find('input[type=text]');
            var targetTypeahead = this.selection.target === 'user' ?
                this.userTypeahead :
                this.groupTypeahead;

            this._displayStepTwo();
            this._bindInputListeners($input);

            targetTypeahead.initialize().done(function () {
                $input.typeahead(
                    { minLength: 1, highlight: true },
                    {
                        name: self.selection.target,
                        displayKey: 'name',
                        source: targetTypeahead.ttAdapter()
                    }
                );
            });
        } else {
            this.callback(this.selection);
        }
    };

    Picker.prototype._displayStepTwo = function () {
        if (this.selection.target === 'group') {
            // change the default (user) label
            this.$pickerModal
                .find('span#search-info')
                .text(Translator.trans('user_picker.enter_group_search', {}, 'competency') + ':');
        }

        this.$pickerModal.find('div.step-1').css('display', 'none');
        this.$pickerModal.find('div.step-2').css('display', 'block');
        this.$pickerModal.find('button[type=submit]').attr('disabled', 'disabled');

        ++this.currentStep;
    };

    Picker.prototype._bindInputListeners = function ($input) {
        var $submit = this.$pickerModal.find('button[type=submit]');
        var self = this;

        $input.on('typeahead:selected', function ($event, suggestion) {
            // update selection and enable form submission
            self.selection.id = suggestion.id;
            $submit.removeAttr('disabled');
        });

        $input.on('keypress', function () {
            if (self.selection.id) {
                // invalidate previous selection if new characters are entered
                self.selection.id = null;
                $submit.attr('disabled', 'disabled');
            }
        });
    };
})();