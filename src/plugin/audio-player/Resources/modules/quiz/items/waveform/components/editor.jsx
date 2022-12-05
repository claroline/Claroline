import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {makeId} from '#/main/core/scaffolding/id'

import {SCORE_SUM} from '#/plugin/exo/quiz/enums'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'

import {isOverlayed} from '#/plugin/audio-player/quiz/items/waveform/utils'
import {
  Section as SectionType,
  WaveformItem as WaveformItemType
} from '#/plugin/audio-player/quiz/items/waveform/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'

class Section extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <div
        className={classes('waveform-answer answer-item', this.props.hasExpectedAnswers && {
          'unexpected-answer' : this.props.solution.score <= 0,
          'expected-answer' : this.props.solution.score > 0
        })}
        style={{
          marginTop: '10px',
          border: this.props.selected ? 'solid 2px darkslategray' : ''
        }}
      >
        <div
          className="form-group"
          style={{
            display: 'flex',
            alignItems: 'center'
          }}
        >
          <CallbackButton
            id={`section-${this.props.solution.section.id}-play`}
            className="btn-link"
            callback={() => this.props.onPlay(this.props.solution.section.start, this.props.solution.section.end)}
          >
            <span className="fa fa-fw fa-play" />
          </CallbackButton>

          <div
            className="input-group"
            style={{
              marginRight: '5px',
              width: '40%'
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
              value={this.props.solution.section.start}
            />
          </div>
          <div
            className="input-group"
            style={{
              marginRight: '5px',
              width: '40%'
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
              value={this.props.solution.section.end}
            />
          </div>

          <div className="right-controls">
            {this.props.hasExpectedAnswers && this.props.hasScore &&
              <input
                title={trans('score', {}, 'quiz')}
                type="number"
                className="form-control score"
                value={this.props.solution.score}
                onChange={(e) => this.props.onUpdate('score', e.target.value)}
              />
            }

            {this.props.hasExpectedAnswers && !this.props.hasScore &&
              <input
                title={trans('score', {}, 'quiz')}
                type="checkbox"
                checked={0 < this.props.solution.score}
                onChange={(e) => this.props.onUpdate('score', e.target.checked ? 1 : 0)}
              />
            }

            <CallbackButton
              id={`section-${this.props.solution.section.id}-feedback-toggle`}
              className="btn-link"
              callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            >
              <span className="fa fa-fw fa-comments" />
            </CallbackButton>

            <CallbackButton
              id={`section-${this.props.solution.section.id}-delete`}
              className="btn-link"
              callback={() => this.props.onRemove()}
              dangerous={true}
            >
              <span className="fa fa-fw fa-trash" />
            </CallbackButton>
          </div>
        </div>

        <div
          className="form-group"
          style={{
            display: 'flex',
            alignItems: 'center'
          }}
        >
          <CallbackButton
            id={`section-${this.props.solution.section.id}-tolerance-play`}
            className="btn-link"
            callback={() => this.props.onPlay(
              parseFloat((this.props.solution.section.start - this.props.solution.section.startTolerance).toFixed(1)),
              parseFloat((this.props.solution.section.end + this.props.solution.section.endTolerance).toFixed(1))
            )}
          >
            <span className="fa fa-fw fa-play" />
          </CallbackButton>

          <div
            className="input-group"
            style={{
              marginRight: '5px',
              width: '40%'
            }}
          >
            <span className="input-group-addon">
              <b>{trans('start_tolerance', {}, 'audio')}</b>
            </span>
            <input
              title={trans('start_tolerance', {}, 'audio')}
              type="number"
              className="form-control section-start-tolerance"
              value={this.props.solution.section.startTolerance}
              min={0}
              step={0.1}
              onChange={(e) => {
                const start = parseFloat((this.props.solution.section.start - parseFloat(parseFloat(e.target.value).toFixed(1))).toFixed(1))
                const end = parseFloat((this.props.solution.section.end + this.props.solution.section.endTolerance).toFixed(1))

                if (!this.props.isOverlayed(start, end)) {
                  this.props.onUpdate('section', Object.assign({}, this.props.solution.section, {startTolerance: parseFloat(parseFloat(e.target.value).toFixed(1))}))
                }
              }}
            />
          </div>
          <div
            className="input-group"
            style={{
              marginRight: '5px',
              width: '40%'
            }}
          >
            <span className="input-group-addon">
              <b>{trans('end_tolerance', {}, 'audio')}</b>
            </span>
            <input
              title={trans('end_tolerance', {}, 'audio')}
              type="number"
              className="form-control section-end-tolerance"
              value={this.props.solution.section.endTolerance}
              min={0}
              step={0.1}
              onChange={(e) => {
                const start = parseFloat((this.props.solution.section.start - this.props.solution.section.startTolerance).toFixed(1))
                const end = parseFloat((this.props.solution.section.end + parseFloat(parseFloat(e.target.value).toFixed(1))).toFixed(1))

                if (!this.props.isOverlayed(start, end)) {
                  this.props.onUpdate('section', Object.assign({}, this.props.solution.section, {endTolerance: parseFloat(parseFloat(e.target.value).toFixed(1))}))
                }
              }}
            />
          </div>
        </div>

        {this.state.showFeedback &&
          <HtmlInput
            id={`section-${this.props.solution.section.id}-feedback`}
            className="feedback-control"
            value={this.props.solution.feedback}
            onChange={(value) => this.props.onUpdate('feedback', value)}
          />
        }
      </div>
    )
  }
}

Section.propTypes = {
  solution: T.shape({
    section: T.shape(SectionType.propTypes),
    score: T.number,
    feedback: T.string
  }).isRequired,
  selected: T.bool.isRequired,
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  onUpdate: T.func.isRequired,
  onRemove: T.func.isRequired,
  onPlay: T.func.isRequired,
  isOverlayed: T.func.isRequired
}

Section.defaultProps = {
  selected: false
}

class WaveformComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSection: null,
      toPlay: null
    }
  }

  render() {
    return (
      <div>
        {this.props.item.file &&
          <Waveform
            id={`waveform-editor-${this.props.item.id}`}
            url={asset(this.props.item.file)}
            regions={this.props.item.solutions.map(s => s.section)}
            selectedRegion={this.state.currentSection}
            toPlay={this.state.toPlay}
            eventsCallbacks={{
              'region-update-end': (region) => {
                const newSolutions = cloneDeep(this.props.item.solutions)
                let regionId = region.id
                let start = parseFloat(region.start.toFixed(1))
                let end = parseFloat(region.end.toFixed(1))
                const isTolerance = -1 < regionId.indexOf('tolerance-')

                if (isTolerance) {
                  regionId = regionId.substring(10)
                }
                const regionIdx = newSolutions.findIndex(r => r.section.id === regionId || r.section.regionId === regionId)

                if (!isTolerance) {
                  const solution = newSolutions.find(s => s.section.id === regionId || s.section.regionId === regionId)

                  if (solution) {
                    // For a existing region, check if start & end with tolerance don't overlay with another region
                    start -= parseFloat(solution.section.startTolerance.toFixed(1))
                    end += parseFloat(solution.section.endTolerance.toFixed(1))
                  } else {
                    // For new region, check if start & end with default tolerance don't overlay with another region
                    start -= parseFloat(this.props.item.tolerance.toFixed(1))
                    end += parseFloat(this.props.item.tolerance.toFixed(1))
                  }
                }
                if (!isOverlayed(this.props.item.solutions.map(s => s.section), start, end, regionIdx)) {
                  if (-1 < regionIdx) {
                    if (isTolerance) {
                      const startTolerance = parseFloat((newSolutions[regionIdx]['section']['start'] - parseFloat(region.start.toFixed(1))).toFixed(1))
                      const endTolerance = parseFloat((parseFloat(region.end.toFixed(1)) - newSolutions[regionIdx]['section']['end']).toFixed(1))

                      if (0 <= startTolerance && 0 <= endTolerance) {
                        newSolutions[regionIdx]['section'] = Object.assign({}, newSolutions[regionIdx]['section'], {
                          startTolerance: startTolerance,
                          endTolerance: endTolerance
                        })
                      }
                    } else {
                      newSolutions[regionIdx]['section'] = Object.assign({}, newSolutions[regionIdx]['section'], {
                        start: parseFloat(region.start.toFixed(1)),
                        end: parseFloat(region.end.toFixed(1))
                      })
                    }
                    this.setState({currentSection: newSolutions[regionIdx]['section']['id']})
                  } else {
                    const newId = makeId()

                    newSolutions.push({
                      section: {
                        id: newId,
                        regionId: region.id,
                        start: parseFloat(region.start.toFixed(1)),
                        end: parseFloat(region.end.toFixed(1)),
                        startTolerance: parseFloat(this.props.item.tolerance.toFixed(1)),
                        endTolerance: parseFloat(this.props.item.tolerance.toFixed(1))
                      },
                      score: 1
                    })
                    this.setState({currentSection: newId})
                  }
                } else {
                  this.setState({currentSection: null})
                }
                this.props.update('solutions', newSolutions)
              },
              'region-click': (region) => {
                const newSolutions = cloneDeep(this.props.item.solutions)
                const current = newSolutions.find(s => s.section.id === region.id || s.section.regionId === region.id)

                if (current) {
                  if (current.section.id === this.state.currentSection) {
                    this.setState({currentSection: null})
                  } else {
                    this.setState({currentSection: current.section.id})
                  }
                }
                this.props.update('solutions', newSolutions)
              }
            }}
          />
        }
        {this.props.item.solutions.map((s, idx) =>
          <Section
            key={s.section.id}
            solution={s}
            selected={s.section.id === this.state.currentSection}
            hasScore={this.props.hasScore}
            hasExpectedAnswers={this.props.item.hasExpectedAnswers}
            onUpdate={(property, value) => {
              const newSolutions = cloneDeep(this.props.item.solutions)
              const solution = newSolutions.find(ns => ns.section.id === s.section.id)

              if (solution) {
                solution[property] = value
                this.props.update('solutions', newSolutions)
              }
            }}
            onRemove={() => {
              const newSolutions = cloneDeep(this.props.item.solutions)
              this.setState({currentSection: null})
              newSolutions.splice(idx, 1)
              this.props.update('solutions', newSolutions)
            }}
            onPlay={(start, end) => this.setState({currentSection: s.section.id, toPlay: [start, end]})}
            isOverlayed={(start, end) => isOverlayed(this.props.item.solutions.map(s => s.section), start, end, idx)}
          />
        )}
      </div>
    )
  }
}

