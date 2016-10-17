import angular from 'angular/index'
import WorkspaceService from './Services/WorkspaceService'


angular.module('workspace', [])
  .service('WorkspaceService', [
    '$http',
    '$q',
    'url',
    WorkspaceService
  ])
