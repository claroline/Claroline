/**
 * Created by panos on 10/16/15.
 */
(function() {
    'use strict';

    window.Editor = function(actions, panels, parameters) {
        this.lang = parameters.lang||this.lang;
        this.lang = this.lang.replace(/_[a-zA-Z]+/g,"").toLocaleLowerCase();
        this.mlang = parameters.mlang||this.mlang;
        this.initEquation = parameters.equation||this.initEquation;
        this.buildActionsHash(actions);
        this.panels = panels;
        var editor = this;
        DomUtils.loadJsFile("js/translations/"+this.lang+".js", function(){editor.init()}, "js/translations/fr.js");
    };
    Editor.__name__ = ["Editor"];
    Editor.prototype = {
        init: function() {
            this.element = document.createElement("div");
            this.element.className = "fm-editor";
            this.createToolbar(this.panels);
            this.createTextArea();
            this.createResultArea();
            document.getElementById("fm-editor-body").appendChild(this.element);
            this.createMatixActionPopup();
            var editor = this;
            this.element.addEventListener("addEquation", function(event){
                editor.addEquation(event);
            });
            document.addEventListener("click", function(e){
                if (editor.toolbar.activePanel != null) {
                    editor.toolbar.activePanel.hideSections(e);
                }
                editor.toolbar.mlangPanel.hideList();
                if (e.target.id.indexOf("btn")==-1||(e.target.id.indexOf("table")==-1&&e.target.id.indexOf("matrix")==-1)) {
                    editor.hideMatrixPopup();
                }
            });
            if (this.initEquation !== null) {
                this.insertEquationToTextarea(decodeURIComponent(this.initEquation));
            }
        },
        createToolbar: function(panels) {
            this.toolbar = new Toolbar(panels, this);
            this.element.appendChild(this.toolbar.element);
        },
        addEquation: function(event) {
            var actionHash = this.actions.get(event.formulaAction);
            var equation = actionHash[this.mlang];
            if (actionHash.matrix) {
                this.currentMatrixEquation = equation;
                this.showMatrixPopup(event.clientX, event.clientY);
            } else {
                var regex = /\{\{\$\}\}/g;
                var matches = equation.match(regex);
                if (matches && matches.length > 0) {
                    if (matches == 1) {
                        equation = equation.replace(regex, "x");
                    } else {
                        var cnt = 1;
                        equation = equation.replace(regex, function(){ return "x"+(cnt++);});
                    }
                }
                equation = equation.trim();
                this.insertEquationToTextarea(equation);
            }
        },
        insertEquationToTextarea : function(equation) {
            if (this.mlang == "latex") {
                equation = equation + " ";
            }
            this.textarea.insertAtCaret(equation);
            this.renderEquationToResultarea(this.textarea.value);
        },
        insertMatrixToTextarea: function() {
            var rows = parseInt(document.getElementById("fm-editor-matrix-rows").value);
            var columns = parseInt(document.getElementById("fm-editor-matrix-columns").value);
            var equation = this.currentMatrixEquation;
            this.hideMatrixPopup();
            if (equation !== null && rows > 0 && columns > 0) {
                var matrixCode = "";
                if (this.mlang == "latex") {
                    matrixCode = this.createMatrixCodeLatex(rows, columns);
                } else {
                    matrixCode = this.createMatrixCodeMml(rows, columns);
                }
                equation = equation.replace(/\{\{\$\}\}/g, matrixCode);
                equation = equation.trim();
                this.insertEquationToTextarea(equation);
            }
        },
        createMatrixCodeLatex: function(rows, columns) {
            var matrixCode = "";
            for (var j = 0; j < rows; j++) {
                for (var i = 0; i < columns; i++) {
                    matrixCode += " x_"+(i+1+j*columns);
                    if (i < (columns - 1)) {
                        matrixCode += " &";
                    } else {
                        matrixCode += " ";
                    }
                }
                if (j < (rows - 1)) {
                    matrixCode += "\\\\";
                }
            }
            return matrixCode;
        },
        createMatrixCodeMml: function(rows, columns) {
            var matrixCode = "";
            for (var j = 0; j < rows; j++) {
                matrixCode += "<mtr>";
                for (var i = 0; i < columns; i++) {
                    matrixCode += "<mtd><msub><mi>x</mi><mi>"+(i+1+j*columns)+"</mi></msub></mtd>";
                }
                matrixCode += "</mtr>";
            }
            return matrixCode;
        },
        renderEquationToResultarea: function(equation) {
            equation = equation || "";
            if (equation.trim() !== "") {
                if (this.mlang == "latex") {
                    equation = "$$" + equation.trim() + "$$";
                } else {
                    equation = "<math xmlns=\"http://www.w3.org/1998/Math/MathML\" mode=\"display\">"+ equation.trim() + "</math>";
                }
                this.toolbar.mlangPanel.disable();
                this.resultarea.innerHTML = equation;
                MathJax.Hub.Queue(["Typeset",MathJax.Hub,this.resultarea]);
            } else {
                this.toolbar.mlangPanel.enable();
                this.resultarea.innerHTML = "";
            }
        },
        createTextArea: function() {
            var textAreaContainer = document.createElement("div");
            textAreaContainer.className = "fm-editor-content-area";
            this.textarea = document.createElement("textarea");
            this.textarea.className = "fm-editor-content";
            textAreaContainer.appendChild(this.textarea);
            this.element.appendChild(textAreaContainer);
            var editor = this;
            this.textarea.addEventListener('input', function(){
                editor.renderEquationToResultarea(this.value);
            });
        },
        createResultArea: function() {
            var resultAreaContainer = document.createElement("div");
            resultAreaContainer.className = "fm-editor-result-area";
            var resultAreaLabel = document.createElement("div");
            resultAreaLabel.className = "fm-editor-result-area-label";
            resultAreaLabel.innerHTML = trans["result"]||"result";
            this.resultarea = document.createElement("div");
            this.resultarea.className = "fm-editor-result-area-inner";
            resultAreaContainer.appendChild(resultAreaLabel);
            resultAreaContainer.appendChild(this.resultarea);
            this.element.appendChild(resultAreaContainer);
        },
        createMatixActionPopup: function() {
            this.matrixPopupMount = document.createElement("div");
            this.matrixPopupMount.className = "fm-editor-matrix-popup-mount";
            var matrixPopupContainer = document.createElement("div");
            matrixPopupContainer.className = "fm-editor-matrix-popup-container";
            matrixPopupContainer.innerHTML = "<table>" +
                "<tr><td>"+(trans["rows"]||"rows")+":</td><td><input id=\"fm-editor-matrix-rows\" type='number' name='matrix-rows'/></td></tr>" +
                "<tr><td>"+(trans["columns"]||"columns")+":</td><td><input id=\"fm-editor-matrix-columns\" type='number' name='matrix-columns'/></td></tr>" +
                "<tr><td colspan='2'><button type='button' id='fm-editor-matrix-create-btn'>"+(trans["ok"]||"ok")+"</button></td></tr>" +
                "</table>";
            this.matrixPopupMount.appendChild(matrixPopupContainer);
            this.element.appendChild(this.matrixPopupMount);
            //Adding listeners
            var editor = this;
            this.matrixPopupMount.addEventListener("click", function(event){event.stopPropagation(); return false;});
            document.getElementById("fm-editor-matrix-create-btn").addEventListener("click", function(){
                editor.insertMatrixToTextarea();
            });
            document.getElementById("fm-editor-matrix-rows").addEventListener("keypress", function(event){
                if (event.which == 13 || event.keyCode == 13) {
                    document.getElementById("fm-editor-matrix-columns").focus();
                }
            });
            document.getElementById("fm-editor-matrix-columns").addEventListener("keypress", function(event){
                if (event.which == 13 || event.keyCode == 13) {
                    editor.insertMatrixToTextarea();
                }
            });
        },
        showMatrixPopup: function(x, y) {
            var rowsInput = document.getElementById("fm-editor-matrix-rows");
            rowsInput.value = 2;
            document.getElementById("fm-editor-matrix-columns").value = 2;
            DomUtils.addClass(this.matrixPopupMount, "active");
            this.matrixPopupMount.style.top = y-40+"px";
            this.matrixPopupMount.style.left = x-20+"px";
            rowsInput.focus();
        },
        hideMatrixPopup: function() {
            DomUtils.removeClass(this.matrixPopupMount, "active");
            this.currentMatrixEquation = null;
        },
        buildActionsHash: function(actions) {
            this.actions = new HashArray();
            for (var i = 0; i < actions.length; i++) {
                var action = actions[i];
                this.actions.set(action.id, action);
            }
        },
        getEquationPng: function(callback){
            var svg = this.resultarea.getElementsByTagName("svg")[0];
            if (svg) {
                svg = svg.cloneNode(true);
                var editor = this;
                DomUtils.replaceSVGUseWithGraphElements(svg);
                var image = new Image();
                image.onload = function() {
                    var canvas = document.createElement("canvas");
                    canvas.width = image.width;
                    canvas.height = image.height;
                    var context = canvas.getContext("2d");
                    context.drawImage(image, 0, 0);
                    var imgSrc = canvas.toDataURL('image/png');
                    var mlang = editor.mlang;
                    var equation = editor.textarea.value.trim();
                    callback(imgSrc, mlang, equation);
                };
                var svgAsXml = (new XMLSerializer).serializeToString(svg);
                image.src = 'data:image/svg+xml,' + encodeURIComponent(svgAsXml);
            } else {
                callback(null, null, null);
            }
        },
        panels: null,
        element: null,
        toolbar: null,
        actions: null,
        textarea: null,
        resultarea: null,
        currentMatrixEquation: null,
        matrixPopupMount: null,
        lang: "en",
        mlang: "latex",
        initEquation: null,
        __class__: Editor
    };
}());