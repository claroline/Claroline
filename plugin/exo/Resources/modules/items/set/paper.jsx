import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {tex} from '#/main/core/translation'
import {Feedback} from './../components/feedback-btn.jsx'
import {SolutionScore} from './../components/score.jsx'
import {AnswerStats} from '../components/stats.jsx'
import {PaperTabs} from './../components/paper-tabs.jsx'
import {utils} from './utils/utils'
import {WarningIcon} from './utils/warning-icon.jsx'
import has from 'lodash/has'

export const SetPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      yours={
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
                      { props.answer && props.answer.length > 0 && utils.getSetItems(set.id, props.answer).map(answer =>
                        <li key={`your-answer-assocation-${answer.itemId}-${answer.setId}`}>
                          { utils.answerInSolutions(answer, props.item.solutions.associations) ?
                            <div className={classes(
                                'association',
                                {'correct-answer': utils.isValidAnswer(answer, props.item.solutions.associations)},
                                {'incorrect-answer': !utils.isValidAnswer(answer, props.item.solutions.associations)}
                              )}>
                              <WarningIcon valid={utils.isValidAnswer(answer, props.item.solutions.associations)}/>
                              <div className="association-data" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(answer.itemId, props.item.items)}} />
                              <Feedback
                                id={`ass-${answer.itemId}-${answer.setId}-feedback`}
                                feedback={utils.getAnswerSolutionFeedback(answer, props.item.solutions.associations)}
                              />
                              {props.showScore &&
                                <SolutionScore score={utils.getAnswerSolutionScore(answer, props.item.solutions.associations)}/>
                              }
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
                              {props.showScore && utils.getAnswerOddScore(answer, props.item.solutions.odd) !== '' &&
                                <SolutionScore score={utils.getAnswerOddScore(answer, props.item.solutions.odd)}/>
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
      }
      expected={
        <div className="set-paper">
          <div className="items-col">
            <ul>
              { props.item.solutions.odd && props.item.solutions.odd.map((item) =>
                <li key={`expected-${item.itemId}`}>
                  <div className="item">
                    <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(item.itemId, props.item.items)}} />
                    <Feedback
                        id={`odd-${item.itemId}-feedback`}
                        feedback={item.feedback}
                    />
                    {props.showScore &&
                      <SolutionScore score={item.score}/>
                    }
                  </div>
                </li>
              )}
            </ul>
          </div>
          <div className="sets-col">
            <ul>
              {props.item.sets.map((set) =>
                <li key={`expected-set-id-${set.id}`}>
                  <div className="set">
                    <div className="set-heading">
                      <div className="set-heading-content" dangerouslySetInnerHTML={{__html: set.data}} />
                    </div>
                    <div className="set-body">
                      <ul>
                      { utils.getSetItems(set.id, props.item.solutions.associations).map(ass =>
                        <li key={`expected-association-${ass.itemId}-${ass.setId}`}>
                          <div className={classes(
                              'association',
                              {'selected-answer': ass.score > 0}
                            )}>
                            <div className="association-data" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(ass.itemId, props.item.items)}} />
                            <Feedback
                                  id={`ass-${ass.itemId}-${ass.setId}-feedback`}
                                  feedback={ass.feedback}
                              />
                            {props.showScore &&
                              <SolutionScore score={ass.score}/>
                            }
                          </div>
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
      }
      stats={
        <div className="set-stats">
          <div className="set-paper">
            <div className="items-col">
              <ul>
                {props.item.solutions.odd && props.item.solutions.odd.map((item) =>
                  <li key={`stats-expected-${item.itemId}`}>
                    <div className="item selected-answer">
                      <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(item.itemId, props.item.items)}} />

                      <AnswerStats stats={{
                        value: props.stats.unused[item.itemId] ? props.stats.unused[item.itemId] : 0,
                        total: props.stats.total
                      }} />
                    </div>
                  </li>
                )}
                {props.item.items.map((item) =>
                  has(props, ['stats', 'unused', item.id]) && !utils.isItemInOddList(item.id, props.item.solutions) ?
                    <li key={`stats-unexpected-${item.id}`}>
                      <div className="item stats-answer">
                        <div className="item-content" dangerouslySetInnerHTML={{__html: item.data}} />

                        <AnswerStats stats={{
                          value: props.stats.unused[item.id],
                          total: props.stats.total
                        }} />
                      </div>
                    </li> :
                    ''
                )}
              </ul>
            </div>
            <div className="sets-col">
              <ul>
                {props.item.sets.map((set) =>
                  <li key={`stats-expected-set-id-${set.id}`}>
                    <div className="set">
                      <div className="set-heading">
                        <div className="set-heading-content" dangerouslySetInnerHTML={{__html: set.data}} />
                      </div>
                      <div className="set-body">
                        <ul>
                          {utils.getSetItems(set.id, props.item.solutions.associations).map(ass =>
                            <li key={`stats-expected-association-${ass.itemId}-${ass.setId}`}>
                              <div className={classes(
                                  'association',
                                  {'selected-answer': ass.score > 0}
                                )}>
                                <div className="association-data" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(ass.itemId, props.item.items)}} />

                                <AnswerStats stats={{
                                  value: has(props, ['stats', 'sets', set.id, ass.itemId]) ?
                                    props.stats.sets[set.id][ass.itemId] :
                                    0,
                                  total: props.stats.total
                                }} />
                              </div>
                            </li>
                          )}
                          {props.item.items.map((item) => has(props, ['stats', 'sets', set.id, item.id]) &&
                            !utils.isItemInSet(item.id, set.id, props.item.solutions) ?
                              <li key={`stats-unexpected-association-${set.id}-${item.id}`}>
                                <div className="association stats-answer">
                                  <div className="association-data" dangerouslySetInnerHTML={{__html: item.data}} />

                                  <AnswerStats stats={{
                                    value: props.stats.sets[set.id][item.id],
                                    total: props.stats.total
                                  }} />
                                </div>
                              </li> :
                              ''
                          )}
                        </ul>
                      </div>
                    </div>
                  </li>
                )}
              </ul>
            </div>
          </div>
          <div className='answer-item unanswered-item'>
            <div>{tex('unanswered')}</div>

            <AnswerStats stats={{
              value: props.stats.unanswered ? props.stats.unanswered : 0,
              total: props.stats.total
            }} />
          </div>
        </div>
      }
    />
  )
}

SetPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.arrayOf(T.object).isRequired,
    sets: T.arrayOf(T.object).isRequired,
    solutions: T.object
  }).isRequired,
  answer: T.array,
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    sets: T.object,
    unused: T.object,
    unanswered: T.number,
    total: T.number
  })
}

SetPaper.defaultProps = {
  answer: []
}
