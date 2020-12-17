import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ContentHtml} from '#/main/app/content/components/html'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'

import {utils} from '#/plugin/exo/items/ordering/utils'
import {constants} from '#/plugin/exo/items/ordering/constants'

const OrderingFeedback = props =>  {
  return (
    <div className="ordering-paper">
      <div className="row">
        <div
          className={classes({
            'horizontal': props.item.direction === constants.DIRECTION_HORIZONTAL,
            'col-md-12': props.item.mode === constants.MODE_INSIDE,
            'col-md-6': props.item.direction === constants.DIRECTION_VERTICAL && props.item.mode === constants.MODE_BESIDE
          })}
        >
          {props.item.mode === constants.MODE_INSIDE ?
            props.answer.map((a) =>
              <div key={a.itemId} className={classes(
                'item',
                props.item.hasExpectedAnswers ?
                  utils.answerIsValid(a, props.item.solutions) ?
                    'text-success positive-score' :
                    'text-danger negative-score' :
                  'no-score'
              )}>
                {props.item.hasExpectedAnswers &&
                  <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                }
                <ContentHtml className="item-data">
                  {props.item.items.find(item => item.id === a.itemId).data}
                </ContentHtml>

                <Feedback
                  id={`oredering-answer-${a.itemId}-feedback`}
                  feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                />
              </div>
            )
            :
            props.item.solutions.filter(solution => undefined === props.answer.find(answer => answer.itemId === solution.itemId)).map((solution) =>
              <div key={solution.itemId} className={classes(
                'item',
                props.item.hasExpectedAnswers ?
                  solution.score > 0 ? 'text-danger negative-score' : 'text-success positive-score' :
                  'no-score'
              )}>
                {props.item.hasExpectedAnswers &&
                  <WarningIcon valid={solution.score < 1}/>
                }
                <ContentHtml className="item-data">
                  {props.item.items.find(item => item.id === solution.itemId).data}
                </ContentHtml>
                {solution.score > 0 &&
                  <Feedback
                    id={`oredering-solution-${solution.itemId}-feedback`}
                    feedback={solution.feedback}
                  />
                }
              </div>
            )
          }
        </div>
        {props.item.direction === constants.DIRECTION_VERTICAL && props.item.mode === constants.MODE_BESIDE &&
          <div className="col-md-6 answer-zone">
            {props.answer.map((a) =>
              <div key={a.itemId} className={classes(
                'item',
                props.item.hasExpectedAnswers ?
                  utils.answerIsValid(a, props.item.solutions) ?
                    'text-success positive-score' :
                    'text-danger negative-score' :
                  'no-score'
              )}>
                {props.item.hasExpectedAnswers &&
                  <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                }
                <ContentHtml className="item-data">
                  {props.item.items.find(item => item.id === a.itemId).data}
                </ContentHtml>
                <Feedback
                  id={`oredering-answer-${a.itemId}-feedback`}
                  feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                />
              </div>
            )}
          </div>
        }
      </div>
      {props.item.direction === constants.DIRECTION_HORIZONTAL && props.item.mode === constants.MODE_BESIDE &&
        <div className="row">
          <div className="col-md-12 answer-zone horizontal">
            {props.answer.map((a) =>
              <div key={a.itemId} className={classes(
                'item',
                props.item.hasExpectedAnswers ?
                  utils.answerIsValid(a, props.item.solutions) ?
                    'text-success positive-score' :
                    'text-danger negative-score' :
                  'no-score'
              )}>
                {props.item.hasExpectedAnswers &&
                  <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                }
                <ContentHtml className="item-data">
                  {props.item.items.find(item => item.id === a.itemId).data}
                </ContentHtml>
                <Feedback
                  id={`oredering-answer-${a.itemId}-feedback`}
                  feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                />
              </div>
            )}
          </div>
        </div>
      }
    </div>
  )
}

OrderingFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    mode: T.string.isRequired,
    direction: T.string.isRequired,
    score: T.object.isRequired,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.array.isRequired
}

OrderingFeedback.defaultProps = {
  answer: []
}

export {
  OrderingFeedback
}
