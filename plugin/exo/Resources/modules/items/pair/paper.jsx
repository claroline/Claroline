import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Feedback} from './../components/feedback-btn.jsx'
import {SolutionScore} from './../components/score.jsx'
import {PaperTabs} from './../components/paper-tabs.jsx'
import {utils} from './utils/utils'
import {WarningIcon} from './utils/warning-icon.jsx'

export const PairPaper = props => {
  const yourAnswers = utils.getYourAnswers(props.answer, props.item)
  const expectedAnswers = utils.getExpectedAnswers(props.item)
  return (
      <PaperTabs
        id={props.item.id}
        yours={
          <div className="row pair-paper">
            <div className="col-md-5 items-col">
              <ul>
                {yourAnswers.orpheans.map((item) =>
                  <li key={`your-answer-orphean-${item.id}`}>
                    <div className={classes(
                        'answer-item item',
                        {'correct-answer': item.score},
                        {'incorrect-answer': !item.score}
                      )}>
                      <WarningIcon valid={item.score !== '' && item.score <= 0}/>
                      <div className="item-content" dangerouslySetInnerHTML={{__html: item.data}} />
                    </div>
                  </li>
                )}
              </ul>

            </div>
            <div className="col-md-7 pairs-col">
              <ul>
                {yourAnswers.answers.map((answer) =>
                  <li key={`your-answer-id-${answer.leftItem.id}-${answer.rightItem.id}`}>
                    <div className={classes(
                        'item',
                        {'correct-answer': answer.valid},
                        {'incorrect-answer': !answer.valid}
                      )}>
                      <WarningIcon valid={answer.valid}/>
                      <div className="item-content" dangerouslySetInnerHTML={{__html: answer.leftItem.data}} />
                      <div className="item-content" dangerouslySetInnerHTML={{__html: answer.rightItem.data}} />
                      <Feedback
                        id={`pair-${answer.leftItem.id}-${answer.rightItem.id}-feedback`}
                        feedback={answer.feedback}
                      />
                      {props.showScore && answer.score !== '' &&
                        <SolutionScore score={answer.score}/>
                      }
                    </div>
                  </li>
                )}
              </ul>
            </div>
          </div>
        }
        expected={
          <div className="row pair-paper">
            <div className="col-md-5 items-col">
              <ul>
                {expectedAnswers.odd.map((o) =>
                  <li key={`your-answer-orphean-${o.id}`}>
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
        }
      />
  )
}

PairPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array,
  showScore: T.bool.isRequired
}

PairPaper.defaultProps = {
  answer: []
}
