import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import WaveSurfer from 'wavesurfer.js'
import RegionsPlugin from 'wavesurfer.js/dist/plugin/wavesurfer.regions'
import TimelinePlugin from 'wavesurfer.js/dist/plugin/wavesurfer.timeline'

import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {constants} from '#/plugin/audio-player/waveform/constants'

class Waveform extends Component {
  constructor(props) {
    super(props)

    this.state = {
      wavesurfer: null,
      playing: false,
      zoom: 20,
      playbackRate: 1,
      regionLoop: false
    }
    this.switchAudio = this.switchAudio.bind(this)
    this.playRegion = this.playRegion.bind(this)
    this.play = this.play.bind(this)
    this.switchRegion = this.switchRegion.bind(this)
    this.zoom = this.zoom.bind(this)
  }

  componentDidMount() {
    const plugins = [
      RegionsPlugin.create({
        dragSelection: this.props.editable &&
          (!this.props.maxRegions || (this.props.regions && this.props.regions.length < this.props.maxRegions)),
        slop: 5
      }),
      TimelinePlugin.create({
        container: `#${this.props.id}-timeline`
      })
    ]

    // Initilize Wavesurfer
    this.setState({
      wavesurfer: WaveSurfer.create({
        container: `#${this.props.id}`,
        scrollParent: true,
        waveColor: constants.COLORS.wave,
        progressColor: constants.COLORS.progression,
        plugins: plugins
      })
    }, () => {
      // Load audio file
      this.state.wavesurfer.load(this.props.url)

      this.state.wavesurfer.on('play', () => this.setState({playing: true}))
      this.state.wavesurfer.on('pause', () => this.setState({playing: false}))
      this.state.wavesurfer.on('seek', () => {
        if (this.props.eventsCallbacks['seek-time']) {
          this.props.eventsCallbacks['seek-time'](this.state.wavesurfer.getCurrentTime())
        }
      })

      if (this.props.editable) {
        this.state.wavesurfer.on('region-created', (region) => {
          if (this.props.eventsCallbacks['region-created']) {
            this.props.eventsCallbacks['region-created'](region)
          }
        })
        this.state.wavesurfer.on('region-updated', (region) => {
          if (this.props.eventsCallbacks['region-updated']) {
            this.props.eventsCallbacks['region-updated'](region)
          }
        })
        this.state.wavesurfer.on('region-update-end', (region) => {
          if (this.props.maxRegions && this.props.maxRegions <= Object.keys(this.state.wavesurfer.regions.list).length) {
            this.state.wavesurfer.disableDragSelection()
          }
          if (this.props.eventsCallbacks['region-update-end']) {
            this.props.eventsCallbacks['region-update-end'](region)
          }
        })
        this.state.wavesurfer.on('region-removed', (region) => {
          if (this.props.maxRegions && this.props.maxRegions > Object.keys(this.state.wavesurfer.regions.list).length) {
            this.state.wavesurfer.enableDragSelection({dragSelection: true, slop: 5})
          }
          if (this.props.eventsCallbacks['region-removed']) {
            this.props.eventsCallbacks['region-removed'](region)
          }
        })
      }
      this.state.wavesurfer.on('region-play', (region) => {
        if (this.props.eventsCallbacks['region-play']) {
          this.props.eventsCallbacks['region-play'](region)
        }
      })
      this.state.wavesurfer.on('region-in', (region) => {
        if (this.props.eventsCallbacks['region-in']) {
          this.props.eventsCallbacks['region-in'](region)
        }
      })
      this.state.wavesurfer.on('region-out', (region) => {
        if (this.props.eventsCallbacks['region-out']) {
          this.props.eventsCallbacks['region-out'](region)
        }
      })
      this.state.wavesurfer.on('region-mouseenter', (region) => {
        if (this.props.eventsCallbacks['region-mouseenter']) {
          this.props.eventsCallbacks['region-mouseenter'](region)
        }
      })
      this.state.wavesurfer.on('region-mouseleave', (region) => {
        if (this.props.eventsCallbacks['region-mouseleave']) {
          this.props.eventsCallbacks['region-mouseleave'](region)
        }
      })
      this.state.wavesurfer.on('region-click', (region, e) => {
        if (this.props.eventsCallbacks['region-click']) {
          this.props.eventsCallbacks['region-click'](region, e)
        }
      })
      this.state.wavesurfer.on('region-dblclick', (region, e) => {
        if (this.props.eventsCallbacks['region-dblclick']) {
          this.props.eventsCallbacks['region-dblclick'](region, e)
        }
      })

      // Necessary to display waveform correctly when the initialization occurs in an undisplayed component
      let refreshInterval = setInterval(() => {
        const waveformEl = document.getElementById(this.props.id)
        const canvas = waveformEl.querySelector('canvas')

        if (canvas.getAttribute('width')) {
          // Initialize existing regions
          this.props.regions.forEach(r => {
            if (r.startTolerance || r.endTolerance) {
              this.state.wavesurfer.addRegion({
                id: `tolerance-${r.id}`,
                start: r.start - r.startTolerance,
                end: r.end + r.endTolerance,
                resize: this.props.editable,
                drag: false,
                color: constants.COLORS.tolerance
              })
            }
            this.state.wavesurfer.addRegion(Object.assign({}, r, {
              resize: this.props.editable,
              drag: this.props.editable,
              color: r.color ?
                r.color :
                r.id === this.props.selectedRegion ?
                  constants.COLORS.selected :
                  constants.COLORS.section
            }))
          })
          clearInterval(refreshInterval)
        } {
          this.state.wavesurfer.drawBuffer()
        }
      }, 2000)
    })
  }

