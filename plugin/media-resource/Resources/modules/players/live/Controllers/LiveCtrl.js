import WaveSurfer from 'wavesurfer.js/dist/wavesurfer'
import 'wavesurfer.js/dist/plugin/wavesurfer.minimap.min'
import 'wavesurfer.js/dist/plugin/wavesurfer.timeline.min'
import 'wavesurfer.js/dist/plugin/wavesurfer.regions.min'


class LiveCtrl {

  constructor($scope, url, configService, helpModalService, regionsService) {
    this.wavesurfer = Object.create(WaveSurfer)
    this.configService = configService
    this.urlService = url
    this.helpModalService = helpModalService
    this.regionsService = regionsService
    this.setSharedData()
    this.initWavesurfer()
    this.playing = false
    this.$scope = $scope
      // enable disable help modal button
    this.showHelp = false
      // wavesurfer region
    this.currentRegion = null
  }

  setSharedData() {
    this.options = this.configService.getWavesurferOptions()
    this.modes = this.configService.getAvailablePlayModes()
  }

  initWavesurfer() {
    const progressDiv = document.querySelector('#progress-bar')
    const progressBar = progressDiv.querySelector('.progress-bar')
    const showProgress = function (percent) {
      progressDiv.style.display = 'block'
      progressBar.style.width = percent + '%'
    }
    const hideProgress = function () {
      progressDiv.style.display = 'none'
    }
    this.wavesurfer.on('loading', showProgress)
    this.wavesurfer.on('ready', hideProgress)
    this.wavesurfer.on('destroy', hideProgress)
    this.wavesurfer.on('error', hideProgress)

    this.wavesurfer.init(this.options)
    this.wavesurfer.initMinimap({
      height: 30,
      waveColor: '#ddd',
      progressColor: '#999',
      cursorColor: '#999'
    })
    this.audioData = this.urlService('innova_get_mediaresource_resource_file', {
      workspaceId: this.resource.workspaceId,
      id: this.resource.id
    })
    this.wavesurfer.load(this.audioData)
    this.wavesurfer.on('ready', function () {
      const timeline = Object.create(WaveSurfer.Timeline)
      timeline.init({
        wavesurfer: this.wavesurfer,
        container: '#wave-timeline'
      })

      this.wavesurfer.enableDragSelection({
        color: 'rgba(200, 55, 99, 0.1)'
      })

    }.bind(this))

    this.wavesurfer.on('pause', function () {
      if (this.currentRegion) {
        this.playing = false
      }

    }.bind(this))

    this.wavesurfer.on('region-created', function (current) {
      // delete all other existing regions
      for (let index in this.wavesurfer.regions.list) {
        const region = this.wavesurfer.regions.list[index]
        if (region.start !== current.start && region.end !== current.start) {
          this.wavesurfer.regions.list[index].remove()
        }
      }
      this.currentRegion = current
      this.$scope.$apply(function () {
        this.showHelp = true
      }.bind(this))

    }.bind(this))
  }

  help() {
    if (this.playing) {
      if (this.wavesurfer.isPlaying()) {
        this.wavesurfer.pause()
      }
      this.playing = false
    }
    this.helpModalService.setData(this.currentRegion, false, this.resource.regions, this.audioData, this.resource.options.lang, true)
    this.helpModalService.open()
  }

  play() {
    if (!this.playing) {
      if (this.currentRegion) {
        this.currentRegion.play()
        this.playing = true
      } else {
        this.wavesurfer.play()
        this.playing = true
      }
    } else {
      this.wavesurfer.pause()
      this.playing = false
    }
  }

  deleteRegions() {
    this.wavesurfer.clearRegions()
    this.currentRegion = null
    this.showHelp = false
    this.wavesurfer.setVolume(1)
    this.wavesurfer.setPlaybackRate(1)
  }
}

LiveCtrl.$inject = [
  '$scope',
  'url',
  'configService',
  'helpModalService',
  'regionsService'
]
export default LiveCtrl
