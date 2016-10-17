import angular from 'angular/index'
import UserService from './Services/UserService'

import '#/main/core/fos-js-router/module'

angular.module('authentication', [])
.service(
  'UserService', [
    '$http',
    '$q',
    'url',
    UserService
  ]
)
