import React from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import {displayDate} from '#/main/app/intl/date'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'
import {SolutionScore} from '#/plugin/exo/components/score'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'

const AnswerTable = (props) =>
  <div className="words-paper">
    {props.answers.map(solution =>
      <div
        key={solution.text}
        className={classes('word-item answer-item', {
          'correct-answer': props.hasExpectedAnswers && solution.score > 0,
          'incorrect-answer': props.hasExpectedAnswers && solution.score <= 0,
          'selected-answer': !props.hasExpectedAnswers && solution.score > 0
        })}
      >
        <span className="word-label">
          {props.hasExpectedAnswers &&
            <WarningIcon valid={solution.score > 0}/>
          }

          {'date' === props.contentType ? displayDate(solution.text) : solution.text}
        </span>

        <Feedback
          id={`${solution.text}-feedback`}
          feedback={solution.feedback}
        /> {'\u00a0'}

        {props.showScore &&
          <SolutionScore score={solution.score}/>
        }
      </div>
    )}
  </div>

AnswerTable.propTypes = {
  contentType: T.string.isRequired,
  answers: T.arrayOf(T.shape({
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string
  })),
  hasExpectedAnswers: T.bool.isRequired,
  showScore: T.bool.isRequired
}

const WordsSolutions = (props) => {
  const solutions = props.answers.slice(0)
  const halfLength = Math.ceil(solutions.length / 2)
  const leftSide = solutions.splice(0, halfLength)
  const rightSide = solutions

  return (
    <div className={classes('row', props.className)}>
      <div className={classes({
        'col-md-12': 0 === rightSide.length,
        'col-md-6': 0 !== rightSide.length
      })}>
        <AnswerTable
          answers={leftSide}
          contentType={props.contentType}
          showScore={props.showScore}
          hasExpectedAnswers={props.hasExpectedAnswers}
        />
      </div>

      {0 !== rightSide.length &&
        <div className="col-md-6">
          <AnswerTable
            answers={rightSide}
            contentType={props.contentType}
            showScore={props.showScore}
            hasExpectedAnswers={props.hasExpectedAnswers}
          />
        </div>
      }
    </div>
  )
}

WordsSolutions.propTypes = {
  className: T.string,
  contentType: T.string.isRequired,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  answers: T.arrayOf(T.shape({
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string
  }))
}

export {
  WordsSolutions
}
