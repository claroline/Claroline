import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

import {WaveformItem as WaveformItemType} from '#/plugin/audio-player/quiz/items/waveform/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'
import {AnswerTable} from '#/plugin/audio-player/quiz/items/waveform/components/answer-table'

const WaveformExpectedAnswer = (props) =>
  <div>
    <Waveform
      id={`waveform-paper-expected-${props.item.id}`}
      url={asset(props.item.file)}
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

WaveformExpectedAnswer.propTypes = {
  item: T.shape(
    WaveformItemType.propTypes
  ).isRequired,
  showScore: T.bool.isRequired
}

export {
  WaveformExpectedAnswer
}
