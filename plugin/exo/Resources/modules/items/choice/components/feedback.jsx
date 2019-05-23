import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {utils} from '#/plugin/exo/items/choice/utils'
import {WarningIcon} from '#/plugin/exo/items/choice/components/warning-icon'

import {ChoiceItem as ChoiceItemTypes} from '#/plugin/exo/items/choice/prop-types'

const ChoiceFeedback = props =>
  <div className="choice-feedback">
    <div className={classes('choice-answer-items', props.item.direction)}>
      {props.item.solutions.map(solution =>
        <label
          key={utils.answerId(solution.id)}
          className={classes('answer-item choice-answer-item', utils.getAnswerClassForSolution(solution, props.answer, props.item.hasExpectedAnswers))}>
          {props.item.hasExpectedAnswers && utils.isSolutionChecked(solution, props.answer) ?
            <WarningIcon className="choice-item-tick" solution={solution} answers={props.answer} />
            :
            <input
              id={utils.answerId(solution.id)}
              className="choice-item-tick"
              name={utils.answerId(props.item.id)}
              type={props.item.multiple ? 'checkbox': 'radio'}
              checked={utils.isSolutionChecked(solution, props.answer)}
              disabled
            />
          }

          <HtmlText className="choice-item-content">
            {utils.getChoiceById(props.item.choices, solution.id).data}
          </HtmlText>

          {utils.isSolutionChecked(solution, props.answer) &&
            <div className="choice-item-feedback">
              <Feedback
                id={`${solution.id}-feedback`}
                feedback={solution.feedback}
              />
            </div>
          }
        </label>
      )}
    </div>
  </div>

ChoiceFeedback.propTypes = {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired,
  answer: T.array
}

export {
  ChoiceFeedback
}
