import React, {Fragment} from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {AnswerStats} from '#/plugin/exo/items/components/stats'

const AnswerStatsTable = (props) =>
  <Fragment>
    {props.solutions.map(solution =>
      <div
        key={solution.text}
        className={classes('word-item answer-item', {
          'selected-answer': props.hasExpectedAnswers && solution.score > 0
        })}
      >
        <span className="word-label">{'date' === props.contentType ? displayDate(solution.text) : solution.text}</span>
        <AnswerStats stats={{
          value: props.stats.words[solution.text] ? props.stats.words[solution.text] : 0,
          total: props.stats.total
        }}/>
      </div>
    )}

    {!props.isCorrect && props.stats.words['_others'] &&
      <div className="word-item answer-item">
        <span className="word-label">{trans('other_answers', {}, 'quiz')}</span>
        <AnswerStats stats={{
          value: props.stats.words['_others'],
          total: props.stats.total
        }}/>
      </div>
    }
  </Fragment>

AnswerStatsTable.propTypes = {
  contentType: T.string.isRequired,
  solutions: T.arrayOf(T.shape({
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string
  })),
  stats: T.shape({
    words: T.object,
    unanswered: T.number,
    total: T.number
  }),
  isCorrect: T.bool,
  hasExpectedAnswers: T.bool
}

const WordsStats = (props) => {
  const solutions = props.solutions.slice(0)
  const halfLength = Math.ceil(solutions.length / 2)
  const leftSide = solutions.splice(0, halfLength)
  const rightSide = solutions

  return (
    <div className="words-stats">
      <div className="row">
        <div className={classes({
          'col-md-12': 0 === rightSide.length,
          'col-md-6': 0 !== rightSide.length
        })}>
          <AnswerStatsTable
            contentType={props.contentType}
            solutions={leftSide}
            stats={props.stats}
            isCorrect={true}
            hasExpectedAnswers={props.hasExpectedAnswers}
          />
        </div>

        {0 !== rightSide.length &&
          <div className="col-md-6">
            <AnswerStatsTable
              contentType={props.contentType}
              solutions={rightSide}
              stats={props.stats}
              isCorrect={false}
              hasExpectedAnswers={props.hasExpectedAnswers}
            />
          </div>
        }
      </div>

      <div className="row">
        <div className="col-md-12">
          <div className="word-item answer-item unanswered-item">
            <span className="word-label">{trans('unanswered', {}, 'quiz')}</span>

            <AnswerStats stats={{
              value: props.stats.unanswered ? props.stats.unanswered : 0,
              total: props.stats.total
            }}/>
          </div>
        </div>
      </div>
    </div>
  )
}

WordsStats.propTypes = {
  contentType: T.string.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  solutions: T.arrayOf(T.shape({
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string
  })),
  stats: T.shape({
    words: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  WordsStats
}
