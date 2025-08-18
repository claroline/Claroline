import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {SCORE_SUM} from '#/plugin/exo/scores/constants'
import {constants} from '#/plugin/exo/items/ordering/constants'
import {OrderingAnswerItem} from '#/plugin/exo/items/ordering/components/answer-item'

const OrderingExpectedAnswer = props => {
  return (
    <div className={classes('ordering-paper', props.item.direction)}>
      <div className={classes('ordering-answer-items', props.item.direction)}>
        {props.item.solutions
          .filter(solution => props.item.mode === constants.MODE_INSIDE || solution.score < 1)
          .map((solution) =>
            <OrderingAnswerItem
              id={solution.itemId}
              key={solution.itemId}
              className={props.item.mode === constants.MODE_INSIDE ? 'selected-answer' : undefined}
              showScore={props.showScore && props.item.score.type === SCORE_SUM}

              content={props.item.items.find(item => item.id === solution.itemId).data}
              feedback={solution.feedback}
              score={solution.score}
            />
          )
        }
      </div>

      {props.item.mode === constants.MODE_BESIDE &&
        <div className={classes('answer-zone ordering-answer-items', props.item.direction)}>
          {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
            <OrderingAnswerItem
              id={solution.itemId}
              key={solution.itemId}
              className="selected-answer"
              showScore={props.showScore && props.item.score.type === SCORE_SUM}

              content={props.item.items.find(item => item.id === solution.itemId).data}
              feedback={solution.feedback}
              score={solution.score}
            />
          )}
        </div>
      }
    </div>
  )
}

OrderingExpectedAnswer.propTypes = {
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
  showScore: T.bool.isRequired
}

export {
  OrderingExpectedAnswer
}
