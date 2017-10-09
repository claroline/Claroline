import angular from 'angular/index'

import {Translator} from './index'

/**
 * Expose WillDurand JS translator as an angular service
 */
angular
  .module('translation', [])
  .service('Translator', () => Translator)
