import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

import {constants} from '#/plugin/audio-player/quiz/items/waveform/constants'
import {constants as waveformConstants} from '#/plugin/audio-player/waveform/constants'
import {WaveformItem as WaveformItemType} from '#/plugin/audio-player/quiz/items/waveform/prop-types'
import {Waveform} from '#/plugin/audio-player/waveform/components/waveform'
import {AnswerStatsTable} from '#/plugin/audio-player/quiz/items/waveform/components/answer-stats-table'

const WaveformStats = props =>
  <div>
    <Waveform
      id={`waveform-paper-stats-${props.item.id}`}
      url={asset(props.item.file)}
      editable={false}
      regions={props.item.solutions.map(s => Object.assign({}, s.section, {
        color: 0 < s.score ?
          waveformConstants.COLORS.section :
          constants.INCORRECT_COLOR
      }))}
    />
    <AnswerStatsTable
      title={trans('statistics')}
      sections={props.item.solutions.map(s => Object.assign({}, s.section, {
        start: s.section.start,
        end: s.section.end,
        score: s.score
      }))}
      stats={props.stats}
      hasExpectedAnswers={props.item.hasExpectedAnswers}
    />
  </div>

WaveformStats.propTypes = {
  item: T.shape(
    WaveformItemType.propTypes
  ).isRequired,
  stats: T.shape({
    sections: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  WaveformStats
}
