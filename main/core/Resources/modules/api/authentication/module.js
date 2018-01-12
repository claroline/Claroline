import angular from 'angular/index'
import UserService from './Services/UserService'

import '#/main/core/api/router/module'

angular.module('authentication', [])
.service(
  'UserService', [
    '$http',
    '$q',
    'url',
    UserService
  ]
)
