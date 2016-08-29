/**
 * Manages Path show
 */

import template from './../Partial/show.html'

export default class PathShowDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'PathShowCtrl'
    this.controllerAs = 'pathShowCtrl'
    this.template = template
    this.scope = {
      path            : '=',  // Data of the path
      editEnabled     : '=',  // User is allowed to edit current path ?
      userProgression : '=?', // Progression of the current User
      totalProgression: '@'   // The number of total seen or done steps in path
    }
    this.bindToController = true
  }
}
