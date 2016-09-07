import langs from '#/main/core/form/Field/Lang/iso'
import trackForm from '../Form/form'
import editTemplate from '../Partial/edit.html'

/* global Routing */
/* global Translator */

export default class TracksModalController {
  constructor (tracks, FormBuilderService, ClarolineAPIService, $uibModal) {
    this.tracks = tracks
    this.newTrack = {}
    this.FormBuilderService = FormBuilderService
    this.ClarolineAPIService = ClarolineAPIService
    this.$uibModal = $uibModal
    this.trackForm = trackForm
  }

  onCreate () {
    this.newTrack['label'] = langs[this.newTrack.lang]['nativeName']

    this.FormBuilderService.submit(
      Routing.generate('api_post_video_track', {video: window['videoId']}),
      {track: this.newTrack}
    ).then(d => {
      this.tracks.push(d.data)
    })
  }

  onDelete (track) {
    const url = Routing.generate('api_delete_video_track', {track: track.id})
    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      function () {
        this.ClarolineAPIService.removeElements([track], this.tracks)
      }.bind(this),
      Translator.trans('delete_track', {}, 'platform'),
      Translator.trans('delete_track_confirm', 'platform')
    )
  }

  onEdit (track) {
    const modalInstance = this.$uibModal.open({
      template: editTemplate,
      controller: 'TrackEditModalController',
      controllerAs: 'temc',
      resolve: {
        track: () => {
          return track},
        trackForm: () => {
          return this.trackForm}
      }
    })

    modalInstance.result.then(track => {
      this.FormBuilderService.submit(
        Routing.generate('api_put_video_track', {track: track.id}),
        {track: track},
        'PUT'
      )
    })
  }
}
