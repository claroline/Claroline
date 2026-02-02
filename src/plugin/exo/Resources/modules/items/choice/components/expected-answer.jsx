import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {SCORE_FIXED, SCORE_RULES} from '#/plugin/exo/scores/constants'
import {utils} from '#/plugin/exo/items/choice/utils'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {ChoiceItem as ChoiceItemTypes} from '#/plugin/exo/items/choice/prop-types'
import {Html} from '#/main/app/components/html'
import {NUMBERING_NONE} from '#/main/app/utils/numbering'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'

const ChoiceExpectedAnswer = props =>
  <div className="choice-paper">
    <div className={classes('choice-answer-items', props.item.direction)}>
      {props.item.solutions.map((solution, idx) =>
        <label
          key={utils.expectedId(solution.id)}
          htmlFor={utils.expectedId(solution.id)}
          className={classes('answer-item choice-answer-item', {
            'selected-answer': solution.score > 0
          })}
        >
          <input
            className="choice-item-tick form-check-input"
            checked={solution.score > 0}
            id={utils.expectedId(solution.id)}
            name={utils.expectedId(props.item.id)}
            type={props.item.multiple ? 'checkbox': 'radio'}
            disabled={true}
          />

          {props.item.numbering !== NUMBERING_NONE &&
            <b>
              {getNumbering(props.item.numbering, idx)} {'\u00a0'} {/*non-breaking whitespace */}
            </b>
          }

          <Html className="choice-item-content">
            {utils.getChoiceById(props.item.choices, solution.id).data}
          </Html>

          <Feedback
            id={`${solution.id}-feedback-expected`}
            feedback={solution.feedback}
          />

          {props.showScore && -1 === [SCORE_FIXED, SCORE_RULES].indexOf(props.item.score.type) &&
            <SolutionScore score={solution.score} />
          }
        </label>
      )}
    </div>
  </div>

ChoiceExpectedAnswer.propTypes = {
  item: T.shape(
    ChoiceItemTypes.propTypes
  ).isRequired,
  showScore: T.bool.isRequired
}

export {
  ChoiceExpectedAnswer
}
