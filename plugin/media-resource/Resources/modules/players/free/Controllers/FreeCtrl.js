import WaveSurfer from 'wavesurfer.js/dist/wavesurfer'
import 'wavesurfer.js/dist/plugin/wavesurfer.minimap.min'
import 'wavesurfer.js/dist/plugin/wavesurfer.timeline.min'
import 'wavesurfer.js/dist/plugin/wavesurfer.regions.min'
import $ from 'jquery'

class FreeCtrl {

  constructor($scope, url, configService, helpModalService, regionsService) {
    this.wavesurfer = Object.create(WaveSurfer)
    this.configService = configService
    this.urlService = url
    this.helpModalService = helpModalService
    this.regionsService = regionsService
    this.setSharedData()
    this.audioPlayer = new Audio()
    this.initWavesurfer()
    this.playing = false
    this.$scope = $scope
    this.helpText = ''
    this.currentHelpTextIndex = 0
    this.showTextTranscriptionText = true
    this.showHelp = false

    this.currentRegion = null
    if (this.resource.regions.length > 0) {
      this.currentRegion = this.resource.regions[0]
    }
  }

  setSharedData() {
    this.options = this.configService.getWavesurferOptions()
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

    this.audioData = this.urlService('innova_get_mediaresource_resource_file', {
      workspaceId: this.resource.workspaceId,
      id: this.resource.id
    })
    this.wavesurfer.load(this.audioData)

    this.audioPlayer.src = this.audioData

    this.wavesurfer.on('ready', function () {
      const timeline = Object.create(WaveSurfer.Timeline)
      timeline.init({
        wavesurfer: this.wavesurfer,
        container: '#wave-timeline'
      })

      // if no region create a default one... we need to do that only for this player since this is the default one
      // if another player is selected in admin view, user will need to save and with this action a region will be created
      if(this.resource.regions.length === 0){
        this.currentRegion = this.regionsService.create(0, this.wavesurfer.getDuration())
        this.resource.regions.push(this.currentRegion)
      }

    }.bind(this))

    this.wavesurfer.on('seek', function () {

      const current = this.regionsService.getRegionFromTime(this.wavesurfer.getCurrentTime(), this.resource.regions)
      if (current !== undefined && this.currentRegion && current.uuid !== this.currentRegion.uuid) {
        // update current region
        this.currentRegion = current
      }
      if (this.playing) {
        if (this.wavesurfer.isPlaying()) {
          this.wavesurfer.pause()
          this.wavesurfer.setVolume(1)
          this.wavesurfer.setPlaybackRate(1)
        }
        // pause help
        this.audioPlayer.pause()
        this.audioPlayer.currentTime = 0
        // hide highlight and help only if we change region
        if (current !== undefined && this.currentRegion && current.uuid !== this.currentRegion.uuid) {
          $('.region-highlight').remove()
          this.showHelp = false
          this.hideHelpText()
        }
        this.wavesurfer.play()
      } else {
        // hide any previous help info
        $('.region-highlight').remove()
        this.$scope.$apply(function(){
          this.showHelp = true
          this.helpText = ''
        }.bind(this))
          // show current help infos
        this.hideHelpText()
        this.highlight()
      }

    }.bind(this))

    this.wavesurfer.on('audioprocess', function () {
      const current = this.regionsService.getRegionFromTime(this.wavesurfer.getCurrentTime(), this.resource.regions)
      if (current !== undefined && this.currentRegion && current.uuid != this.currentRegion.uuid) {
        // update current region
        this.$scope.$apply(function () {
          this.currentRegion = current
        }.bind(this))
      }
    }.bind(this))
  }

  hasHelpText() {
    return this.regionsService.regionHasHelpTexts(this.currentRegion.helps)
  }

  highlight() {
    const $canvas = $('#waveform').find('wave').first().find('canvas').first()
    const cHeight = $canvas.height()
    const current = this.regionsService.getRegionFromTime(this.wavesurfer.getCurrentTime(), this.resource.regions)
    if(current !== undefined){
      const left = this.getPositionFromTime(parseFloat(current.start))
      const width = this.getPositionFromTime(parseFloat(current.end)) - left

      const elem = document.createElement('div')
      elem.className = 'region-highlight'
      elem.style.left = left + 'px'
      elem.style.width = width + 'px'
      elem.style.height = cHeight + 'px'
      elem.style.top = '0px'
      $('#waveform').find('wave').first().append(elem)
    }
  }

  getPositionFromTime(time) {
    const duration = this.wavesurfer.getDuration()
    const $canvas = $('#waveform').find('wave').first().find('canvas').first()
    const cWidth = $canvas.width()

    return time * cWidth / duration
  }

  showHelpText() {
    if (this.playing) {
      this.playing = false
      if (this.wavesurfer.isPlaying()) this.wavesurfer.pause()
      this.audioPlayer.pause()
      if (window.speechSynthesis.speaking) {
        // can not really stop shared.playing tts since the callback can not be canceled
        window.speechSynthesis.cancel()
      }
    }
    this.helpText = this.currentRegion.helps.helpTexts[this.currentHelpTextIndex]
    if (this.currentHelpTextIndex < this.currentRegion.helps.helpTexts.length - 1 && this.currentRegion.helps.helpTexts[this.currentHelpTextIndex + 1].text !== '') {
      this.currentHelpTextIndex++
    } else {
      this.currentHelpTextIndex = 0
    }
  }

