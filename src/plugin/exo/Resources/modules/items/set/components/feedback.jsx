import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {utils} from '#/plugin/exo/items/set/utils'
import {ContentHtml} from '#/main/app/content/components/html'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'

const SetFeedback = props =>
  <div className="set-item set-paper row">
    <div className="items-col col-md-5 col-sm-5 col-xs-5">

    </div>

    <div className="sets-col col-md-7 col-sm-7 col-xs-7">
      <ul>
        {props.item.sets.map((set) =>
          <li key={`your-answer-set-id-${set.id}`}>
            <div className="set">
              <ContentHtml className="set-heading">
                {set.data}
              </ContentHtml>

              <ul>
                {props.answer && props.answer.length > 0 && utils.getSetItems(set.id, props.answer).map(answer =>
                  <li key={`your-answer-assocation-${answer.itemId}-${answer.setId}`}>
                    {utils.answerInSolutions(answer, props.item.solutions.associations) ?
                      <div className={classes('association answer-item set-answer-item', props.item.hasExpectedAnswers && {
                        'correct-answer': utils.isValidAnswer(answer, props.item.solutions.associations),
                        'incorrect-answer': !utils.isValidAnswer(answer, props.item.solutions.associations)
                      })}>
                        {props.item.hasExpectedAnswers &&
                          <WarningIcon valid={utils.isValidAnswer(answer, props.item.solutions.associations)}/>
                        }
                        <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(answer.itemId, props.item.items)}} />
                        <Feedback
                          id={`ass-${answer.itemId}-${answer.setId}-feedback`}
                          feedback={utils.getAnswerSolutionFeedback(answer, props.item.solutions.associations)}
                        />
                      </div>
                      :
                      <div className={classes('association answer-item set-answer-item', {'incorrect-answer': props.item.hasExpectedAnswers})}>
                        {props.item.hasExpectedAnswers &&
                          <WarningIcon valid={false}/>
                        }
                        <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(answer.itemId, props.item.items)}} />
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
    solutions: T.object,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.array
}

export {
  SetFeedback
}