  regionsEquals(oldRegions, regions) {
    let isEqual = oldRegions.length === regions.length

    if (isEqual) {
      oldRegions.forEach((r, idx) => {
        if (r.id !== regions[idx].id) {
          isEqual = false
        }
      })
    }

    return isEqual
  }

  componentDidUpdate(prevProps) {
    // Required if we want the waveform to be refreshed when another audio file is selected
    if (this.props.url !== prevProps.url) {
      this.state.wavesurfer.clearRegions()
      this.state.wavesurfer.load(this.props.url)
    } else {
      Object.values(this.state.wavesurfer.regions.list).forEach(region => {
        // Updates wavesurfer regions with given ones
        let regionId = region.id
        const isTolerance = -1 < regionId.indexOf('tolerance-')

        if (isTolerance) {
          regionId = regionId.substring(10)
        }
        const propRegion = this.props.regions.find(r => r.id === regionId || r.regionId === regionId)

        if (propRegion) {
          if (propRegion.id === regionId) {
            if (isTolerance) {
              region.update({
                start: propRegion.start - propRegion.startTolerance,
                end: propRegion.end + propRegion.endTolerance,
                resize: this.props.editable,
                drag: false,
                color: constants.COLORS.tolerance
              })
            } else {
              region.update({
                start: propRegion.start,
                end: propRegion.end,
                resize: this.props.editable,
                drag: this.props.editable,
                color: propRegion.color ?
                  propRegion.color :
                  propRegion.id === this.props.selectedRegion ?
                    constants.COLORS.selected :
                    constants.COLORS.section
              })
            }
          } else {
            // In this case we can override default wavesurfer id
            region.remove()

            if (propRegion.startTolerance || propRegion.endTolerance) {
              this.state.wavesurfer.addRegion({
                id: `tolerance-${propRegion.id}`,
                start: propRegion.start - propRegion.startTolerance,
                end: propRegion.end + propRegion.endTolerance,
                resize: this.props.editable,
                drag: false,
                color: constants.COLORS.tolerance
              })
            }
            this.state.wavesurfer.addRegion(Object.assign(
              {},
              propRegion,
              {
                resize: this.props.editable,
                drag: this.props.editable,
                color: propRegion.color ?
                  propRegion.color :
                  propRegion.id === this.props.selectedRegion ?
                    constants.COLORS.selected :
                    constants.COLORS.section
              }
            ))
          }
        } else {
          // Remove deleted regions
          region.remove()
        }
      })

      // As the displayed regions are fetched from [wavesurfer.regions.list]
      // it is possible that a newly created region is deleted from [wavesurfer.regions.list] because of state delay
      // In that case we re-add the region in [wavesurfer.regions.list]
      if (this.props.forceRegions && !this.regionsEquals(prevProps.regions, this.props.regions)) {
        this.props.regions.forEach(region => {
          const isPresent = this.state.wavesurfer.regions.list[region.id]

          if (!isPresent) {
            if (region.startTolerance || region.endTolerance) {
              this.state.wavesurfer.addRegion({
                id: `tolerance-${region.id}`,
                start: region.start - region.startTolerance,
                end: region.end + region.endTolerance,
                resize: this.props.editable,
                drag: false,
                color: constants.COLORS.tolerance
              })
            }
            this.state.wavesurfer.addRegion(Object.assign(
              {},
              region,
              {
                resize: this.props.editable,
                drag: this.props.editable,
                color: region.color ?
                  region.color :
                  region.id === this.props.selectedRegion ?
                    constants.COLORS.selected :
                    constants.COLORS.section
              }
            ))
          }
        })
      }

      if (this.props.toPlay && prevProps.toPlay !== this.props.toPlay) {
        this.play(this.props.toPlay[0], this.props.toPlay[1])
      }
    }
  }

