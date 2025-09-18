import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import {SHAPE_RECT} from '#/plugin/exo/items/graphic/constants'
import {AnswerStats} from '#/plugin/exo/components/answer-stats'

const AnswerStatsTable = props => {
  return (
    <div className="mt-4">
      {props.areas.map((area, idx) =>
        <div key={area.id} className={classes('graphic-answer-item answer-item d-flex align-items-center', props.hasExpectedAnswers && {
          'selected-answer': area.score > 0,
          'stats-answer': area.score <= 0
        })}>
          <div className="flex-fill d-flex gap-2 align-items-center">
            <span className="d-inline-block" style={{
              width: '1.5rem',
              height: '1.5rem',
              backgroundColor: area.color || '#000',
              borderRadius: area.shape === SHAPE_RECT ? 0 : '50rem'
            }}/>
            <strong>{idx + 1}</strong>
          </div>
          <AnswerStats stats={{
            value: props.stats.areas && props.stats.areas[area.id] ? props.stats.areas[area.id] : 0,
            total: props.stats.total
          }} />
        </div>
      )}

      {props.stats.areas['_others'] &&
        <div className="graphic-answer-item answer-item d-flex align-items-center stats-answer">
          <div className="flex-fill d-flex gap-2 align-items-center">
            {trans('other_answers', {}, 'quiz')}
          </div>
          <AnswerStats stats={{
            value: props.stats.areas && props.stats.areas['_others'],
            total: props.stats.total
          }} />
        </div>
      }

      <div className="graphic-answer-item answer-item d-flex align-items-center unanswered-item mb-0">
        <div className="flex-fill d-flex gap-2 align-items-center">
          {trans('unanswered', {}, 'quiz')}
        </div>
        <AnswerStats stats={{
          value: props.stats.unanswered ? props.stats.unanswered : 0,
          total: props.stats.total
        }} />
      </div>
    </div>
  )
}

AnswerStatsTable.propTypes = {
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
  }),
  hasExpectedAnswers: T.bool.isRequired
}

export {
  AnswerStatsTable
}
