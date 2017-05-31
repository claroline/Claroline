import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Feedback} from '../components/feedback-btn.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'
import {utils} from './utils/utils'

export const BooleanFeedback = props => {
  return (
    <div className="boolean-feedback row">
      {props.item.solutions.map(solution =>
        <div key={solution.id} className="col-md-6">
          <div className={classes(
              'answer-item choice-item',
              utils.getAnswerClass(solution, props.answer)
            )}>
            {solution.id === props.answer &&
              <WarningIcon valid={solution.score > 0}/>
            }

            <div dangerouslySetInnerHTML={{__html: props.item.choices.find(choice => choice.id === solution.id).data}}/>

            {solution.id === props.answer &&
              <Feedback
                id={`${solution.id}-feedback`}
                feedback={solution.feedback}/>
            }
          </div>
        </div>
      )}
    </div>
  )
}

BooleanFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    choices: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.string
}
