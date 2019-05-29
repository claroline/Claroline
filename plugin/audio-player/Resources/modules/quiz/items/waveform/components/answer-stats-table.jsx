import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import {AnswerStats} from '#/plugin/exo/items/components/stats'

export const AnswerStatsTable = props =>
  <div
    className="answers-table"
    style={{
      width: '60%',
      margin: 'auto'
    }}
  >
    <h3 className="title">{props.title}</h3>

    {props.sections.map((section) =>
      <div
        key={section.id}
        className={classes('answer-row', props.hasExpectedAnswers && {
          'selected-answer': section.score > 0,
          'stats-answer': section.score <= 0
        })}
        style={{
          minHeight: '34px',
          border: 'solid 1px #DDDDDD',
          padding: '6px',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}
      >
        <span
          className="info-block"
          style={{
            display: 'flex',
            alignItems: 'center'
          }}
        >
          <input
            className="form-control"
            type="text"
            disabled={true}
            value={section.start}
            style={{
              maxWidth: '100px',
              marginRight: '2px',
              marginLeftt: '2px'
            }}
          />
          <input
            className="form-control"
            type="text"
            disabled={true}
            value={section.end}
            style={{
              maxWidth: '100px',
              marginRight: '2px',
              marginLeftt: '2px'
            }}
          />
        </span>
        <span className="info-block">
          <AnswerStats stats={{
            value: props.stats.sections[section.id] ? props.stats.sections[section.id] : 0,
            total: props.stats.total
          }} />
        </span>
      </div>
    )}
    {props.stats.sections['_others'] &&
      <div
        className="answer-row stats-answer"
        style={{
          minHeight: '34px',
          border: 'solid 1px #DDDDDD',
          padding: '6px',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}
      >
        <span className="info-block">
          {trans('other_answers', {}, 'quiz')}
        </span>
        <span className="info-block">
          <AnswerStats stats={{
            value: props.stats.sections['_others'],
            total: props.stats.total
          }} />
        </span>
      </div>
    }
    <div
      className="answer-row unanswered-item"
      style={{
        minHeight: '34px',
        border: 'solid 1px #DDDDDD',
        padding: '6px',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
      }}
    >
      <span className="info-block">
        {trans('unanswered', {}, 'quiz')}
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
  sections: T.arrayOf(T.shape({
    id: T.string.isRequired,
    score: T.number,
    feedback: T.string
  })).isRequired,
  stats: T.shape({
    sections: T.object,
    unanswered: T.number,
    total: T.number
  }),
  hasExpectedAnswers: T.bool.isRequired
}
