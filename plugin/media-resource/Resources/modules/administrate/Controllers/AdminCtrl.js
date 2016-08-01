import WaveSurfer from 'wavesurfer.js/dist/wavesurfer'
import 'wavesurfer.js/dist/plugin/wavesurfer.minimap.min'
import 'wavesurfer.js/dist/plugin/wavesurfer.timeline.min'
import 'wavesurfer.js/dist/plugin/wavesurfer.regions.min'
import $ from 'jquery'

class AdminCtrl {

  constructor($scope, $window, $filter, url, configService, helpModalService, optionsModalService, regionsService, AdminService) {
    this.wavesurfer = Object.create(WaveSurfer)
    this.popoverTranscriptionTplUrl = 'popover.transcription.html'
    this.popoverPlayersTplUrl = 'popover.players.html'
    this.filter = $filter
    this.configService = configService
    this.urlService = url
    this.helpModalService = helpModalService
    this.optionsModalService = optionsModalService
    this.regionsService = regionsService
    this.adminService = AdminService
    this.markers = []
    this.setSharedData()
    this.initWavesurfer()
    this.initContentEditable()
    this.playing = false
    this.httpSuccess = false
    this.httpError = false
    this.$window = $window
    this.$scope = $scope

    this.currentRegion = null
    if (this.resource.regions.length > 0) {
      this.currentRegion = this.resource.regions[0]
    }
  }

