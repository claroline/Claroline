/**
 * Resource module
 */

import angular from 'angular/index'

import '#/main/core/asset/module'
import '#/main/core/fos-js-router/module'
import '../utils/module'
import '../confirm/module'

import ResourceService from './Service/ResourceService'

angular
  .module('Resource', [
    'ui.fos-js-router',
    'ui.asset',
    'Utils',
    'Confirm'
  ])
  .service('ResourceService', [
    '$q',
    '$http',
    'url',
    'IdentifierService',
    ResourceService
  ])
