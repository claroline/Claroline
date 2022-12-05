import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {makeId} from '#/main/core/scaffolding/id'

import {isOverlayed} from '#/plugin/audio-player/quiz/items/waveform/utils'
import {Section as SectionType} from '#/plugin/audio-player/quiz/items/waveform/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'

const Section = props =>
  <div style={{marginTop: '20px'}}>
    <div
      className="form-group"
      style={{display: 'flex'}}
    >
      <CallbackButton
        id={`section-${props.section.id}-play`}
        className="btn-link"
        callback={() => props.onPlay()}
      >
        <span className="fa fa-fw fa-play" />
      </CallbackButton>

      <div
        className="input-group"
        style={{
          marginRight: '5px',
          width: '100%'
        }}
      >
        <span className="input-group-addon">
          <b>{`${trans('start', {}, 'audio')} (${trans('second')})`}</b>
        </span>
        <input
          title={trans('start', {}, 'audio')}
          type="number"
          className="form-control section-start"
          disabled={true}
          value={props.section.start}
        />
      </div>
      <div
        className="input-group"
        style={{
          marginRight: '5px',
          width: '100%'
        }}
      >
        <span className="input-group-addon">
          <b>{`${trans('end', {}, 'audio')} (${trans('second')})`}</b>
        </span>
        <input
          title={trans('end', {}, 'audio')}
          type="number"
          className="form-control section-end"
          disabled={true}
          value={props.section.end}
        />
      </div>

      {!props.readOnly &&
        <div className="right-controls">
          <CallbackButton
            id={`section-${props.section.id}-delete`}
            className="btn-link"
            callback={() => props.onRemove()}
            dangerous={true}
          >
            <span className="fa fa-fw fa-trash" />
          </CallbackButton>
        </div>
      }
    </div>
  </div>

Section.propTypes = {
  section: T.shape(SectionType.propTypes).isRequired,
  readOnly: T.bool.isRequired,
  onRemove: T.func.isRequired,
  onPlay: T.func.isRequired
}

Section.defaultProps = {
  readOnly: false
}

class WaveformPlayer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      toPlay: null
    }
  }

  render() {
    return (
      <div>
        <Waveform
          id={`waveform-player-${this.props.item.id}`}
          url={asset(this.props.item.file)}
          editable={!this.props.disabled}
          maxRegions={this.props.item.answersLimit}
          regions={this.props.answer.map(a => a.id ? a : Object.assign({}, a, {id: makeId()}))}
          toPlay={this.state.toPlay}
          eventsCallbacks={{
            'region-update-end': (region) => {
              if (!this.props.disabled) {
                const newAnswer = cloneDeep(this.props.answer)
                const answerIdx = newAnswer.findIndex(a => a.id === region.id)

                if (!isOverlayed(this.props.answer, region.start, region.end, answerIdx)) {
                  if (-1 < answerIdx) {
                    newAnswer[answerIdx]['start'] = parseFloat(region.start.toFixed(1))
                    newAnswer[answerIdx]['end'] = parseFloat(region.end.toFixed(1))
                  } else if (!this.props.item.answersLimit || newAnswer.length < this.props.item.answersLimit) {
                    newAnswer.push({
                      id: region.id,
                      start: parseFloat(region.start.toFixed(1)),
                      end: parseFloat(region.end.toFixed(1))
                    })
                  }
                }
                this.props.onChange(newAnswer)
              }
            }
          }}
        />
        {this.props.answer.map(a =>
          <Section
            key={`section-${a.start}`}
            section={a}
            readOnly={this.props.disabled}
            onRemove={() => {
              const newAnswer = cloneDeep(this.props.answer)
              const answerIdx = newAnswer.findIndex(na => na.id === a.id)

              if (-1 < answerIdx) {
                newAnswer.splice(answerIdx, 1)
                this.props.onChange(newAnswer)
              }
            }}
            onPlay={() => this.setState({toPlay: [a.start, a.end]})}
          />
        )}
      </div>
    )
  }
}

WaveformPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    file: T.string.isRequired,
    answersLimit: T.number.isRequired
  }).isRequired,
  answer: T.arrayOf(T.shape({
    id: T.string,
    start: T.number.isRequired,
    end: T.number.isRequired
  })),
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

WaveformPlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {
  WaveformPlayer
}