  setSharedData() {
    this.options = this.configService.getWavesurferOptions()
    this.langs = this.configService.getAvailableTTSLanguage()
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
      this.initRegionsAndMarkers()
      this.highlightWaveform()
      this.highligthRegionRow(this.currentRegion)
    }.bind(this))

    this.wavesurfer.on('seek', function () {
      const current = this.regionsService.getRegionFromTime(this.wavesurfer.getCurrentTime(), this.resource.regions)
      if (current && this.currentRegion && current.uuid != this.currentRegion.uuid) {
        // update current region
        this.currentRegion = current
        this.highlightWaveform()
        this.highligthRegionRow(this.currentRegion)
      }
    }.bind(this))

    this.wavesurfer.on('audioprocess', function () {
      const current = this.regionsService.getRegionFromTime(this.wavesurfer.getCurrentTime(), this.resource.regions)
      if (current && this.currentRegion && current.uuid != this.currentRegion.uuid) {
        // update current region
        this.currentRegion = current
        this.highlightWaveform()
        this.highligthRegionRow(this.currentRegion)
      }
    }.bind(this))
  }

  initRegionsAndMarkers() {
    if (this.resource.regions.length === 0) {
      let region = this.regionsService.create(0, this.wavesurfer.getDuration())
      this.$scope.$apply(function () {
        this.resource.regions.push(region)
        this.currentRegion = region
      }.bind(this))
    } else {
      for (let region of this.resource.regions) {
        if (Number(region.start) > 0) {
          this.addMarker(region.start, region.uuid)
        }
      }
    }
  }

  optionsModeChanged() {
    if (this.resource.options.mode !== 'free') {
      this.resource.options.showTextTranscription = false
    }
  }

  addMarker(time, uuid) {
    const $canvas = $('#waveform').find('wave').first().find('canvas').first()
    const cHeight = $canvas.height()

    const left = this.getMarkerLeftPostionFromTime(time)
    const markerWidth = 1
    const dragHandlerBorderSize = 1
    const dragHandlerSize = 18
    const dragHandlerTop = cHeight / 2 - dragHandlerSize / 2
    const dragHandlerLeft = dragHandlerBorderSize - dragHandlerSize / 2

    let marker = document.createElement('div')
    marker.className = 'divide-marker'
    marker.style.left = left + 'px'
    marker.style.width = markerWidth + 'px'
    marker.dataset.time = time

    let dragHandler = document.createElement('div')
    dragHandler.className = 'marker-drag-handler'
    dragHandler.style.border = dragHandlerBorderSize + 'px solid white'
    dragHandler.style.width = dragHandlerSize + 'px'
    dragHandler.style.height = dragHandlerSize + 'px'
    dragHandler.style.top = dragHandlerTop + 'px'
    dragHandler.style.left = dragHandlerLeft + 'px'
    dragHandler.title = this.filter('trans')('marker_drag_title', {}, 'media_resource')
    const guid = uuid || this.regionsService.createGuid()
    dragHandler.dataset.uuid = guid

    marker.appendChild(dragHandler)
    $('#waveform').find('wave').first().append(marker)

    let dragData
      // set the drag data when handler is clicked
    dragHandler.addEventListener('mousedown', function (event) {
      var time = this.getTimeFromPosition($(event.target).closest('.divide-marker').position().left)
      dragData = this.setDragData(time, marker)
      this.$window.addEventListener('mousemove', moveMarker)
      this.$window.addEventListener('mouseup', dropMarker)
    }.bind(this))

    let moveMarker = function moveMarker(event) {
      let rect = document.getElementById('waveform').getBoundingClientRect()
      let left = event.pageX - rect.left - this.$window.pageXOffset
      let time = this.getTimeFromPosition(left)
      if (left > rect.left && left < rect.right && dragData.minTime < time && dragData.maxTime > time) {

        var position = this.getMarkerLeftPostionFromTime(time)
        dragData.marker.style.left = position + 'px'
        if (dragData.prevRegion) {
          dragData.prevRegion.end = Number(time)
        }
        if (dragData.nextRegion) {
          dragData.nextRegion.start = Number(time)
        }
        // udpate dom marker data-time attribute
        dragData.marker.dataset.time = time
          // udpate marker object time value
        dragData.markerO.time = time
          // update wavesurfer region highlight
        const current = this.regionsService.getRegionFromTime(time, this.resource.regions)
        this.wavesurfer.clearRegions()
        this.wavesurfer.addRegion({
          start: current.start,
          end: current.end,
          color: 'rgba(255,0,0,0.5)',
          drag: false,
          resize: false
        })
      } else {
        return false
      }
    }.bind(this)

    let dropMarker = function dropMarker() {
      this.$window.removeEventListener('mousemove', moveMarker)
      this.$window.removeEventListener('mouseup', dropMarker)
      this.$scope.$apply(function () {
        dragData.prevRegion.end = Number(dragData.marker.dataset.time)
        dragData.nextRegion.start = Number(dragData.marker.dataset.time)
      })
    }.bind(this)
    const mark = {
      time: Number(time),
      uuid: guid
    }
    this.markers.push(mark)
    return mark
  }

  setDragData(time, marker) {
    let data = {}
      // marker should not be moved before the previous nore after the next one
    const tolerance = 1
      // since we are on a frontier, add / remove a little time to ensure next / prev search
    let prevRegion = this.regionsService.getPrevRegion(time + 0.01, this.resource.regions)
    let nextRegion = this.regionsService.getNextRegion(time - 0.01, this.resource.regions)
    const min = prevRegion && prevRegion.start ? prevRegion.start : 0
    const max = nextRegion && nextRegion.end ? nextRegion.end : this.wavesurfer.getDuration()

    // search for marker object
    let markerObject
    for (let mark of this.markers) {
      if (mark.time.toFixed(2) === time.toFixed(2)) {
        markerObject = mark
      }
    }

    data = {
      minTime: min + tolerance,
      maxTime: max - tolerance,
      prevRegion: prevRegion,
      nextRegion: nextRegion,
      marker: marker,
      markerO: markerObject
    }
    return data
  }

  getMarkerLeftPostionFromTime(time) {
    const duration = this.wavesurfer.getDuration()
    const $canvas = $('#waveform').find('wave').first().find('canvas').first()
    const cWidth = $canvas.width()
    return time * cWidth / duration
  }

  getTimeFromPosition(position) {
    const duration = this.wavesurfer.getDuration()
    const $canvas = $('#waveform').find('wave').first().find('canvas').first()
    const cWidth = $canvas.width()
    return position * duration / cWidth
  }

  highlightWaveform() {
    this.wavesurfer.clearRegions()
    let params = {
      start: this.currentRegion.start,
      end: this.currentRegion.end,
      color: 'rgba(255,0,0,0.5)',
      drag: false,
      resize: false
    }
    this.wavesurfer.addRegion(params)
  }

  // should do it with data-ng-class but this is not working well and would need $scope.$apply
  // and we should avoid to use $scope.$apply
  highligthRegionRow(region) {
    let row = this.getRegionRow(region)
    $('.active-row').each(function () {
      $(this).removeClass('active-row')
    })
    $(row).closest('.region').addClass('active-row')
  }

  getRegionRow(region) {
    var row
    $('.region').each(function () {
      var temp = $(this)
      if ($(this).attr('data-uuid') === region.uuid) {
        row = temp
      }
    })
    return row
  }

  configureRegionHelps(region) {
    this.optionsModalService.setData(region, this.resource.regions, this.audioData)
    this.optionsModalService.open()
  }

  // confirm delete callback
  deleteRegion(region) {
    this.removeMeFromHelp(region)
    if (region.start === 0) {
      let next = this.regionsService.getNextRegion(region.end - 0.1, this.resource.regions)
      if (next) {
        next.start = 0
        this.currentRegion = next
      }
    } else { // all other cases
      // get previous region and update it's end
      let previous = this.regionsService.getPrevRegion(region.start + 0.1, this.resource.regions)
      if (previous) {
        previous.end = region.end
        this.currentRegion = previous
      }
    }

    // remove marker from DOM
    $('.marker-drag-handler').each(function () {
      const $marker = $(this).closest('.divide-marker')
      const time = Number($marker.attr('data-time'))
      if (region.start > 0 && time === region.start) {
        $marker.remove()
      } else if (region.start === 0 && time === region.end) {
        $marker.remove()
      }
    })
    const index = this.resource.regions.indexOf(region)
    this.resource.regions.splice(index, 1)
      // remove marker from array
    for (let marker of this.markers) {
      if (marker.time === region.start) {
        const i = this.markers.indexOf(marker)
        this.markers.splice(i, 1)
      }
    }
    // highlight region on waveform
    this.highlightWaveform()
    this.highligthRegionRow(this.currentRegion)

  }

  hasHelp(helps) {
    return this.regionsService.regionHasHelp(helps)
  }

  /**
   * checks for region that use the given region in there help
   * if yes remove that help
   */
  removeMeFromHelp(region) {
    for (let r of this.resource.regions) {
      if (r.helps.helpRegionUuid !== '' && r.helps.helpRegionUuid === region.uuid) {
        r.helps.helpRegionUuid = ''
      }
    }
  }

  play() {
    if (!this.playing) {
      this.wavesurfer.play()
      this.playing = true
    } else {
      this.wavesurfer.pause()
      this.playing = false
    }
  }

  playRegion(region) {
    const wRegion = this.wavesurfer.addRegion({
      start: region ? region.start : this.currentRegion.start,
      end: region ? region.end : this.currentRegion.end,
      color: 'rgba(0,0,0,0)',
      drag: false,
      resize: false
    })
    if (!this.playing) {
      wRegion.play()
      this.playing = true
      this.wavesurfer.once('pause', function () {
        this.playing = false
      }.bind(this))
    } else {
      this.wavesurfer.pause()
      this.playing = false
    }
  }

  goTo(time) {
    const percent = time / this.wavesurfer.getDuration()
    this.wavesurfer.seekAndCenter(percent)
    if (this.playing || this.wavesurfer.isPlaying()) {
      this.wavesurfer.pause()
      this.playing = false
    }
  }

  createRegion(time) {
    const toSplit = this.regionsService.getRegionFromTime(time, this.resource.regions)
      // region to create after the given time
    let region = this.regionsService.create(time, toSplit.end)
      // update "left" region in collection
    toSplit.end = time
    this.resource.regions.push(region)
    this.currentRegion = region
    this.highlightWaveform()
      // need to wait for the row to be added in the dom
    window.setTimeout(function () {
      this.highligthRegionRow(this.currentRegion)
    }.bind(this), 100)
  }

  backward() {
    if (this.resource.regions.length > 1) {
      let prev
      if (this.currentRegion.start > 0) {
        prev = this.regionsService.getPrevRegion(this.wavesurfer.getCurrentTime() + 0.01, this.resource.regions)
        this.goTo(prev.start)
      } else {
        this.wavesurfer.seekAndCenter(0)
      }

    } else {
      this.wavesurfer.seekAndCenter(0)
    }
  }

  forward() {
    if (this.resource.regions.length > 1) {
      let next
      if (this.currentRegion.end < this.wavesurfer.getDuration().toFixed(2)) {
        next = this.regionsService.getNextRegion(this.wavesurfer.getCurrentTime(), this.resource.regions)
          // go to start of the next region
        this.goTo(next.start)
      }
    }
  }

  mark() {
    const time = this.wavesurfer.getCurrentTime()
    if (time > 0) {
      const mark = this.addMarker(time)
      this.createRegion(mark.time)
    }
  }

  help() {
    let previous = null
      // search for prev region only if we are not in the first one
    if (this.currentRegion.start > 0) {
      for (let region of this.resource.regions) {
        if (region.end === this.currentRegion.start) {
          previous = region
        }
      }
    }

    if (this.playing) {
      if (this.wavesurfer.isPlaying()) {
        this.wavesurfer.pause()
      }
      this.playing = false
    }

    this.helpModalService.setData(this.currentRegion, previous, this.resource.regions, this.audioData, this.resource.options.lang, false)
    this.helpModalService.open()
  }

  initContentEditable() {
    $('body').on('focus', '[contenteditable]', function (event) {
      const $input = $(event.target)
      $input.data('before', $input.html())
      // when focused skip to the start of the region on the waveform
      const start = $input.closest('.region').find('.start').attr('data-start')
      this.goTo(start)
      return $input
    }.bind(this))
    .on('blur keypress keyup paste input', '[contenteditable]', function (e) {
      const $input = $(this)
      // do not allow user to add a line when pressing enter key
      if (e.type === 'keypress' && e.which === 13) {
        return false
      }

      if ($input.data('before') !== $input.html()) {
        $input.data('before', $input.html())
        $input.trigger('change')
      }
      return $input
    })
  }

  /**
   * Add span tag and css class to the selected text
   * does not update the object !!
   */
  annotate(color) {
    const selection = window.getSelection()
    document.execCommand('insertHTML', false, '<span class="accent-' + color + '">' + selection.toString() + '</span>')
  }

  zip() {
    this.adminService.zip(this.resource).then(
      function onSuccess(response) {
        const a = document.createElement('a')
        a.style.display = 'none'
        const blob = new Blob([response])
        const url = URL.createObjectURL(blob)
        a.href = url
        a.download = this.resource.name + '.zip'
        document.body.appendChild(a)
        a.click()
        setTimeout(function () {
          document.body.removeChild(a)
          window.URL.revokeObjectURL(url)
        }, 100)
      }.bind(this),
      function onError() {
        this.httpSuccess = false
        this.httpError = true
      }.bind(this)
    )
  }

  togglePanel($event) {
    $($event.target).closest('.panel').find('.panel-body').toggle()
    $($event.target).hasClass('fa-chevron-down') ? $($event.target).removeClass('fa-chevron-down').addClass('fa-chevron-up') : $($event.target).removeClass('fa-chevron-up').addClass('fa-chevron-down')
  }

  save() {
    // need to update every region note to add html class and tags
    let my = this
    $('.region').each(function () {
      let note = $(this).find('[contenteditable]').html()
      let uuid = $(this).attr('data-uuid')
      let region = my.regionsService.getRegionByUuid(uuid, my.resource.regions)
      region.note = note
    })

    this.adminService.save(this.resource).then(
      function onSuccess() {
        this.httpError = false
        this.httpSuccess = true
      }.bind(this),
      function onError() {
        this.httpSuccess = false
        this.httpError = true
      }.bind(this)
    )
  }
}
AdminCtrl.$inject = [
  '$scope',
  '$window',
  '$filter',
  'url',
  'configService',
  'helpModalService',
  'optionsModalService',
  'regionsService',
  'AdminService'
]
export default AdminCtrl
