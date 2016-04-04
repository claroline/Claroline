/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function getCurrentSearch(query) {
    var pattern = /([^,]+)$/;
    var result = pattern.exec(query);

    if (result && result[1]) {

        return result[1].trim();
    } else {

        return '';
    }
}

var TagTypeAhead = function (options) {
    this.allTags = (options['tags'] !== undefined) ? options['tags'] : [];
    this.selector = (options['selector'] !== undefined) ? options['selector'] : '#tag_form_tags';
    
    $(this.selector).typeahead({
        source: this.allTags,
        updater: function (item) {

            return this.$element.val().replace(/[^,]*$/,'') + item + ',';
        },
        matcher: function (item) {
            var search = getCurrentSearch(this.query);

            if (!search) {

                return false;
            } else {

                return ~item.toLowerCase().indexOf(search.toLowerCase())
            }
        },
        highlighter: function (item) {
            var search = getCurrentSearch(this.query);
            search = search.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
            var regex = new RegExp('(' + search + ')', 'ig');

            return item.replace(regex, function (match) {

                return '<strong>' + match + '</strong>'
            });
        }
    });
};