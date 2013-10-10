(function () {

    var config = {
       "include_jquery":false,
       "tinymce_jquery":true,
       "use_callback_tinymce_init":true,
       "selector":".tinymce",
       "theme":{
          "simple":{
             "theme":"modern",
             "menubar":false,
             "toolbar1":"bold italic underline | ressourceLinker | alignleft aligncenter alignright alignjustify | fullscreenToggle",
             "language":"fr_FR"
          },
          "medium":{
             "theme":"modern",
             "plugins":[
                " -ressourceLinker fullscreen, emoticons code autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars  fullscreen",
                "insertdatetime media nonbreaking save table contextmenu directionality"
             ],
             "menubar":false,
             "statusbar": false,
             "toolbar1":"bold,italic,underline,undo,redo,removeformat cleanup code | ressourceLinker | fullscreenToggle ",
             "toolbar2":"alignleft aligncenter alignright alignjustify | emoticons lists link image charmap print preview hr anchor pagebreak ",
             "relative_urls":false,
             "language":"fr_FR"
          },
          "advanced":{
             "plugins":[
                "-ressourceLinker -fullscreenToggle advlist autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen",
                "insertdatetime media nonbreaking save table contextmenu directionality",
                "emoticons template paste textcolor"
             ],
             "menubar":false,
             "toolbar1":" undo redo | styleselect | bold italic | ressourceLinker fullscreenToggle | advlist autolink lists link image charmap print preview hr anchor pagebreak ",
             "toolbar2":"print preview media | forecolor backcolor emoticons | stfalcon | insertdatetime media nonbreaking save table contextmenu directionality",
             "toolbar3":"searchreplace wordcount visualblocks visualchars code",
             "image_advtab":true,
             "templates":[
                {
                   "title":"Test template 1",
                   "content":"Test 1"
                },
                {
                   "title":"Test template 2",
                   "content":"Test 2"
                }
             ],
             "language":"fr_FR"
          }
       },
       "tinymce_buttons":{
          "ressourceLinker":{
             "title":"Resource Linker",
             "image":"\/vostro\/Claroline\/web\/"
          },
          "fullscreenToggle":{
             "title":"toggle",
             "image":"\/vostro\/Claroline\/web\/"
          }
       },
       "external_plugins":{
          "imagemanager":{
             "url":"\/vostro\/Claroline\/web\/bundles\/clarolinecore\/js\/tinymce\/config.js"
          }
       },
       "language":"fr_FR",
       "jquery_script_url":"\/vostro\/Claroline\/web\/bundles\/stfalcontinymce\/vendor\/tinymce\/tinymce.jquery.min.js"
    };

    'use strict';

    window.Claroline = window.Claroline || {};
    var utilities = window.Claroline.Utilities = {};

    /**
     * Truncates a text and/or splits it into multiple lines if its length is greater
     * than maxLengthPerLine * maxLines. Truncation is marked with '...'. Multilines
     * use the html break, and avoid slicing words whenever possible.
     */
    utilities.formatText = function (text, maxLengthPerLine, maxLines) {
        if (text.length <= maxLengthPerLine) {
            return text;
        }

        maxLengthPerLine = maxLengthPerLine || 20;
        maxLines = maxLines || 1;
        var lines = new Array(maxLines),
            curLine = 0,
            curText = text,
            blankCuts = 0,
            newText = '';

        while (curText.length > 0 && curLine < maxLines) {
            lines[curLine] = curText.substr(0, maxLengthPerLine);

            if (curLine !== maxLines - 1) {

                for (var i = lines[curLine].length; i > 0; i--) {
                    var c = lines[curLine].charAt(i - 1);

                    if (!((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') || (c >= '0' && c <= '9'))) {
                        blankCuts++;
                        break;
                    }
                }

                if (i > 0) {
                    lines[curLine] = lines[curLine].substr(0, i);
                }

                curText = curText.substr(lines[curLine].length, curText.length);
            }
            curLine++;
        }

        if (curText.length > 0) {
            if (lines[curLine - 1].length > maxLengthPerLine ||
                ((text.length + blankCuts) > (maxLengthPerLine * maxLines))) {
                lines[curLine - 1] = lines[curLine - 1].substr(0, maxLengthPerLine - 3);
                lines[curLine - 1] = lines[curLine - 1] + '...';
            }
        }

        for (var j = 0; j < lines.length; ++j) {
            newText += j === lines.length - 1 ? lines[j] : lines[j] + '<br/>';
        }

        return newText;
    };

    /**
     * Returns the checked value of a combobox form.
     */
    utilities.getCheckedValue = function (radioObj) {
        if (!radioObj) {
            return '';
        }

        var radioLength = radioObj.length;

        if (radioLength === undefined) {
            if (radioObj.checked) {
                return radioObj.value;
            } else {
                return '';
            }
        }

        for (var i = 0; i < radioLength; i++) {
            if (radioObj[i].checked) {
                return radioObj[i].value;
            }
        }
        return '';
    };

    utilities.tinyMceInit = function() {
        tinyMCE.init({selector:'.tinymce', });
    };

    utilities.tinyMceAddInstance = function(elementId) {
        var newConfig = _.clone(config);
        newConfig.selector = '#'+elementId;
        initTinyMCE(newConfig);
    };

    utilities.tinyMceRemoveInstance = function(elementId) {
        tinyMCE.remove(elementId);
    };
})();
