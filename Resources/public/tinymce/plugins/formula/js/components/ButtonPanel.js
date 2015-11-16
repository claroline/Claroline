/**
 * Created by panos on 10/16/15.
 */
(function() {
    'use strict';

    window.ButtonPanel = function(panel, toolbar) {
        this.visible = panel.visible;
        this.id = panel.id;
        this.name = trans[panel.id]||panel.id;
        this.sectionWidth = panel.width;
        this.element = document.createElement("div");
        this.element.className = "fm-editor-panel";
        this.element.id = this.id+"-panel";
        this.toolbar = toolbar;
        this.panel = panel;
        this.sections = [];
        this.createPanelButton();
        this.createSections(panel.sections);
    };
    ButtonPanel.__name__ = ["ButtonPanel"];
    ButtonPanel.prototype = {
        createPanelButton: function() {
            this.buttonElement = document.createElement("div");
            this.buttonElement.className = "fm-editor-panel-button";
            var iconDiv = document.createElement("div");
            iconDiv.className = "fm-editor-panel-button-icon math math-"+this.id+"-btn";
            var labelDiv = document.createElement("div");
            labelDiv.className = "fm-editor-panel-button-label";
            labelDiv.innerHTML = this.name;
            var arrowDiv = document.createElement("div");
            arrowDiv.className = "arrow-down";
            this.buttonElement.appendChild(iconDiv);
            this.buttonElement.appendChild(labelDiv);
            this.buttonElement.appendChild(arrowDiv);
            this.element.appendChild(this.buttonElement);
            var buttonPanel = this;
            this.buttonElement.addEventListener("click", function(event){buttonPanel.toggleSections(event);});
        },
        createSections: function(sections) {
            var sectionDiv = document.createElement("div");
            sectionDiv.className = "fm-editor-section-container";
            sectionDiv.style.width = this.sectionWidth+"px";
            for (var i = 0; i < sections.length; i++) {
                var section = sections[i];
                this.appendSection(section, sectionDiv);
            }
            this.sectionsMountPoint = document.createElement("div");
            this.sectionsMountPoint.className = "fm-editor-section-mount-point";
            this.sectionsMountPoint.appendChild(sectionDiv);
            this.element.appendChild(this.sectionsMountPoint);
        },
        toggleSections: function(event) {
            if (this.sectionsMountPoint != null) {
                if (this.active) {
                    this.hideSections(event);
                } else {
                    this.showSections(event);
                }
            }
            this.toolbar.mlangPanel.hideList();
            event.stopPropagation();
            return false;
        },
        showSections: function(event) {
            if (this.toolbar.activePanel != null) {
                this.toolbar.activePanel.hideSections(event);
            }
            this.active = true;
            DomUtils.addClass(this.element, "active");
            var finalWidth = this.buttonElement.getBoundingClientRect().left+this.sectionWidth;
            if (finalWidth > document.body.clientWidth) {
                this.sectionsMountPoint.style.left = -this.sectionWidth+this.buttonElement.clientWidth+"px";
            } else {
                this.sectionsMountPoint.style.left = "-1px";
            }
            this.toolbar.activePanel = this;
        },
        hideSections: function(event) {
            this.active = false;
            DomUtils.removeClass(this.element, "active");
            this.toolbar.activePanel = null;
        },
        appendSection: function(section, sectionDiv) {
            var buttonSection = new ButtonSection(section, this);
            if (buttonSection.buttons.length > 0) {
                sectionDiv.appendChild(buttonSection.element);
                this.sections.push(buttonSection);
            }
        },
        redraw: function() {
            this.sections = [];
            this.sectionsMountPoint.parentNode.removeChild(this.sectionsMountPoint);
            this.createSections(this.panel.sections);
        },
        element: null,
        sectionsMountPoint: null,
        buttonElement: null,
        sectionWidth: null,
        visible: false,
        active: false,
        sections: null,
        panel: null,
        id: null,
        name: null,
        toolbar: null,
        __class__: ButtonPanel
    };
}());