  componentWillUnmount() {
    this.state.wavesurfer.destroy()
  }

  switchAudio() {
    this.state.wavesurfer.setPlaybackRate(1)
    this.setState({playbackRate: 1})

    if (this.state.playing) {
      this.state.wavesurfer.pause()
    } else {
      this.state.wavesurfer.play()
    }
  }

  playRegion(region) {
    if (this.state.regionLoop) {
      if (this.state.wavesurfer.regions.list[region.id]) {
        this.state.wavesurfer.regions.list[region.id].playLoop()
      }
    } else {
      this.state.wavesurfer.play(region.start, region.end)
    }
  }

  play(start, end) {
    this.state.wavesurfer.play(start, end)
  }

  switchRegion(direction = 1) {
    const currentTime = this.state.wavesurfer.getCurrentTime()

    if (0 < direction) {
      // Play the region that will come next
      let next = null

      this.props.regions.forEach(r => {
        if (r.start > currentTime && (!next || r.start < next.start)) {
          next = r
        }
      })

      if (next) {
        this.playRegion(next)
      }
    } else {
      // Replay the current region or play the previous one
      let previous = null

      this.props.regions.forEach(r => {
        if (r.start < currentTime - 1 && (!previous || r.start > previous.start)) {
          previous = r
        }
      })

      if (previous) {
        this.playRegion(previous)
      }
    }
  }

  zoom(value) {
    this.setState({zoom: value})
    this.state.wavesurfer.zoom(Number(value))
  }

