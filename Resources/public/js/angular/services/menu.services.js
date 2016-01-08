/**
 * Created by nico on 8/01/16.
 */
var clarolineAPI = angular.module('clarolineMenu', []);

clarolineAPI.factory('clarolineMenu', function() {
    return {
        build: function (options) {
            var html = "<div class='btn-group' uib-dropdown is-open='status.isopen'>";
            var baseClass = options.class || 'btn btn-primary';
            html += "<button type='button' class='" + baseClass + "' uib-dropdown-toggle>";
            if (options.name) html += options.name;
            if (options.icon) html += "<i class='fa fa-cog'></i>";
            html += '&nbsp<span class="caret"></span>'
            html += "</button>";
            html += "<ul class='uib-dropdown-menu' role='menu' aria-labelledby='single-button'>";

            for (var i = 0; i < options.elements.length; i++) {
                html += "<li role='menuitem'><a href='#'>" + options.elements[i].name + "</a></li>";
            }

            html += "</ul></div>";
            return html;
        }
    }
});
