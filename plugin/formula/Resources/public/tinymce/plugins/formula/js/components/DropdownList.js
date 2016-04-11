/**
 * Created by panos on 10/16/15.
 */
(function() {
    'use strict';

    window.DropdownList = function(areaPanel) {
        this.areaPanel = areaPanel;
        this.element = document.createElement("div");
        this.element.className = "fm-editor-dropdown fm-editor-dropdown-button";
        this.element.id = areaPanel.id+"-dropdown";
        this.createLabel();
        this.createList();
    };
    DropdownList.__name__ = ["DropdownList"];
    DropdownList.prototype = {
        createLabel: function() {
            this.labelButton = document.createElement("div");
            this.labelButton.className = "fm-editor-dropdown-label-button";
            this.labelButtonText = document.createElement("div");
            this.labelButtonText.className = "fm-editor-dropdown-label-button-text";
            this.labelButton.appendChild(this.labelButtonText);
            var arrowDown = document.createElement("div");
            arrowDown.className = "arrow-down";
            this.labelButton.appendChild(arrowDown);
            this.element.appendChild(this.labelButton);
            var dropdownList = this;
            this.labelButton.addEventListener("click", function(event){dropdownList.toggleList(event)});
        },
        createList: function() {
            this.listMountPoint = document.createElement("div");
            this.listMountPoint.className = "fm-editor-dropdown-list-mount-point";
            var listElement = document.createElement("div");
            listElement.className = "fm-editor-dropdown-list-items";
            this.listItemContainer = document.createElement("div");
            this.listItemContainer.className = "fm-editor-dropdown-list-items-container";
            listElement.appendChild(this.listItemContainer);
            this.listMountPoint.appendChild(listElement);
            this.element.appendChild(this.listMountPoint);
        },
        toggleList: function(event) {
            if (this.active) {
                this.hideList(event);
            } else {
                this.showList(event);
            }
            event.stopPropagation();
            return false;
        },
        hideList: function(event) {
            DomUtils.removeClass(this.listMountPoint, "active");
            this.active = false;
        },
        showList: function(event) {
            DomUtils.addClass(this.listMountPoint, "active");
            this.active = true;
        },
        addListItem: function(section, setActive) {
            var item = new DropdownListItem(section, this);
            this.listItemContainer.appendChild(item.element);
            if (setActive) {
                item.setActive();
            }
        },
        changeActiveItem: function(item) {
            if (this.activeItem !== null) {
                this.activeItem.unsetActive();
            }
            this.activeItem = item;
            this.labelButtonText.innerHTML = item.name;
            this.areaPanel.cloneButtonsToAreaBox(this.activeItem.section);
        },
        element: null,
        active: false,
        labelButton: null,
        labelButtonText: null,
        listMountPoint: null,
        listItemContainer: null,
        activeItem: null,
        areaPanel: null,
        __class__: DropdownList
    };
}());