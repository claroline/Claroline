/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
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
})();
