import TracksButtonController from '../Controller/TracksButtonController'
import trackButtonTemplate from '../Partial/tracks_button.html'

export default class TracksButtonDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = trackButtonTemplate
    this.replace = true
    this.controller = TracksButtonController
    this.controllerAs = 'tbc'
  }
}
