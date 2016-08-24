/**
 * Resource module
 */

import 'angular/index'

import '../utils/module'
import '../confirm/module'

import ResourceService from './Service/ResourceService'

angular
  .module('Resource', [
    'Utils',
    'Confirm'
  ])
  .service('ResourceService', [
    'IdentifierService',
    ResourceService
  ])
