import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import {Feedback} from './../components/feedback-btn.jsx'
import {SolutionScore} from './../components/score.jsx'
import {PaperTabs} from './../components/paper-tabs.jsx'
import {utils} from './utils/utils'
import {WarningIcon} from './utils/warning-icon.jsx'

export const SetPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
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
                              <SolutionScore score={utils.getAnswerSolutionScore(answer, props.item.solutions.associations)}/>
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
                              {utils.getAnswerOddScore(answer, props.item.solutions.odd) !== '' &&
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
                <li key={`expected-${item.id}`}>
                  <div className="item">
                    <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(item.itemId, props.item.items)}} />
                    <Feedback
                        id={`odd-${item.itemId}-feedback`}
                        feedback={item.feedback}
                    />
                    <SolutionScore score={item.score}/>
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
                              {'bg-info text-info': ass.score > 0}
                            )}>
                            <div className="association-data" dangerouslySetInnerHTML={{__html: utils.getSolutionItemData(ass.itemId, props.item.items)}} />
                            <Feedback
                                  id={`ass-${ass.itemId}-${ass.setId}-feedback`}
                                  feedback={ass.feedback}
                              />
                            <SolutionScore score={ass.score}/>
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
  answer: T.array
}

SetPaper.defaultProps = {
  answer: []
}
