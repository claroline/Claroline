import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import classes from 'classnames'

import {trans} from '#/main/app/intl'

import {SCORE_SUM} from '#/plugin/exo/scores/constants'
import {utils} from '#/plugin/exo/items/ordering/utils'
import {constants} from '#/plugin/exo/items/ordering/constants'
import {OrderingAnswerItem} from '#/plugin/exo/items/ordering/components/answer-item'

const OrderingFeedback = props =>
  <div className={classes('ordering-feedback', props.item.direction)}>
    <div className={classes('ordering-answer-items', props.item.direction)}>
      {props.item.mode === constants.MODE_INSIDE ?
        props.answer.map((a) =>
          <OrderingAnswerItem
            id={a.itemId}
            key={a.itemId}
            className={classes(props.item.hasExpectedAnswers ?
              utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type) :
              'selected-answer'
            )}
            hasExpectedAnswers={props.item.hasExpectedAnswers}
            valid={utils.answerIsValid(a, props.item.solutions)}
            showScore={props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions)}

            content={props.item.items.find(item => item.id === a.itemId).data}
            score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}
            feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
          />
        )
        :
        props.item.solutions.filter(solution => undefined === props.answer.find(answer => answer.itemId === solution.itemId)).map((solution) =>
          <OrderingAnswerItem
            id={solution.itemId}
            key={solution.itemId}
            className={classes(props.item.hasExpectedAnswers ?
              solution.score > 0 ? 'incorrect-answer' : 'correct-answer' :
              undefined
            )}
            hasExpectedAnswers={props.item.hasExpectedAnswers}
            valid={solution.score < 1}
            showScore={props.showScore && solution.score > 0}

            content={props.item.items.find(item => item.id === solution.itemId).data}
            score={solution.score}
            feedback={solution.score > 0 && solution.feedback}
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
          <OrderingAnswerItem
            id={a.itemId}
            key={a.itemId}
            className={classes(props.item.hasExpectedAnswers ?
              utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type) :
              undefined
            )}
            hasExpectedAnswers={props.item.hasExpectedAnswers}
            valid={utils.answerIsValid(a, props.item.solutions)}
            showScore={props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions)}

            content={props.item.items.find(item => item.id === a.itemId).data}
            feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
            score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}
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
  answer: T.array.isRequired,
  showScore: T.bool
}

OrderingFeedback.defaultProps = {
  answer: []
}

export {
  OrderingFeedback
}
