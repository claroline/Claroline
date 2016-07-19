/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from '../Partial/scormResults.html'

export default class ScormResultsDirective {

  constructor() {
    this.controllerAs = 'src'
    this.bindToController = true
    this.controller = 'ScormResultsCtrl'
    this.template = template
  }
}