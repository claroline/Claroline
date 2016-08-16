/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import template from '../Partial/cursus_registration_informations.html'

export default class CursusRegistrationInformationsDirective {  
        
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
  }
}