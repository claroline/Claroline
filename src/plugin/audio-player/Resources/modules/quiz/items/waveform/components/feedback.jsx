import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

import {isCorrectAnswer} from '#/plugin/audio-player/quiz/items/waveform/utils'
import {constants} from '#/plugin/audio-player/quiz/items/waveform/constants'
import {constants as waveformConstants} from '#/plugin/audio-player/waveform/constants'
import {WaveformItem as WaveformItemType} from '#/plugin/audio-player/quiz/items/waveform/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'
import {AnswerTable} from '#/plugin/audio-player/quiz/items/waveform/components/answer-table'

const WaveformFeedback = props =>
  <div className="waveform-feedback">
    <Waveform
      id={`waveform-feedback-${props.item.id}`}
      url={asset(props.item.file)}
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
            a.start <= s.section.start + s.section.startTolerance &&
            a.end >= s.section.end - s.section.endTolerance &&
            a.end <= s.section.end + s.section.endTolerance
          )

          return Object.assign({}, a, {
            start: a.start,
            end: a.end,
            score: solution ? solution.score : 0,
            feedback: solution ? solution.feedback : null
          })
        })}
        showScore={false}
        highlightScore={props.item.hasExpectedAnswers}
      />
    }
  </div>

WaveformFeedback.propTypes = {
  item: T.shape(WaveformItemType.propTypes).isRequired,
  answer: T.array
}

WaveformFeedback.defaultProps = {
  answer: []
}

export {
  WaveformFeedback
}
