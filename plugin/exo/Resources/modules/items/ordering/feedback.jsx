import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {Feedback} from '../components/feedback-btn.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'
import {utils} from './utils/utils'
import {MODE_INSIDE, MODE_BESIDE, DIRECTION_HORIZONTAL, DIRECTION_VERTICAL} from './editor'

const OrderingFeedback = props =>  {
  return (
    <div className="ordering-paper">
      <div className="row">
        <div className={classes(
            {'horizontal': props.item.direction === DIRECTION_HORIZONTAL},
            {'col-md-12': props.item.mode === MODE_INSIDE},
            {'col-md-6': props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE}
          )}>
          {props.item.mode === MODE_INSIDE ?
            props.answer.map((a) =>
              <div key={a.itemId} className={classes(
                  'item',
                  utils.answerIsValid(a, props.item.solutions) ? 'text-success positive-score' : 'text-danger negative-score'
                )}>
                <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === a.itemId).data}}/>
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
                  solution.score > 0 ? 'text-danger negative-score' : 'text-success positive-score'
                )}>
                <WarningIcon valid={solution.score < 1}/>
                <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
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
        {props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE &&
          <div className="col-md-6 answer-zone">
            {props.answer.map((a) =>
              <div key={a.itemId} className={classes(
                  'item',
                  utils.answerIsValid(a, props.item.solutions) ? 'text-success positive-score' : 'text-danger negative-score'
                )}>
                <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === a.itemId).data}}/>
                <Feedback
                  id={`oredering-answer-${a.itemId}-feedback`}
                  feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                />
              </div>
            )}
          </div>
        }
      </div>
      {props.item.direction === DIRECTION_HORIZONTAL && props.item.mode === MODE_BESIDE &&
        <div className="row">
          <div className="col-md-12 answer-zone horizontal">
            {props.answer.map((a) =>
              <div key={a.itemId} className={classes(
                  'item',
                  utils.answerIsValid(a, props.item.solutions) ? 'text-success positive-score' : 'text-danger negative-score'
                )}>
                <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === a.itemId).data}}/>
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
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array.isRequired
}

OrderingFeedback.defaultProps = {
  answer: []
}

export {OrderingFeedback}
