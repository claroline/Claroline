/**
 * Created by panos on 10/16/15.
 */
(function() {
    'use strict';

    window.DomUtils = {
        hasClass: function(el, className) {
            if (el.classList)
                return el.classList.contains(className);
            else
                return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
        },
        addClass: function(el, className) {
            if (el.classList)
                el.classList.add(className);
            else if (!this.hasClass(el, className)) el.className += " " + className;
        },
        removeClass: function(el, className) {
            if (el.classList)
                el.classList.remove(className)
            else if (this.hasClass(el, className)) {
                var reg = new RegExp('(\\s|^)' + className + '(\\s|$)')
                el.className=el.className.replace(reg, ' ')
            }
        },
        loadJsFile: function(filename, onLoadCallback, defaultFilename) {
            var fileref=document.createElement('script');
            if (defaultFilename) {
                fileref.onerror = function () {
                    DomUtils.loadJsFile(defaultFilename, onLoadCallback);
                };
            }
            fileref.onload = onLoadCallback;
            fileref.type = "text/javascript";
            fileref.src =  filename;
            document.getElementsByTagName("head")[0].appendChild(fileref);
        },
        replaceSVGUseWithGraphElements: function(svg) {
            var useElements = svg.getElementsByTagName("use");
            var originalElements = [];
            var newUseElements = [];
            // Get all use elements
            for (var i=0; i<useElements.length; i++) {
                var useElement = useElements[i];
                var originalElementId = useElement.getAttribute("href").replace("#","");
                var originalElement = document.getElementById(originalElementId).cloneNode(true);
                originalElement.id += "-c-"+(new Date()).getTime();
                var position = {};
                //For every element get all attributes and copy them to graph element
                for (var j=0; j<useElement.attributes.length; j++) {
                    var attribute = useElement.attributes[j];
                    if (attribute.nodeName !=="href" && attribute.nodeName !== "x" && attribute.nodeName !== "y") {
                        originalElement.setAttribute(attribute.nodeName, attribute.nodeValue);
                    } else if (attribute.nodeName == "x" || attribute.nodeName == "y") {
                        //If position attributes (x or y) are set, create a position element
                        position[attribute.nodeName] = attribute.nodeValue;
                    }
                }
                //If position element is set then add or change tranform attribute
                if (position.x) {
                    var positionStr = (position.x||0)+", "+(position.y||0);
                    var transform = originalElement.getAttribute("transform")||"";
                    if (transform !== "") transform +=" ";
                    transform += "translate("+positionStr+")";
                    originalElement.setAttribute("transform", transform);
                }
                originalElements.push(originalElement);
                newUseElements.push(useElement);
            }
            for (var i=0; i<originalElements.length; i++) {
                var tmp = newUseElements[i];
                newUseElements[i] = i;
                tmp.parentNode.replaceChild(originalElements[i], tmp);
            }
        }
    };

    HTMLTextAreaElement.prototype.insertAtCaret = function (text) {
        text = text || '';
        if (document.selection) {
            // IE
            this.focus();
            var sel = document.selection.createRange();
            sel.text = text;
        } else if (this.selectionStart || this.selectionStart === 0) {
            // Others
            var startPos = this.selectionStart;
            var endPos = this.selectionEnd;
            this.value = this.value.substring(0, startPos) +
                text +
                this.value.substring(endPos, this.value.length);
            this.selectionStart = startPos + text.length;
            this.selectionEnd = startPos + text.length;
        } else {
            this.value += text;
        }
    };

    HTMLTextAreaElement.prototype.getCaret = function() {
        if (this.selectionStart) {
            return this.selectionStart;
        } else if (document.selection) {
            this.focus();

            var r = document.selection.createRange();
            if (r == null) {
                return 0;
            }

            var re = this.createTextRange(),
                rc = re.duplicate();
            re.moveToBookmark(r.getBookmark());
            rc.setEndPoint('EndToStart', re);

            return rc.text.length;
        }
        return 0;
    }

    window.Url = {
        get get(){
            var vars= {};
            if(window.location.search.length!==0)
                window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value){
                    key=decodeURIComponent(key);
                    if(typeof vars[key]==="undefined") {vars[key]= decodeURIComponent(value);}
                    else {vars[key]= [].concat(vars[key], decodeURIComponent(value));}
                });
            return vars;
        }
    }
}());