import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Html} from '#/main/app/components/html'

import {FeedbackButton} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'

const OrderingAnswerItem = (props) =>
  <div className={classes('ordering-answer-item answer-item', props.className)}>
    {props.hasExpectedAnswers &&
      <WarningIcon className="ordering-item-tick" valid={props.valid} />
    }

    <Html className="ordering-item-content">
      {props.content}
    </Html>

    <FeedbackButton
      id={`ordering-answer-${props.id}-feedback`}
      feedback={props.feedback}
    />

    {props.showScore &&
      <SolutionScore score={props.score} />
    }
  </div>

OrderingAnswerItem.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  content: T.string.isRequired,
  feedback: T.string,
  score: T.number,
  hasExpectedAnswers: T.bool,
  valid: T.bool,
  showScore: T.bool
}

export {
  OrderingAnswerItem
}
