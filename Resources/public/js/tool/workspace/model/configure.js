/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

'use_strict';

var resourceManager = window.Claroline.ResourceManager;

var pickerCopy = resourceManager.createPicker('copy', {}, false);
var pickerLink = resourceManager.createPicker('link', {}, false);

$('#add-resnode-copy').on('click', function(event) {
    resourceManager.picker('copy', 'open');
});

$('#add-resnode-link').on('click', function(event) {
    resourceManager.picker('link', 'open');
});