import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {utils} from '#/plugin/exo/items/choice/utils'
import {WarningIcon} from '#/plugin/exo/items/choice/components/warning-icon'

import {ChoiceItem as ChoiceItemTypes} from '#/plugin/exo/items/choice/prop-types'
import {Html} from '#/main/app/components/html'
import {SCORE_FIXED, SCORE_RULES} from '#/plugin/exo/scores/constants'
import {SolutionScore} from '#/plugin/exo/components/score'

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
              className="choice-item-tick form-check-input"
              name={utils.answerId(props.item.id)}
              type={props.item.multiple ? 'checkbox': 'radio'}
              checked={utils.isSolutionChecked(solution, props.answer)}
              disabled={true}
            />
          }

          <Html className="choice-item-content">
            {utils.getChoiceById(props.item.choices, solution.id).data}
          </Html>

          {utils.isSolutionChecked(solution, props.answer) &&
            <>
              <Feedback
                id={`${solution.id}-feedback`}
                feedback={solution.feedback}
              />

              {props.showScore && -1 === [SCORE_FIXED, SCORE_RULES].indexOf(props.item.score.type) &&
                <SolutionScore score={solution.score} />
              }
            </>
          }
        </label>
      )}
    </div>
  </div>

ChoiceFeedback.propTypes = {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired,
  answer: T.array,
  showScore: T.bool
}

export {
  ChoiceFeedback
}
