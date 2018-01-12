/**
 * Resource module
 */

import angular from 'angular/index'

import '#/main/core/scaffolding/asset/module'
import '#/main/core/api/router/module'
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