  hideHelpText() {
    this.currentHelpTextIndex = 0
    this.helpText = ''
  }

  play() {
    if (!this.playing) {
      this.wavesurfer.play()
      this.playing = true
      $('.region-highlight').remove()
      this.showHelp = false
    } else {
      this.wavesurfer.pause()
      this.playing = false
      this.highlight()
      this.showHelp = true
    }
  }

  playInLoop() {
    this.hideHelpText()
    this.wavesurfer.setPlaybackRate(1)
    const options = {
      start: this.currentRegion.start,
      end: this.currentRegion.end,
      loop: true,
      drag: false,
      resize: false,
      color: 'rgba(0,0,0,0)' //invisible
    }
    const region = this.wavesurfer.addRegion(options)
    if (this.playing) {
      $('#btn-play').prop('disabled', false)
      this.playing = false
      this.wavesurfer.un('pause')
      this.wavesurfer.pause()
      this.wavesurfer.clearRegions()
    } else {
      $('#btn-play').prop('disabled', true)
      region.play()
      this.wavesurfer.on('pause', function () {
        if (options.loop) {
          region.play()
          this.playing = true
        } else {
          this.playing = false
        }
      }.bind(this))
    }
  }

  playSlowly() {
    this.hideHelpText()
    const options = {
      start: this.currentRegion.start,
      end: this.currentRegion.end,
      loop: false,
      drag: false,
      resize: false,
      color: 'rgba(0,0,0,0)' //invisible
    }
    const region = this.wavesurfer.addRegion(options)
      // stop playing if needed
    if (this.playing) {
      $('#btn-play').prop('disabled', false)
      this.playing = false
      this.wavesurfer.pause()
      this.wavesurfer.clearRegions()
      this.wavesurfer.setPlaybackRate(1)
      this.audioPlayer.pause()
      this.wavesurfer.setVolume(1)
    } else {
      $('#btn-play').prop('disabled', true)
      this.wavesurfer.setPlaybackRate(0.8)
      this.wavesurfer.setVolume(0)
      this.audioPlayer.playbackRate = 0.8
      this.audioPlayer.currentTime = this.currentRegion.start
      region.play()
      this.audioPlayer.play()
      this.playing = true
        // at the end of the region stop every audio readers
      this.wavesurfer.once('pause', function () {
        this.playing = false
        this.audioPlayer.pause()
        const progress = region.start / this.wavesurfer.getDuration()
        this.wavesurfer.seekTo(progress)
        this.audioPlayer.currentTime = region.start
        this.wavesurfer.clearRegions()
        this.wavesurfer.setPlaybackRate(1)
        this.wavesurfer.setVolume(1)
        this.audioPlayer.playbackRate = 1
      }.bind(this))
    }
  }

  playBackward() {
    // is shared.playing for real audio (ie not for TTS)
    if (this.playing) {
      // stop audio playback before playing
      this.audioPlayer.pause()
      this.playing = false
      $('#btn-play').prop('disabled', false)
    }
    if (window.SpeechSynthesisUtterance !== undefined) {
      let text = this.regionsService.removeHtml(this.currentRegion.note)
      const textArray = text.split(' ')
      const startIndex = textArray.length - 1
        // check if utterance is already speaking before playing (multiple click on backward button)
      if (!window.speechSynthesis.speaking) {
        this.handleUtterancePlayback(startIndex, textArray)
      }
    }
  }

  sayIt(text, callback) {
    let utterance = new SpeechSynthesisUtterance()
    utterance.text = text
    let voices = window.speechSynthesis.getVoices()
    if (voices.length === 0) {
      // chrome hack...
      window.setTimeout(function () {
        voices = window.speechSynthesis.getVoices()
        this.continueToSay(utterance, voices, this.resource.options.lang, callback)
      }.bind(this), 200)
    } else {
      this.continueToSay(utterance, voices, this.resource.options.lang, callback)
    }
  }

  continueToSay(utterance, voices, lang, callback) {
    for (let voice of voices) {
      // voices names are not the same depending on navigators
      // chrome is always code1-code2 while fx is sometimes code1-code2 and sometimes code1
      let fxLang = lang.split('-')[0]
      if (voice.lang === lang || voice.lang === fxLang) {
        utterance.voice = voice
      }
    }
    window.speechSynthesis.speak(utterance)
    utterance.onend = function () {
      return callback()
    }
  }

  handleUtterancePlayback(index, textArray) {
    let toSay = ''
    const length = textArray.length
    for (let j = index; j < length; j++) {
      toSay += textArray[j] + ' '
    }
    if (index >= 0) {
      $('#btn-play').prop('disabled', true)
      this.sayIt(toSay, function () {
        index = index - 1
        this.handleUtterancePlayback(index, textArray)
      }.bind(this))
    } else {
      $('#btn-play').prop('disabled', false)
    }
  }

  toggleTextTranscriptionText() {
    this.showTextTranscriptionText = !this.showTextTranscriptionText
  }
}

FreeCtrl.$inject = [
  '$scope',
  'url',
  'configService',
  'helpModalService',
  'regionsService'
]
export default FreeCtrl
