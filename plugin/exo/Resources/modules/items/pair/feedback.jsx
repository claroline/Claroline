import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {utils} from './utils/utils'
import {Feedback} from '../components/feedback-btn.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'

export const PairFeedback = (props) => {
  const yourAnswers = utils.getYourAnswers(props.answer, props.item)
  return (
    <div className="pair-feedback row">
      <div className="col-md-5 col-sm-5 items-col">
        <ul>
          {yourAnswers.orpheans.map((item) =>
            <li key={`your-answer-orphean-${item.id}`}>
              <div className={classes(
                  'answer-item item',
                  {'incorrect-answer': !item.score && 0 !== item.score},
                  {'correct-answer': item.score || item.score === 0}
                )}>
                <WarningIcon valid={item.score || item.score === 0} />
                <div className="item-content" dangerouslySetInnerHTML={{__html: item.data}} />
              </div>
            </li>
          )}
        </ul>
      </div>

      <div className="col-md-7 col-sm-7 pairs-col">
        <ul>
          {yourAnswers.answers.map((answer) =>
            <li key={`your-answer-id-${answer.leftItem.id}-${answer.rightItem.id}`}>
              <div className={classes(
                  'item',
                  {'correct-answer': answer.valid},
                  {'incorrect-answer': !answer.valid}
                )}
              >
                <WarningIcon valid={answer.valid} />

                <div className="item-content" dangerouslySetInnerHTML={{__html: answer.leftItem.data}} />
                <div className="item-content" dangerouslySetInnerHTML={{__html: answer.rightItem.data}} />

                <Feedback
                  id={`pair-${answer.leftItem.id}-${answer.rightItem.id}-feedback`}
                  feedback={answer.feedback}
                />
              </div>
            </li>
          )}
        </ul>
      </div>
    </div>
  )
}

PairFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.array.isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.array
}
