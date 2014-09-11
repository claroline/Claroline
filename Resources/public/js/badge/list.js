/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){
    var locationhash = window.location.hash;
    if (locationhash.substr(0,2) == "#!") {
        $("a[href='#" + locationhash.substr(2) + "']").tab("show");
    }
});