(function () {
    'use strict';

    window.HeVinci = window.HeVinci || {};
    window.HeVinci.Flasher = Flasher;

    var categories = {
        'success': 'check-circle',
        'info': 'info-circle',
        'warning': 'exclamation-circle',
        'danger': 'exclamation-triangle'
    };

    /**
     * Initializes a flash handler according to a hash of options:
     *
     * - element:  the DOM element in which the messages must be prepended
     *             (defaults to document body)
     * - animate:  whether the height of messages must be animated
     *             (defaults to true)
     * - useIcons: whether icons associated to the message category
     *             must be prepended
     *             (defaults to true)
     *
     * @param options Object
     * @constructor
     */
    function Flasher(options) {
        options = options || {};
        this._refElement = options.element || document.body;
        this._mustAnimate = options.animate !== false;
        this._mustUseIcons = options.useIcons !== false;
        this._container = document.createElement('div');
        this._container.className = 'flash-box';

        if (this._refElement.firstChild) {
            this._refElement.insertBefore(
                this._container,
                this._refElement.firstChild
            );
        } else {
            this._refElement.appendChild(this._container);
        }

        this._flash = document.createElement('div');
        this._flash.role = 'alert alert-dismissible';
    }

    /**
     * Displays a flash message.
     *
     * @param message   String  The text/html message to be displayed
     * @param category  String  The category of the message, i.e. "success", "info",
     *                          "warning" or "danger" (defaults to "success")
     */
    Flasher.prototype.setMessage = function (message, category) {
        if (Object.keys(categories).indexOf(category) === -1) {
            category = 'success';
        }

        if (!this._mustAnimate) {
            this._updateMessage(message, category);
        } else {
            this._animate(function () {
                this._updateMessage(message, category);
            }.bind(this));
        }
    };

    Flasher.prototype._updateMessage = function (message, category) {
        var closeButton =
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
            + '<span aria-hidden="true">&times;</span></button>';
        var icon = this._mustUseIcons ?
            '<i class="fa fa-' + categories[category] + '"></i> ' :
            '';
        this._flash.className = 'alert alert-' + category;
        this._flash.innerHTML = closeButton + icon + message;
        this._container.appendChild(this._flash);
    };

    // TODO: find a better way to animate the alert (in a way bootstrap is happy with...)
    Flasher.prototype._animate = function (updateFunction) {
        // hide and update new message
        this._container.style.height = '0px';
        this._flash.style.visibility = 'hidden';
        this._flash.style.position = 'absolute';
        updateFunction();

        // show message
        this._flash.style.visibility = 'visible';
        this._flash.style.position = 'static';
        this._container.style.transition = 'height 0.4s';
        this._container.style.height = this._flash.clientHeight + 22 + 'px';

        setTimeout(function () {
            // allow container to shrink if alert is dismissed
            this._container.style.maxHeight = this._container.style.height;
            this._container.style.height = 'auto';
        }.bind(this), 500);
    };
})();