import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import {Feedback} from '../components/feedback-btn.jsx'
import {SolutionScore} from '../components/score.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'
import {utils} from './utils/utils'
import {PaperTabs} from '../components/paper-tabs.jsx'

export const ChoicePaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      yours={
        <div className="choice-paper">
          {props.item.solutions.map(solution =>
            <div
              key={utils.answerId(solution.id)}
              className={classes(
                'item',
                props.item.multiple ? 'checkbox': 'radio',
                utils.getAnswerClassForSolution(solution, props.answer)
              )}
            >
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
              <Feedback
                id={`${solution.id}-feedback`}
                feedback={solution.feedback}
              />
              <SolutionScore score={solution.score}/>
            </div>
          )}
        </div>
      }
      expected={
        <div className="choice-paper">
          {props.item.solutions.map(solution =>
            <div
              key={utils.expectedId(solution.id)}
              className={classes(
                'item',
                props.item.multiple ? 'checkbox': 'radio',
                {
                  'bg-info text-info': solution.score > 0
                }
              )}
            >
              <span className="answer-warning-span"></span>
              <input
                className={props.item.multiple ? 'checkbox': 'radio'}
                checked={solution.score > 0}
                id={utils.expectedId(solution.id)}
                name={utils.expectedId(props.item.id)}
                type={props.item.multiple ? 'checkbox': 'radio'}
                disabled
              />
              <label
                className="control-label"
                htmlFor={utils.expectedId(solution.id)}
                dangerouslySetInnerHTML={{__html: utils.getChoiceById(props.item.choices, solution.id).data}}
              />
              <Feedback
                id={`${solution.id}-feedback-expected`}
                feedback={solution.feedback}
              />
              <SolutionScore score={solution.score}/>
            </div>
          )}
        </div>
      }
    />
  )
}

ChoicePaper.propTypes = {
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

ChoicePaper.defaultProps = {
  answer: []
}
