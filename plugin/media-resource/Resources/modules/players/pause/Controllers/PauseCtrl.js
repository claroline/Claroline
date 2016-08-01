class PauseCtrl {

  constructor($scope, url, configService, regionsService) {
    this.progress = document.getElementById('seekbar')
    this.audioPlayer = document.getElementById('audioPlayer')
    this.playing = false
    this.ended = false
    this.$scope = $scope
    this.urlService = url
    this.configService = configService
    this.regionsService = regionsService
    this.getConfigData()
    this.loadAudio()
    this.audioPlayer.addEventListener('timeupdate', function () {
      const percent = this.audioPlayer.currentTime * 100 / this.audioPlayer.duration
      this.progress.style.width = percent + '%'
    }.bind(this))

    this.audioPlayer.addEventListener('pause', function () {
      let nextRegion = this.getRegionToPlay(this.audioPlayer.currentTime)
      if (!this.ended && nextRegion) {
        let offset = nextRegion.start
        let paramString = '#t=' + offset + ',' + nextRegion.end
        this.audioPlayer.src = this.baseAudioUrl + paramString

        window.setTimeout(function () {
          this.audioPlayer.play()
          if (nextRegion.last) {
            this.ended = true
          }
        }.bind(this), this.pauseTime)
      } else { // all the document has been read
        this.audioPlayer.currentTime = 0
        this.ended = false
        $scope.$apply(function () {
          this.playing = false
        }.bind(this))
      }
    }.bind(this))
  }

  loadAudio() {
    this.audioData = this.urlService('innova_get_mediaresource_resource_file', {
      workspaceId: this.resource.workspaceId,
      id: this.resource.id
    })

    this.baseAudioUrl = this.audioData
    this.audioPlayer.src = this.audioData
  }
  getConfigData() {
    this.options = this.configService.getWavesurferOptions()
    this.modes = this.configService.getAvailablePlayModes()
    this.pauseTime = this.configService.getAutoPauseTime()
  }

  play() {
    this.ended = false
    this.audioPlayer.currentTime = 0
    let paramString = ''
    const nextRegion = this.getRegionToPlay(this.audioPlayer.currentTime)
    if (nextRegion) {
      var offset = nextRegion.end
      paramString = '#t=0,' + offset
    }
    this.audioPlayer.src = this.baseAudioUrl + paramString
    this.audioPlayer.play()
    this.playing = true
  }

  getRegionToPlay(time) {
    let region = this.regionsService.getRegionFromTime(time, this.resource.regions)
    let isLast = this.audioPlayer.duration <= region.end
    return {
      start: region.start,
      end: region.end,
      last: isLast
    }
  }
}

PauseCtrl.$inject = [
  '$scope',
  'url',
  'configService',
  'regionsService'
]
export default PauseCtrl
