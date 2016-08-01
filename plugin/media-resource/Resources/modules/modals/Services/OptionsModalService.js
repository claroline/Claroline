import template from './../Partials/optionsModal.html'

class OptionsModalService {
  constructor($uibModal, regionsService) {
    this.uibModal = $uibModal
    this.regionsService = regionsService
    this.dialogOpts = {
      template: template,
      bindToController: true,
      controllerAs: 'modalCtrl',
      controller: ['$uibModalInstance', 'current', 'regions', 'audioSrc', 'regionsService',
        function ($uibModalInstance, current, regions, audioSrc, regionsService) {
          this.current = current
          this.selected = this.current.helps.helpRegionUuid
          this.regions = regions
          this.audioSrc = audioSrc
          this.playing = false
          this.audio = new Audio()
          this.audio.addEventListener('pause', function () {
            this.playing = false
          })
          this.close = function () {
            $uibModalInstance.dismiss('cancel')
          }
          this.updateRelatedRegion = function () {
            if (this.selected !== '') {
              this.current.helps.helpRegionUuid = this.selected
            }
          }
          this.play = function () {
            let related = regionsService.getRegionByUuid(this.current.helps.helpRegionUuid, this.regions)
            let paramString = ''
            if (related) {
              paramString = '#t=' + related.start + ',' + related.end
            }
            this.audio.src = this.audioSrc + paramString

            if (this.playing) {
              this.audio.pause()
              this.playing = false
            } else {
              this.audio.play()
              this.playing = true
            }
          }
        }
      ],
      resolve: {
        current: function () {
          return this.current
        }.bind(this),
        regions: function () {
          return this.regions
        }.bind(this),
        audioSrc: function () {
          return this.audioSrc
        }.bind(this),
        regionsService: function () {
          return this.regionsService
        }.bind(this)
      }
    }
  }

  setData(current, regions, audioSrc) {
    this.current = current
    this.regions = regions
    this.audioSrc = audioSrc
  }

  open() {
    this.uibModal.open(this.dialogOpts)
  }
}

export default OptionsModalService
