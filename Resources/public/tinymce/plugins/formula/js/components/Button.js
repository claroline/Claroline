/**
 * Created by panos on 10/16/15.
 */
(function() {
    'use strict';

    window.Button = function(button, section) {
        this.id = button;
        this.element = document.createElement("span");
        this.element.className = "fm-editor-button math math-"+button;
        this.element.id = this.id+"-btn";
        this.section = section;
        this.createEvent();
        var button = this;
        this.element.addEventListener("click", function(event){
            button.addEquationEvent.clientX = event.clientX;
            button.addEquationEvent.clientY = event.clientY;
            button.element.dispatchEvent(button.addEquationEvent);
        });
    };
    Button.__name__ = ["Button"];
    Button.prototype = {
        createEvent: function() {
            this.addEquationEvent = document.createEvent('Event');
            this.addEquationEvent.formulaAction = this.id;
            this.addEquationEvent.initEvent('addEquation', true, true);
        },
        element: null,
        addEquationEvent: null,
        id: null,
        section: null,
        __class__: Button
    };
}());
