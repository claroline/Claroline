import angular from 'angular/index'

import {ClarolineTranslator} from './index'

/**
 * Expose WillDurand JS translator as an angular service
 */
angular
  .module('translation', [])
  .service('Translator', () => ClarolineTranslator)
