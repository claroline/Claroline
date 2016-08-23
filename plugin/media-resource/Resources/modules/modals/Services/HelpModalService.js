import template from './../Partials/helpModal.html'


class HelpModalService {
  constructor($uibModal, regionsService, configService) {
    this.uibModal = $uibModal
    this.regionsService = regionsService
    this.configService = configService
    this.dialogOpts = {
      template: template,
      bindToController: true,
      controllerAs: 'modalCtrl',
      controller: ['$uibModalInstance', 'data', 'regionsService', 'configService',
        function ($uibModalInstance, data, regionsService, configService) {
          this.modalInstance = $uibModalInstance

          this.data = data
          this.selected = this.data.current
          this.regionsService = regionsService
          this.configService = configService
          this.playModes = configService.getHelPlayModes()
          this.audioSrc = this.data.audioSrc
          this.playing = false
          this.audio = new Audio()
          this.currentMode = this.playModes.NORMAL
          this.audio.addEventListener('pause', function () {
            if (this.audio.loop) {
              this.play(null, this.playModes.LOOP)
            } else {
              this.playing = false
            }
          }.bind(this))

          this.close = function () {
            if (this.playing) {
              // stop audio playback before playing
              this.audio.pause()
              this.playing = false
            }
            this.modalInstance.dismiss('cancel')
          }
          this.hasHelp = function () {
            return regionsService.regionHasHelp(this.selected.helps)
          }
          this.hasHelpTexts = function () {
            return regionsService.regionHasHelpTexts(this.selected.helps)
          }
          this.hasHelpLinks = function () {
            return regionsService.regionHasHelpLinks(this.selected.helps)
          }
          this.hasPlayHelps = function () {
            return regionsService.regionHasPlayHelps(this.selected.helps)
          }
          // play / pause
          this.play = function ($event, mode) {
            // allready playing but press another button
            if (mode !== this.currentMode && $event && this.playing) {
              this.audio.pause()
              this.playing = false
            }
            // playing -> pause
            if (this.playing && $event) {
              if (this.audio.loop) {
                this.audio.loop = false
              }
              this.audio.pause()
            } else if (this.playing) { // loop mode continue to loop
              this.audio.currentTime = this.selected.start
              this.audio.play()
            } else {
              let paramString = '#t='
              if (mode === this.playModes.RELATED) {
                const uuid = this.selected.helps.helpRegionUuid
                let related = regionsService.getRegionByUuid(uuid, this.data.regions)
                paramString += related.start + ',' + related.end
              } else {
                paramString += this.selected.start + ',' + this.selected.end
              }
              this.audio.src = this.audioSrc + paramString
              this.audio.loop = mode === this.playModes.LOOP
              this.audio.playbackRate = mode === this.playModes.SLOW ? 0.8 : 1
              this.audio.play()
              this.playing = true
              this.currentMode = mode
            }
          }

          /**
           * Called by HelpModal play backward button
           */
          this.playBackward = function () {
            // is shared.playing for real audio (ie not for TTS)
            if (this.playing) {
              // stop audio playback before playing
              this.audio.pause()
              this.playing = false
            }
            if (window.SpeechSynthesisUtterance !== undefined) {
              let text = this.regionsService.removeHtml(this.selected.note)
              const textArray = text.split(' ')
              const startIndex = textArray.length - 1
                // check if utterance is already speaking before playing (multiple click on backward button)
              if (!window.speechSynthesis.speaking) {
                this.handleUtterancePlayback(startIndex, textArray)
              }
            }
          }

          this.sayIt = function (text, callback) {
            let utterance = new SpeechSynthesisUtterance()
            utterance.text = text
            let voices = window.speechSynthesis.getVoices()
            if (voices.length === 0) {
              // chrome hack...
              window.setTimeout(function () {
                voices = window.speechSynthesis.getVoices()
                this.continueToSay(utterance, voices, this.data.lang, callback)
              }.bind(this), 200)
            } else {
              this.continueToSay(utterance, voices, this.data.lang, callback)
            }
          }

          this.continueToSay = function (utterance, voices, lang, callback) {
            for (let voice of voices) {
              // voices names are not the same depending on navigators
              // chrome is always code1-code2 while fx is sometimes code1-code2 and sometimes code1
              var fxLang = lang.split('-')[0]
              if (voice.lang === lang || voice.lang === fxLang) {
                utterance.voice = voice
              }
            }
            window.speechSynthesis.speak(utterance)
            utterance.onend = function () {
              return callback()
            }
          }

          this.handleUtterancePlayback = function (index, textArray) {
            let toSay = ''
            const length = textArray.length
            for (let j = index; j < length; j++) {
              toSay += textArray[j] + ' '
            }
            if (index >= 0) {
              this.sayIt(toSay, function () {
                index = index - 1
                this.handleUtterancePlayback(index, textArray)
              }.bind(this))
            }
          }

          this.showHelpText = function ($event, text) {
            const target = $event.target
            target.innerHTML = ''
            target.textContent = text
            target.disabled = true
          }
        }
      ],
      resolve: {
        data: function () {
          return this.data
        }.bind(this),
        regionsService: function () {
          return this.regionsService
        }.bind(this),
        configService: function () {
          return this.configService
        }.bind(this)
      }
    }
  }

  setData(current, previous, regions, audioSrc, lang, minimal) {
    this.data = {
      current: current,
      previous: previous,
      regions: regions,
      audioSrc: audioSrc,
      lang: lang,
      minimal : minimal
    }
  }

  open() {
    // if we need to show every options (ie in admin / active view or the minimal one ie live view)
    this.uibModal.open(this.dialogOpts)
  }
}

export default HelpModalService
