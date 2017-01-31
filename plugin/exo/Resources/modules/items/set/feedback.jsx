import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import {utils} from './utils/utils'
import {Feedback} from '../components/feedback-btn.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'

export const SetFeedback = props =>
  <div className="set-paper">
    <div className="items-col">

    </div>
    <div className="sets-col">
      <ul>
        {props.item.sets.map((set) =>
          <li key={`your-answer-set-id-${set.id}`}>
            <div className="set">
              <div className="set-heading">
                <div className="set-heading-content" dangerouslySetInnerHTML={{__html: set.data}} />
              </div>
              <div className="set-body">
                <ul>
                { utils.getSetItems(set.id, props.answer).map(answer =>
                  <li key={`your-answer-assocation-${answer.itemId}-${answer.setId}`}>
                    { utils.answerInSolutions(answer, props.item.solutions.associations) ?
                      <div className={classes(
                          'association',
                          {'bg-success text-success': utils.isValidAnswer(answer, props.item.solutions.associations)},
                          {'bg-danger text-danger': !utils.isValidAnswer(answer, props.item.solutions.associations)}
                        )}>
                        <WarningIcon valid={utils.isValidAnswer(answer, props.item.solutions.associations)}/>
                        <div className="association-data" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(answer.itemId, props.item.items)}} />
                        <Feedback
                              id={`ass-${answer.itemId}-${answer.setId}-feedback`}
                              feedback={utils.getAnswerSolutionFeedback(answer, props.item.solutions.associations)}
                          />
                      </div>
                      :
                      <div className="association bg-danger text-danger">
                        <WarningIcon valid={false}/>
                        <div className="association-data" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(answer.itemId, props.item.items)}} />
                        {utils.getAnswerOddFeedback(answer, props.item.solutions.odd) !== '' &&
                          <Feedback
                            id={`ass-${answer.itemId}-${answer.setId}-feedback`}
                            feedback={utils.getAnswerOddFeedback(answer, props.item.solutions.odd)}
                          />
                        }
                      </div>
                    }
                  </li>
                )}
                </ul>
              </div>
            </div>
          </li>
        )}
      </ul>
    </div>
  </div>


SetFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    sets: T.arrayOf(T.object).isRequired,
    items: T.array.isRequired,
    solutions: T.object
  }).isRequired,
  answer: T.array
}