  render() {
    return (
      <div>
        <div id={this.props.id}>
        </div>
        <div id={`${this.props.id}-timeline`}>
        </div>
        <div
          id={`${this.props.id}-cmd`}
          style={{
            marginTop: '20px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-evenly'
          }}
        >
          <span className="waveform-control">
            {0 < this.props.regions.length &&
              <CallbackButton
                className="btn"
                callback={() => this.switchRegion(-1)}
                primary={true}
                size="sm"
                style={{
                  marginRight: '10px'
                }}
              >
                <span className="fa fa-fast-backward" />
              </CallbackButton>
            }

            {this.props.rateControl &&
              <CallbackButton
                className="btn"
                callback={() => {
                  const newRate = parseFloat((this.state.wavesurfer.getPlaybackRate() - 0.1).toFixed(1))

                  if (0.8 <= newRate && 1.2 >= newRate) {
                    this.state.wavesurfer.setPlaybackRate(newRate)
                    this.setState({playbackRate: newRate})
                  }
                }}
                primary={true}
                size="sm"
                style={{
                  marginRight: '10px'
                }}
              >
                {1 > this.state.playbackRate &&
                  <span style={{marginRight: '5px'}}>
                    {`(x${this.state.playbackRate})`}
                  </span>
                }
                <span className="fa fa-backward" />
              </CallbackButton>
            }

            <CallbackButton
              className="btn"
              callback={this.switchAudio}
              primary={true}
              size="sm"
            >
              <span className={`fa fa-${this.state.playing ? 'pause' : 'play'}`} />
              <span style={{marginLeft: '5px'}}>
                {this.state.playing ? trans('pause', {}, 'audio') : trans('play', {}, 'audio')}
              </span>
            </CallbackButton>


            {this.props.rateControl &&
              <CallbackButton
                className="btn"
                callback={() => {
                  const newRate = parseFloat((this.state.wavesurfer.getPlaybackRate() + 0.1).toFixed(1))

                  if (0.8 <= newRate && 1.2 >= newRate) {
                    this.state.wavesurfer.setPlaybackRate(newRate)
                    this.setState({playbackRate: newRate})
                  }
                }}
                primary={true}
                size="sm"
                style={{
                  marginLeft: '10px'
                }}
              >
                <span className="fa fa-forward" />
                {1 < this.state.playbackRate &&
                  <span style={{marginLeft: '5px'}}>
                    {`(x${this.state.playbackRate})`}
                  </span>
                }
              </CallbackButton>
            }

            {0 < this.props.regions.length &&
              <CallbackButton
                className="btn"
                callback={() => this.switchRegion()}
                primary={true}
                size="sm"
                style={{
                  marginLeft: '10px'
                }}
              >
                <span className="fa fa-fast-forward" />
              </CallbackButton>
            }

            {0 < this.props.regions.length && false &&
              <CallbackButton
                className="btn"
                callback={() => this.setState({regionLoop: !this.state.regionLoop})}
                size="sm"
                style={{
                  marginLeft: '10px',
                  backgroundColor: this.state.regionLoop ? 'rgba(29, 105, 153, 0.7)' : '#FFFFFF'
                }}
              >
                <span className="fa fa-repeat" />
              </CallbackButton>
            }
          </span>
          <div
            className="waveform-zoom"
            style={{
              display: 'flex',
              alignItems: 'center'
            }}
          >
            <CallbackButton
              callback={() => 0 < this.state.zoom - 1 ? this.zoom(this.state.zoom - 1) : false}
              style={{
                marginRight: '5px'
              }}
            >
              <span className="fa fa-search-minus" />
            </CallbackButton>
            <input
              type="range"
              min="1"
              max="100"
              value={this.state.zoom}
              style={{
                display: 'inline',
                minWidth: '200px'
              }}
              onChange={(e) => this.zoom(parseInt(e.target.value))}
            />
            <CallbackButton
              callback={() => 100 > this.state.zoom + 1 ? this.zoom(this.state.zoom + 1) : false}
              style={{
                marginLeft: '5px'
              }}
            >
              <span className="fa fa-search-plus" />
            </CallbackButton>
          </div>
        </div>
      </div>
    )
  }
}

Waveform.propTypes = {
  id: T.string.isRequired,
  url: T.string.isRequired,
  editable: T.bool.isRequired,
  rateControl: T.bool.isRequired,
  regions: T.arrayOf(T.shape({
    id: T.string.isRequired,
    regionId: T.string,
    start: T.number.isRequired,
    end: T.number.isRequired,
    color: T.string
  })),
  selectedRegion: T.string,
  maxRegions: T.number,
  forceRegions: T.bool,
  eventsCallbacks: T.object,
  toPlay: T.array
}

Waveform.defaultProps = {
  editable: true,
  rateControl: true,
  regions: [],
  forceRegions: true,
  eventsCallbacks: {}
}

export {
  Waveform
}