WaveformComponent.propTypes = {
  item: T.shape(WaveformItemType.propTypes).isRequired,
  hasScore: T.bool.isRequired,
  update: T.func.isRequired
}

const WaveformEditor = (props) => {
  const Waveform = (
    <WaveformComponent
      item={props.item}
      hasScore={props.hasAnswerScores}
      update={props.update}
    />
  )

  return (
    <FormData
      className="waveform-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'penalty',
              type: 'number',
              label: trans('global_penalty', {}, 'quiz'),
              required: true,
              displayed: (item) => item.hasExpectedAnswers && props.hasAnswerScores && item.score.type === SCORE_SUM,
              options: {
                min: 0
              }
            }, {
              name: 'answersLimit',
              type: 'number',
              label: trans('nb_authorized_selection', {}, 'quiz'),
              required: true,
              options: {
                min: 0
              }
            }, {
              name: 'tolerance',
              label: trans('default_tolerance', {}, 'quiz'),
              type: 'number',
              required: true,
              options: {
                min: 0,
                unit: trans('seconds')
              }
            }, {
              name: '_file',
              label: trans('pick_audio_file', {}, 'quiz'),
              type: 'file',
              required: true,
              calculated: () => null,
              onChange: (file) => {
                props.update('file', file.url)
                props.update('solutions', [])
              },
              options: {
                types: ['audio/*']
              }
            }, {
              name: 'data',
              label: trans('waveform'),
              hideLabel: true,
              required: true,
              component: Waveform
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(WaveformEditor, ItemEditorTypes, {
  item: T.shape(WaveformItemType.propTypes).isRequired
})

export {
  WaveformEditor
}
