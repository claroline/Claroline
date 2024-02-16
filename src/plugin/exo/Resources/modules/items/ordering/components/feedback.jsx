import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentHtml} from '#/main/app/content/components/html'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'

import {utils} from '#/plugin/exo/items/ordering/utils'
import {constants} from '#/plugin/exo/items/ordering/constants'
import isEmpty from 'lodash/isEmpty'
import {trans} from '#/main/app/intl'

const OrderingAnswerFeedback = (props) =>
  <div className={classes('ordering-answer-item answer-item', {
    'correct-answer': props.hasExpectedAnswer && props.valid,
    'incorrect-answer': props.hasExpectedAnswer && !props.valid,
    'selected-answer': !props.hasExpectedAnswer
  })}>
    {props.hasExpectedAnswer &&
      <WarningIcon className="ordering-item-tick" valid={props.valid}/>
    }
    <ContentHtml className="ordering-item-content">
      {props.content}
    </ContentHtml>

    <Feedback
      id={`ordering-answer-${props.id}-feedback`}
      feedback={props.feedback}
    />
  </div>

OrderingAnswerFeedback.propTypes = {
  id: T.string.isRequired,
  hasExpectedAnswer: T.bool.isRequired,
  valid: T.bool.isRequired,
  content: T.string.isRequired,
  feedback: T.string
}

const OrderingFeedback = props =>
  <div className={classes('ordering-feedback', props.item.direction)}>
    <div className={classes('ordering-answer-items', props.item.direction)}>
      {props.item.mode === constants.MODE_INSIDE ?
        props.answer.map((a) =>
          <OrderingAnswerFeedback
            id={a.itemId}
            key={a.itemId}
            hasExpectedAnswer={props.item.hasExpectedAnswers}
            valid={utils.answerIsValid(a, props.item.solutions)}
            content={props.item.items.find(item => item.id === a.itemId).data}
            feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
          />
        )
        :
        props.item.solutions.filter(solution => undefined === props.answer.find(answer => answer.itemId === solution.itemId)).map((solution) =>
          <OrderingAnswerFeedback
            key={solution.itemId}
            id={solution.itemId}
            hasExpectedAnswer={props.item.hasExpectedAnswers}
            valid={solution.score < 1}
            content={props.item.items.find(item => item.id === solution.itemId).data}
            feedback={solution.feedback}
          />
        )
      }
    </div>

    {props.item.mode === constants.MODE_BESIDE &&
      <div className={classes('answer-zone ordering-answer-items', props.item.direction)}>
        {isEmpty(props.answer) &&
          <div className="ordering-drop-container">{trans('no_answer', {}, 'quiz')}</div>
        }

        {props.answer.map((a) =>
          <OrderingAnswerFeedback
            key={a.itemId}
            id={a.itemId}
            hasExpectedAnswer={props.item.hasExpectedAnswers}
            valid={utils.answerIsValid(a, props.item.solutions)}
            content={props.item.items.find(item => item.id === a.itemId).data}
            feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
          />
        )}
      </div>
    }
  </div>

OrderingFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    mode: T.string.isRequired,
    direction: T.string.isRequired,
    score: T.object.isRequired,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.array.isRequired
}

OrderingFeedback.defaultProps = {
  answer: []
}

export {
  OrderingFeedback
}
