/**
 * Created by panos on 5/30/16.
 */
import angular from 'angular/index'

export default class PortalSearch {
  constructor() {
    this.options = angular.extend(this._defaultOptions, this.options)
  }

  submit() {
    this.options.onSearchClick.apply(this, [this.options.query])
  }

  get _defaultOptions() {
    return {
      'resourceType': 'all',
      'query': '',
      'onSearchClick': () => {}
    }
  }
}