import angular from 'angular/index'

import {asset} from './index'

angular
  .module('ui.asset', [])
  .filter('asset', () => asset)
