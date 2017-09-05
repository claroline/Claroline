import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'
import {tex} from '#/main/core/translation'
import {Feedback} from '../components/feedback-btn.jsx'
import {SolutionScore} from '../components/score.jsx'
import {AnswerStats} from '../components/stats.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'
import {utils} from './utils/utils'
import {PaperTabs} from '../components/paper-tabs.jsx'
import {SCORE_SUM} from './../../quiz/enums'
import {MODE_INSIDE, MODE_BESIDE, DIRECTION_HORIZONTAL, DIRECTION_VERTICAL} from './editor'

const OrderingPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      yours={
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
                      utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type)
                    )}>
                    <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === a.itemId).data}}/>
                    <Feedback
                      id={`oredering-answer-${a.itemId}-feedback`}
                      feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                    />
                  {props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions) &&
                      <SolutionScore score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}/>
                    }
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
                    {props.showScore && solution.score > 0 &&
                      <SolutionScore score={solution.score}/>
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
                      utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type)
                    )}>
                    <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === a.itemId).data}}/>
                    <Feedback
                      id={`oredering-answer-${a.itemId}-feedback`}
                      feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                    />
                    {props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions) &&
                      <SolutionScore score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}/>
                    }
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
                      utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type)
                    )}>
                    <WarningIcon valid={utils.answerIsValid(a, props.item.solutions)}/>
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === a.itemId).data}}/>
                    <Feedback
                      id={`oredering-answer-${a.itemId}-feedback`}
                      feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                    />
                    {props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions) &&
                      <SolutionScore score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}/>
                    }
                  </div>
                )}
              </div>
            </div>
          }
        </div>
      }
      expected={
        <div className="ordering-paper">
          <div className="row">
            <div className={classes(
                {'horizontal': props.item.direction === DIRECTION_HORIZONTAL},
                {'col-md-12': props.item.mode === MODE_INSIDE},
                {'col-md-6': props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE}
              )}>
              {props.item.mode === MODE_INSIDE ?
                props.item.solutions.map((solution) =>
                  <div key={solution.itemId} className="item text-info bg-info">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    <Feedback
                      id={`oredering-answer-${solution.itemId}-feedback`}
                      feedback={solution.feedback}
                    />
                    {props.showScore && props.item.score.type === SCORE_SUM &&
                      <SolutionScore score={solution.score}/>
                    }
                  </div>
                )
                :
                props.item.solutions.filter(solution => solution.score < 1).map((solution) =>
                  <div key={solution.itemId} className="item item-bg">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    <Feedback
                      id={`oredering-solution-${solution.itemId}-feedback`}
                      feedback={solution.feedback}
                    />
                    {props.showScore && props.item.score.type === SCORE_SUM &&
                      <SolutionScore score={solution.score}/>
                    }
                  </div>
                )
              }
            </div>
            {props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE &&
              <div className="col-md-6 answer-zone">
                {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                  <div key={solution.itemId} className="item text-info bg-info">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    <Feedback
                      id={`oredering-answer-${solution.itemId}-feedback`}
                      feedback={solution.feedback}
                    />
                    {props.showScore && props.item.score.type === SCORE_SUM &&
                      <SolutionScore score={solution.score}/>
                    }
                  </div>
                )}
              </div>
            }
          </div>
          {props.item.direction === DIRECTION_HORIZONTAL && props.item.mode === MODE_BESIDE &&
            <div className="row">
              <div className="col-md-12 answer-zone horizontal">
                {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                  <div key={solution.itemId} className="item text-info bg-info">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    <Feedback
                      id={`oredering-answer-${solution.itemId}-feedback`}
                      feedback={solution.feedback}
                    />
                    {props.showScore && props.item.score.type === SCORE_SUM &&
                      <SolutionScore score={solution.score}/>
                    }
                  </div>
                )}
              </div>
            </div>
          }
        </div>
      }
      stats={
        <div className="ordering-paper">
          <div className="row">
            <div className={classes(
                {'horizontal': props.item.direction === DIRECTION_HORIZONTAL && props.item.mode === MODE_BESIDE},
                {'col-md-6': props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE}
              )}>
              {props.item.mode === MODE_INSIDE ?
                <div className={classes('col-md-12 answer-zone',
                  {'horizontal': props.item.direction === DIRECTION_HORIZONTAL}
                )}>
                  {props.item.solutions.map((solution) =>
                    <div key={solution.itemId} className="item selected-answer text-info bg-info">
                      <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    </div>
                  )}
                  <div className="item stats-item stats-success">
                    <AnswerStats stats={{
                      value: has(props.stats, ['orders', utils.getKey(props.item.solutions.filter(solution => solution.score > 0))]) ?
                        props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))].count :
                        0,
                      total: props.stats.total
                    }}/>
                  </div>
                </div>
                :
                props.item.solutions.filter(solution => solution.score < 1).map((solution) =>
                  <div key={solution.itemId} className="item selected-answer">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>

                    <AnswerStats stats={{
                      value: props.stats.unused[solution.itemId] ? props.stats.unused[solution.itemId] : 0,
                      total: props.stats.total
                    }}/>
                  </div>
                )
              }
              {props.item.mode === MODE_INSIDE && props.stats.orders &&
                Object.values(props.stats.orders).map((o) => {
                  const data = o.data.slice()
                  const key = utils.getKey(data)

                  if (props.stats.orders[key] && !utils.isInSolutions(key, props.item.solutions)) {
                    return (
                      <div key={`stats-unexpected-${key}`} className={classes('col-md-12 answer-zone',
                        {'horizontal': props.item.direction === DIRECTION_HORIZONTAL}
                      )}>
                        {props.stats.orders[key].data.map((d) =>
                          <div key={d.itemId} className="item item-bg">
                            <div className="item-data" dangerouslySetInnerHTML={{__html: d._data}}/>
                          </div>
                        )}
                        <div className="item stats-item">
                          <AnswerStats stats={{
                            value: props.stats.orders[key].count,
                            total: props.stats.total
                          }}/>
                        </div>
                      </div>
                    )
                  }
                })
              }
              {props.item.mode === MODE_BESIDE &&
                props.item.items.filter(i => props.stats.unused[i.id] && !utils.isInOddsSolutions(i.id, props.item.solutions)).map((i) =>
                  <div key={`stats-unused-${i.id}`} className="item item-bg">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: i.data}}/>

                    <AnswerStats stats={{
                      value: props.stats.unused[i.id],
                      total: props.stats.total
                    }}/>
                  </div>
                )
              }
            </div>
            {props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE &&
              <div className="col-md-6">
                <div className="answer-zone">
                  {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                    <div key={solution.itemId} className="item text-info bg-info">
                      <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    </div>
                  )}
                  <div className="item stats-item stats-success">
                    <AnswerStats stats={{
                      value: props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))] ?
                        props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))].count :
                        0,
                      total: props.stats.total
                    }}/>
                  </div>
                </div>
                {props.item.direction === DIRECTION_VERTICAL && props.item.mode === MODE_BESIDE &&
                  Object.values(props.stats.orders).map((o) => {
                    const data = o.data.slice()
                    const key = utils.getKey(data)

                    if (props.stats.orders[key] && !utils.isInSolutions(key, props.item.solutions)) {
                      return (
                        <div key={`stats-unexpected-${key}`} className="answer-zone">
                          {props.stats.orders[key].data.map((d) =>
                            <div key={d.itemId} className="item item-bg">
                              <div className="item-data" dangerouslySetInnerHTML={{__html: d._data}}/>
                            </div>
                          )}

                          <div className="item stats-item">
                            <AnswerStats stats={{
                              value: props.stats.orders[key].count,
                              total: props.stats.total
                            }}/>
                          </div>
                        </div>
                      )
                    }
                  })
                }
              </div>
            }
          </div>
          {props.item.direction === DIRECTION_HORIZONTAL && props.item.mode === MODE_BESIDE &&
            <div className="row">
              <div className="col-md-12 answer-zone horizontal">
                {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                  <div key={solution.itemId} className="item text-info bg-info">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                  </div>
                )}
                <div className="item stats-item stats-success">
                  <AnswerStats stats={{
                    value: props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))] ?
                      props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))].count :
                      0,
                    total: props.stats.total
                  }}/>
                </div>
              </div>
            </div>
          }
          {props.item.direction === DIRECTION_HORIZONTAL && props.item.mode === MODE_BESIDE &&
            Object.values(props.stats.orders).map((o) => {
              const data = o.data.slice()
              const key = utils.getKey(data)

              if (has(props.stats.orders, [key]) && !utils.isInSolutions(key, props.item.solutions)) {
                return (
                  <div key={`stats-unexpected-${key}`} className="row">
                    <div className="col-md-12 answer-zone horizontal">
                      {props.stats.orders[key].data.map((d) =>
                        <div key={d.itemId} className="item item-bg">
                          <div className="item-data" dangerouslySetInnerHTML={{__html: d._data}}/>
                        </div>
                      )}

                      <div className="item stats-item">
                        <AnswerStats stats={{
                          value: props.stats.orders[key].count,
                          total: props.stats.total
                        }}/>
                      </div>
                    </div>
                  </div>
                )
              }
            })
          }
          <div className='answer-item unanswered-item'>
            <div>{tex('unanswered')}</div>

            <AnswerStats stats={{
              value: props.stats.unanswered ? props.stats.unanswered : 0,
              total: props.stats.total
            }}/>
          </div>
        </div>
      }
    />
  )
}

OrderingPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    mode: T.string.isRequired,
    direction: T.string.isRequired,
    score: T.object.isRequired,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array.isRequired,
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    orders: T.object,
    unused: T.object,
    unanswered: T.number,
    total: T.number
  })
}

OrderingPaper.defaultProps = {
  answer: []
}

export {OrderingPaper}
