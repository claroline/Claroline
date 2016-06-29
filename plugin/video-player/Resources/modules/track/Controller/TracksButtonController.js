import tracksTemplate from '../Partial/tracks.html'

export default class TracksButtonController {
  constructor ($uibModal, $http) {
    this.$uibModal = $uibModal
    this.tracks = window['tracks']
  }

  openTracks () {
    this.$uibModal.open({
      template: tracksTemplate,
      controller: 'TracksModalController',
      controllerAs: 'tmc',
      resolve: {
        tracks: () => {
          return this.tracks}
      }
    })
  }
}
