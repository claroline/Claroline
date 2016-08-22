import $ from 'jquery'

class ScriptedCtrl {

  constructor($scope, url, configService, helpModalService, regionsService) {
    this.configService = configService
    this.urlService = url
    this.helpModalService = helpModalService
    this.regionsService = regionsService
    this.playing = false
    this.$scope = $scope
    this.isFirstStep = true
    this.showRetry = false
    this.problems = 0
    this.regionsIdentified = []
    this.audioPlayer = document.getElementById('audio-player')
    this.audioData = this.urlService('innova_get_mediaresource_resource_file', {
      workspaceId: this.resource.workspaceId,
      id: this.resource.id
    })
    this.audioPlayer.src = this.audioData

    this.audioPlayer.addEventListener('play', function () {
      this.playing = true
    })
    this.audioPlayer.addEventListener('pause', function () {
      this.playing = false
    })

    $('body').on('keypress', function (e) {
      if (e.which === 32) {
        this.addProblem()
      }
    }.bind(this))
  }

  addProblem() {
    document.activeElement.blur()
    const region = this.regionsService.getRegionFromTime(this.audioPlayer.currentTime, this.resource.regions)
    let isInArray = this.regionsIdentified.length > 0 && this.regionsService.getRegionByUuid(region.uuid, this.regionsIdentified) !== undefined
    if (!isInArray) {
      this.regionsIdentified.push(region)
    }
    window.setTimeout(function () {
      this.$scope.$apply(function () {
        this.problems += 1
      }.bind(this))
    }.bind(this), 10)

  }

  playFirstStep() {
    this.audioPlayer.play()
    this.playing = true
    this.isFirstStep = true
    this.showRetry = false
    this.audioPlayer.addEventListener('ended', function () {
      this.playing = false
      this.audioPlayer.currentTime = 0
      if (this.regionsIdentified.length > 0) {
        this.$scope.$apply(function () {
          this.isFirstStep = false
        }.bind(this))
      } else {
        this.$scope.$apply(function () {
          this.showRetry = true
        }.bind(this))
      }
    }.bind(this))
    document.activeElement.blur()
  }

  help(current) {
    let previous = null
      // search for prev region only if we are not in the first one
    if (current.start > 0) {
      for (let region of this.resource.regions) {
        if (region.end === current.start) {
          previous = region
        }
      }
    }
    this.audioPlayer.pause()
    this.playing = false

    this.helpModalService.setData(current, previous, this.resource.regions, this.audioData, this.resource.options.lang, false)
    this.helpModalService.open()
  }
}

ScriptedCtrl.$inject = [
  '$scope',
  'url',
  'configService',
  'helpModalService',
  'regionsService'
]
export default ScriptedCtrl
