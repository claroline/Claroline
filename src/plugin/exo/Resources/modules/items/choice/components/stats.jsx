import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'

import {utils} from '#/plugin/exo/items/choice/utils'
import {AnswerStats} from '#/plugin/exo/components/answer-stats'

import {ChoiceItem as ChoiceItemTypes} from '#/plugin/exo/items/choice/prop-types'

const ChoiceStats = (props) => {
  return (
    <div className="choice-paper">
      <div className={classes('choice-answer-items', props.item.direction)}>
        {props.item.solutions.map(solution =>
          <label
            key={solution.id}
            className={classes('answer-item choice-answer-item', props.item.hasExpectedAnswers && {
              'selected-answer': solution.score > 0
            })}
          >
            <Html className="choice-item-content">
              {utils.getChoiceById(props.item.choices, solution.id).data}
            </Html>

            <AnswerStats stats={{
              value: props.stats.choices && props.stats.choices[solution.id] ?
                props.stats.choices[solution.id] :
                0,
              total: props.stats.total
            }} />
          </label>
        )}

        <label className='answer-item choice-answer-item unanswered-item'>
          <div className="choice-item-content">
            {trans('unanswered', {}, 'quiz')}
          </div>

          <AnswerStats stats={{
            value: props.stats.unanswered ? props.stats.unanswered : 0,
            total: props.stats.total
          }} />
        </label>
      </div>
    </div>
  )
}

ChoiceStats.propTypes = {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired,
  stats: T.shape({
    choices: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  ChoiceStats
}
