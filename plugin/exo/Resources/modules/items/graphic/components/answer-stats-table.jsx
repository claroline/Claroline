import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {tex} from '#/main/app/intl/translation'

import {SHAPE_RECT} from '#/plugin/exo/items/graphic/constants'
import {AnswerStats} from '#/plugin/exo/items/components/stats'

export const AnswerStatsTable = props =>
  <div className="answers-table">
    <h3 className="title">{props.title}</h3>
    {props.areas.map((area, idx) =>
      <div key={area.id} className={classes('answer-row', {
        'selected-answer': area.score > 0,
        'stats-answer': area.score <= 0
      })}>
        <span className="info-block">
          <span><strong>{idx + 1}</strong></span>
          <span style={{
            display: 'inline-block',
            width: '24px',
            height: '24px',
            backgroundColor: tinycolor(area.color).lighten(20).toString(),
            border: `solid 1px ${area.color}`,
            borderRadius: area.shape === SHAPE_RECT ? 0 : '12px'
          }}/>
        </span>
        <span className="info-block">
          <AnswerStats stats={{
            value: props.stats.areas[area.id] ? props.stats.areas[area.id] : 0,
            total: props.stats.total
          }} />
        </span>
      </div>
    )}
    {props.stats.areas['_others'] &&
      <div className="answer-row stats-answer">
        <span className="info-block">
          {tex('other_answers')}
        </span>
        <span className="info-block">
          <AnswerStats stats={{
            value: props.stats.areas['_others'],
            total: props.stats.total
          }} />
        </span>
      </div>
    }
    <div className="answer-row unanswered-item">
      <span className="info-block">
        {tex('unanswered')}
      </span>
      <span className="info-block">
        <AnswerStats stats={{
          value: props.stats.unanswered ? props.stats.unanswered : 0,
          total: props.stats.total
        }} />
      </span>
    </div>
  </div>

AnswerStatsTable.propTypes = {
  title: T.string.isRequired,
  areas: T.arrayOf(T.shape({
    id: T.string.isRequired,
    score: T.number,
    color: T.string.isRequired,
    shape: T.string.isRequired,
    feedback: T.string
  })).isRequired,
  stats: T.shape({
    areas: T.object,
    unanswered: T.number,
    total: T.number
  })
}
