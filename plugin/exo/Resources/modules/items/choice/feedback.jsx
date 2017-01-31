import classes from 'classnames'

import React, {PropTypes as T} from 'react'
import {utils} from './utils/utils'
import {Feedback} from '../components/feedback-btn.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'

export const ChoiceFeedback = props => {
  return (
    <div className="container choice-paper">
    {props.item.solutions.map(solution =>
      <div
        key={utils.answerId(solution.id)}
        className={classes(
          'item',
          props.item.multiple ? 'checkbox': 'radio',
          utils.getAnswerClassForSolution(solution, props.answer)
        )}>
        <WarningIcon solution={solution} answers={props.answer}/>
        <input
          className={props.item.multiple ? 'checkbox': 'radio'}
          checked={utils.isSolutionChecked(solution, props.answer)}
          id={utils.answerId(solution.id)}
          name={utils.answerId(props.item.id)}
          type={props.item.multiple ? 'checkbox': 'radio'}
          disabled
        />
        <label
          className="control-label"
          htmlFor={utils.answerId(solution.id)}
          dangerouslySetInnerHTML={{__html: utils.getChoiceById(props.item.choices, solution.id).data}}
        />
        {utils.isSolutionChecked(solution, props.answer) &&
          <Feedback
            id={`${solution.id}-feedback`}
            feedback={solution.feedback}
          />
        }
      </div>
    )}
  </div>
)}

ChoiceFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    choices: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    multiple: T.bool.isRequired,
    solutions: T.arrayOf(T.object),
    title: T.string,
    description: T.string
  }).isRequired,
  answer: T.array
}
