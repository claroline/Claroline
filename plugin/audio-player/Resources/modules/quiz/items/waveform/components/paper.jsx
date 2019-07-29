import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'

import {isCorrectAnswer} from '#/plugin/audio-player/quiz/items/waveform/utils'
import {constants} from '#/plugin/audio-player/quiz/items/waveform/constants'
import {constants as waveformConstants} from '#/plugin/audio-player/waveform/constants'
import {WaveformItem as WaveformItemType} from '#/plugin/audio-player/quiz/items/waveform/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'
import {AnswerTable} from '#/plugin/audio-player/quiz/items/waveform/components/answer-table'
import {AnswerStatsTable} from '#/plugin/audio-player/quiz/items/waveform/components/answer-stats-table'

const WaveformPaper = props =>
  <PaperTabs
    id={props.item.id}
    showExpected={props.showExpected}
    showStats={props.showStats}
    showYours={props.showYours}
    yours={
      <div className="waveform-paper">
        <Waveform
          id={`waveform-paper-yours-${props.item.id}`}
          url={asset(props.item.url)}
          editable={false}
          regions={props.answer.map(a => Object.assign({}, a, {
            color: !props.item.hasExpectedAnswers || isCorrectAnswer(props.item.solutions, a.start, a.end) ?
              waveformConstants.COLORS.section :
              constants.INCORRECT_COLOR
          }))}
        />
        {props.answer.length > 0 &&
          <AnswerTable
            title={trans('your_answers', {}, 'quiz')}
            sections={props.answer.map(a => {
              const solution = props.item.solutions.find(s => a.start >= s.section.start - s.section.startTolerance &&
                a.start <= s.section.start &&
                a.end >= s.section.end &&
                a.end <= s.section.end + s.section.endTolerance
              )

              return Object.assign({}, a, {
                start: a.start,
                end: a.end,
                score: solution ? solution.score : -props.item.penalty,
                feedback: solution ? solution.feedback : null
              })
            })}
            showScore={props.item.hasExpectedAnswers && props.showScore}
            highlightScore={props.item.hasExpectedAnswers}
          />
        }
      </div>
    }
    expected={
      <div>
        <Waveform
          id={`waveform-paper-expected-${props.item.id}`}
          url={asset(props.item.url)}
          editable={false}
          regions={props.item.solutions.filter(s => 0 < s.score).map(s => s.section)}
        />
        {0 < props.item.solutions.filter(s => 0 < s.score).length &&
          <AnswerTable
            title={trans('expected_zones', {}, 'quiz')}
            sections={props.item.solutions.filter(s => 0 < s.score).map(s => Object.assign({}, s.section, {
              start: s.section.start,
              end: s.section.end,
              score: s.score,
              feedback: s.feedback
            }))}
            showScore={props.showScore}
            highlightScore={false}
            showLegend={true}
          />
        }
      </div>
    }
    stats={
      <div>
        <Waveform
          id={`waveform-paper-stats-${props.item.id}`}
          url={asset(props.item.url)}
          editable={false}
          regions={props.item.solutions.map(s => Object.assign({}, s.section, {
            color: 0 < s.score ?
              waveformConstants.COLORS.section :
              constants.INCORRECT_COLOR
          }))}
        />
        <AnswerStatsTable
          title={trans('stats', {}, 'quiz')}
          sections={props.item.solutions.map(s => Object.assign({}, s.section, {
            start: s.section.start,
            end: s.section.end,
            score: s.score
          }))}
          stats={props.stats}
          hasExpectedAnswers={props.item.hasExpectedAnswers}
        />
      </div>
    }
  />

WaveformPaper.propTypes = {
  item: T.shape(WaveformItemType.propTypes).isRequired,
  answer: T.array,
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showStats: T.bool.isRequired,
  showYours: T.bool.isRequired,
  stats: T.shape({
    sections: T.object,
    unanswered: T.number,
    total: T.number
  })
}

WaveformPaper.defaultProps = {
  answer: []
}

export {
  WaveformPaper
}
