/**
 * Created by panos on 11/3/15.
 */
(function() {
    'use strict';

    window.AreaPanel = function(panel, toolbar) {
        this.visible = panel.visible;
        this.id = panel.id;
        this.sectionWidth = panel.width;
        this.element = document.createElement("div");
        this.element.className = "fm-editor-panel-area";
        this.element.id = this.id+"-panel-area";
        this.toolbar = toolbar;
        this.sections = [];
        this.panel = panel;

        this.createPanelArea();
        this.createSections(panel.sections);
    };
    AreaPanel.__name__ = ["AreaPanel"];
    AreaPanel.prototype = {
        createPanelArea: function() {
            //Panel area
            var panelAreaObj = this;
            this.panelArea = document.createElement("div");
            this.panelArea.className = "fm-editor-panel-area-container";
            this.panelAreaBox = document.createElement("div");
            this.panelAreaBox.className = "fm-editor-panel-area-box";
            this.panelArea.appendChild(this.panelAreaBox);
            this.element.appendChild(this.panelArea);
            //Button area
            this.panelButtonArea = document.createElement("div");
            this.panelButtonArea.className = "fm-editor-panel-area-button-container";
            this.panelButtonArea.innerHTML =
                "<div class='fm-editor-panel-area-moveup-button'><div class='arrow-up'></div></div>" +
                "<div class='fm-editor-panel-area-movedown-button'><div class='arrow-down'></div></div>" +
                "<div class='fm-editor-panel-area-popup-button'><div class='left-down-arrow'></div></div>";
            this.element.appendChild(this.panelButtonArea);
            var popupButton = this.panelButtonArea.getElementsByClassName("fm-editor-panel-area-popup-button")[0];
            popupButton.addEventListener("click", function(event){panelAreaObj.toggleSections(event);});
            var moveDownButton = this.panelButtonArea.getElementsByClassName("fm-editor-panel-area-movedown-button")[0];
            moveDownButton.addEventListener("click", function(event){panelAreaObj.scrollDownAreaBox();});
            var moveUpButton = this.panelButtonArea.getElementsByClassName("fm-editor-panel-area-moveup-button")[0];
            moveUpButton.addEventListener("click", function(event){panelAreaObj.scrollUpAreaBox();});
        },
        createSections: function(sections) {
            var sectionContainer = document.createElement("div");
            sectionContainer.className = "fm-editor-panel-area-section-container";
            var sectionBox = document.createElement("div");
            sectionBox.className = "fm-editor-panel-area-section-box";
            sectionBox.style.width = this.sectionWidth+"px";
            sectionContainer.appendChild(sectionBox);
            var sectionBoxContainer = document.createElement("div");
            sectionBoxContainer.className = "fm-editor-panel-area-section-box-container";
            sectionBox.appendChild(sectionBoxContainer);
            var sectionOverlapContainer = document.createElement("div");
            sectionOverlapContainer.className = "fm-editor-panel-area-section-overlap-container";
            sectionBoxContainer.appendChild(sectionOverlapContainer);
            var dropdownList = new DropdownList(this);
            sectionOverlapContainer.appendChild(dropdownList.element);
            for (var i = 0; i < sections.length; i++) {
                var section = sections[i];
                this.appendSection(section, sectionOverlapContainer, dropdownList, i==0);
            }
            this.sectionsMountPoint = document.createElement("div");
            this.sectionsMountPoint.className = "fm-editor-panel-area-section-mount-point";
            this.sectionsMountPoint.appendChild(sectionContainer);
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
            event.stopPropagation();
            return false;
        },
        showSections: function(event) {
            if (this.toolbar.activePanel != null) {
                this.toolbar.activePanel.hideSections(event);
            }
            this.sectionsMountPoint.style.display = "block";
            this.active = true;
            DomUtils.addClass(this.element, "active");
            this.toolbar.activePanel = this;
        },
        hideSections: function(event) {
            this.sectionsMountPoint.style.display = "none";
            this.active = false;
            DomUtils.removeClass(this.element, "active");
            this.toolbar.activePanel = null;
        },
        appendSection: function(section, sectionContainer, dropdownList, isActive) {
            var buttonSection = new ButtonSection(section, this);
            if (buttonSection.buttons.length > 0) {
                sectionContainer.appendChild(buttonSection.element);
                this.sections.push(buttonSection);
                dropdownList.addListItem(buttonSection, isActive);
            }
        },
        cloneButtonsToAreaBox: function(section) {
            this.panelAreaBox.innerHTML="";
            var buttonsContainer = document.createElement("div");
            buttonsContainer.className = "fm-editor-section-buttons";
            this.panelAreaBox.appendChild(buttonsContainer);
            for (var i = 0; i < section.buttons.length; i++) {
                var sectionButton = section.buttons[i];
                var newButton = new Button(sectionButton.id, section);
                buttonsContainer.appendChild(newButton.element);
            }
            this.panelAreaBox.style.marginTop = "0px";
        },
        scrollUpAreaBox: function() {
            var marginTop = this.getMarginTop();
            this.panelAreaBox.style.marginTop = Math.min(0, marginTop+56)+"px";
        },
        scrollDownAreaBox: function() {
            var height = parseInt(this.panelAreaBox.scrollHeight);
            var marginTop = this.getMarginTop();
            this.panelAreaBox.style.marginTop = Math.min(0, Math.max(-height + 56 + height%28, marginTop-56))+"px";
        },
        getMarginTop: function() {
            var marginTop = this.panelAreaBox.style.marginTop;
            if (marginTop.length == 0) marginTop = 0;
            return parseInt(marginTop);
        },
        redraw: function() {
            this.sections = [];
            this.panelArea.parentNode.removeChild(this.panelArea);
            this.panelButtonArea.parentNode.removeChild(this.panelButtonArea);
            this.sectionsMountPoint.parentNode.removeChild(this.sectionsMountPoint);
            this.createPanelArea();
            this.createSections(this.panel.sections);
        },
        element: null,
        panelArea: null,
        panelButtonArea: null,
        panelAreaBox: null,
        sectionsMountPoint: null,
        sectionWidth: null,
        visible: false,
        active: false,
        sections: null,
        panel: null,
        id: null,
        toolbar: null,
        __class__: AreaPanel
    };
}());
