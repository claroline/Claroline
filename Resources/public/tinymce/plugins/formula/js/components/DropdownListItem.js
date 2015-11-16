/**
 * Created by panos on 11/5/15.
 */
(function() {
    'use strict';

    window.DropdownListItem = function(section, dropdownList) {
        this.id = section.id;
        this.name = section.name;
        this.element = document.createElement("div");
        this.element.className = "fm-editor-dropdown-list-item";
        this.element.id = this.id+"-list-item";
        this.section = section;
        this.dropdownList = dropdownList;
        this.element.innerHTML = this.name;
        this.addListener();
    };
    DropdownListItem.__name__ = ["DropdownListItem"];
    DropdownListItem.prototype = {
        addListener: function() {
            var item = this;
            this.element.addEventListener("click", function(event){item.changeActive(event);});
        },
        changeActive: function(event) {
            this.setActive();
            this.dropdownList.hideList(event);
            event.stopPropagation();
            return false;
        },
        setActive: function() {
            this.dropdownList.changeActiveItem(this);
            this.section.setActive();
            if (!this.active) {
                DomUtils.addClass(this.element, "active");
                this.active = true;
            }
        },
        unsetActive: function() {
            this.section.unsetActive();
            if (this.active) {
                DomUtils.removeClass(this.element, "active");
                this.active = false;
            }
        },
        element: null,
        active: false,
        id: null,
        name: null,
        section: null,
        dropdownList: null,
        __class__: DropdownListItem
    };
}());