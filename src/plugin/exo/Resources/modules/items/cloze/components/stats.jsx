import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import {ContentHtml} from '#/main/app/content/components/html'

import {AnswerStats} from '#/plugin/exo/items/components/stats'
import {utils} from '#/plugin/exo/items/cloze/utils'

// TODO : move elsewhere it's also used by Graphic item

const AnswerStatsTable = props =>
  <div className="answer-stats-table">
    <h3>
      <span className="badge">{props.title}</span>
    </h3>
    {props.solution && props.solution.answers && props.solution.answers.map((answer, idx) => {
      const key = utils.getKey(props.solution.holeId, answer.text, props.solutions)

      return (
        <div key={idx} className={classes('answer-item', {'selected-answer': props.hasExpectedAnswers && answer.score > 0})}>
          <ContentHtml>{answer.text}</ContentHtml>
          <div>
            <AnswerStats stats={{
              value: props.stats.holes[props.solution.holeId] && props.stats.holes[props.solution.holeId][key] ?
                props.stats.holes[props.solution.holeId][key] :
                0,
              total: props.stats.total
            }} />
          </div>
        </div>
      )
    })}
    {props.solution && props.stats.holes[props.solution.holeId] && props.stats.holes[props.solution.holeId]['_others'] &&
      <div className="answer-item">
        <div>{trans('other_answers', {}, 'quiz')}</div>
        <div>
          <AnswerStats stats={{
            value: props.stats.holes[props.solution.holeId]['_others'],
            total: props.stats.total
          }} />
        </div>
      </div>
    }
    {props.solution && props.stats.holes[props.solution.holeId] && props.stats.holes[props.solution.holeId]['_unanswered'] &&
      <div className="answer-item unanswered-item">
        <div>{trans('unanswered', {}, 'quiz')}</div>
        <div>
          <AnswerStats stats={{
            value: props.stats.holes[props.solution.holeId]['_unanswered'],
            total: props.stats.total
          }} />
        </div>
      </div>
    }
  </div>

AnswerStatsTable.propTypes = {
  title: T.string.isRequired,
  solutions: T.arrayOf(T.shape({
    holeId: T.string.isRequired,
    answers: T.arrayOf(T.object)
  })).isRequired,
  solution: T.shape({
    holeId: T.string.isRequired,
    answers: T.arrayOf(T.object)
  }).isRequired,
  stats: T.shape({
    holes: T.object,
    unanswered: T.number,
    total: T.number
  }).isRequired,
  hasExpectedAnswers: T.bool.isRequired
}

const AnswersStatsTable = props =>
  <div className="answers-stats-table">
    {props.solutions.map((solution, idx) => {
      return (
        <AnswerStatsTable
          key={`stats-table-${solution.holeId}`}
          title={`${idx + 1}`}
          solutions={props.solutions}
          solution={solution}
          stats={props.stats}
          hasExpectedAnswers={props.hasExpectedAnswers}
        />
      )
    })}
    <div className="answer-item unanswered-item">
      <div>{trans('unanswered', {}, 'quiz')}</div>
      <div>
        <AnswerStats stats={{
          value: props.stats.unanswered ? props.stats.unanswered : 0,
          total: props.stats.total
        }} />
      </div>
    </div>
  </div>

AnswersStatsTable.propTypes = {
  solutions: T.arrayOf(T.shape({
    holeId: T.string.isRequired,
    answers: T.arrayOf(T.object)
  })).isRequired,
  stats: T.shape({
    holes: T.object,
    unanswered: T.number,
    total: T.number
  }).isRequired,
  hasExpectedAnswers: T.bool.isRequired
}

export {
  AnswerStatsTable,
  AnswersStatsTable
}
