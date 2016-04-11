/**
 * Created by panos on 11/12/15.
 */
(function() {
    'use strict';

    window.MlangPanel = function(toolbar) {
        this.id = "mlang";
        this.element = document.createElement("div");
        this.element.className = "fm-editor-panel";
        this.toolbar = toolbar;
        this.currentMlang = this.toolbar.editor.mlang;
        this.createPanelButton();
        this.createList();
    };
    MlangPanel.__name__ = ["MlangPanel"];
    MlangPanel.prototype = {
        createPanelButton: function() {
            var buttonDiv = document.createElement("div");
            buttonDiv.className = "fm-editor-panel-button";
            var iconDiv = document.createElement("div");
            iconDiv.className = "fm-editor-panel-button-icon math math-"+this.id+"-btn";
            this.labelButtonText = document.createElement("div");
            this.labelButtonText.className = "fm-editor-panel-button-label";
            this.labelButtonText.innerHTML = this.mlangs[this.currentMlang];
            var arrowDiv = document.createElement("div");
            arrowDiv.className = "arrow-down";
            buttonDiv.appendChild(iconDiv);
            buttonDiv.appendChild(this.labelButtonText);
            buttonDiv.appendChild(arrowDiv);
            this.element.appendChild(buttonDiv);
            var mlangPanel = this;
            buttonDiv.addEventListener("click", function(event){
                if (mlangPanel.enabled) mlangPanel.toggleList(event);
                if (mlangPanel.toolbar.activePanel != null) {
                    mlangPanel.toolbar.activePanel.hideSections(event);
                }
                event.stopPropagation();
                return false;
            });
        },
        createList: function() {
            this.listMountPoint = document.createElement("div");
            this.listMountPoint.className = "fm-editor-dropdown-list-mount-point";
            var listElement = document.createElement("div");
            listElement.className = "fm-editor-dropdown-list-items";
            this.listItemContainer = document.createElement("div");
            this.listItemContainer.className = "fm-editor-dropdown-list-items-container";
            // Create latex and mml dropdown items
            var latexSection = new ButtonSection({id:"latex", name: "LaTeX", children:[]}, this);
            var latexItem = new DropdownListItem(latexSection, this);
            this.listItemContainer.appendChild(latexItem.element);
            var mmlSection = new ButtonSection({id:"mml", name: "MathML", children:[]}, this);
            var mmlItem = new DropdownListItem(mmlSection, this);
            this.listItemContainer.appendChild(mmlItem.element);
            if(this.currentMlang == "latex") {
                latexItem.setActive();
            } else {
                mmlItem.setActive();
            }
            listElement.appendChild(this.listItemContainer);
            this.listMountPoint.appendChild(listElement);
            this.element.appendChild(this.listMountPoint);
        },
        toggleList: function(event) {
            if (this.active) {
                this.hideList();
            } else {
                this.showList();
            }
        },
        showList: function() {
            DomUtils.addClass(this.listMountPoint, "active");
            this.active = true;
        },
        hideList: function() {
            DomUtils.removeClass(this.listMountPoint, "active");
            this.active = false;
        },
        changeActiveItem: function(item) {
            if (this.activeItem != null) {
                this.activeItem.unsetActive();
                this.toolbar.editor.mlang = item.id;
                this.toolbar.redraw();
            }
            this.activeItem = item;
            this.labelButtonText.innerHTML = item.name;
        },
        disable: function() {
            if (this.enabled){
                this.enabled = false;
                DomUtils.addClass(this.element, "disabled");
            }
        },
        enable: function() {
            if (!this.enabled) {
                this.enabled = true;
                DomUtils.removeClass(this.element, "disabled");
            }
        },
        enabled: true,
        active: false,
        activeItem: null,
        element: null,
        labelButtonText: null,
        listItemContainer: null,
        listMountPoint: null,
        currentMlang: null,
        toolbar: null,
        mlangs: {"latex": "LaTeX", "mml": "MathML"},
        __class__: MlangPanel
    };
}());