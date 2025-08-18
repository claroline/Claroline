import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {utils} from '#/plugin/exo/items/pair/utils'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'

const PairExpectedAnswer = props => {
  const expectedAnswers = utils.getExpectedAnswers(props.item)

  return (
    <div className="row pair-paper">
      <div className="col-md-5 items-col">
        <ul>
          {expectedAnswers.odd.map((o) =>
            <li key={`your-answer-orphean-${o.item.id}`}>
              <div className={classes(
                'item',
                {'selected-answer': o.score}
              )}>
                <WarningIcon valid={o.score && o.score <= 0}/>
                <div className="item-data" dangerouslySetInnerHTML={{__html: o.item.data}} />
              </div>
            </li>
          )}
        </ul>
      </div>
      <div className="col-md-7 pairs-col">
        <ul>
          {expectedAnswers.answers.map((answer) =>
            <li key={`expected-answer-id-${answer.leftItem.id}-${answer.rightItem.id}`}>
              <div className={classes(
                'item',
                {'selected-answer': answer.valid}
              )}>
                <WarningIcon valid={answer.valid}/>
                <div className="item-data" dangerouslySetInnerHTML={{__html: answer.leftItem.data}} />
                <div className="item-data" dangerouslySetInnerHTML={{__html: answer.rightItem.data}} />
                <Feedback
                  id={`pair-${answer.leftItem.id}-${answer.rightItem.id}-feedback`}
                  feedback={answer.feedback}
                />
                {props.showScore &&
                  <SolutionScore score={answer.score}/>
                }
              </div>
            </li>
          )}
        </ul>
      </div>
    </div>
  )
}

PairExpectedAnswer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  showScore: T.bool.isRequired
}

export {
  PairExpectedAnswer
}
