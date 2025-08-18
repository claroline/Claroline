import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {SolutionScore} from '#/plugin/exo/components/score'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {utils} from '#/plugin/exo/items/set/utils'
import {Html} from '#/main/app/components/html'

const SetExpectedAnswer = props =>
  <div className="set-item set-paper row">
    <div className="items-col col-md-5 col-sm-5 col-xs-5">
      <ul>
        {props.item.solutions.odd && props.item.solutions.odd.map((item) =>
          <li key={`expected-${item.itemId}`}>
            <div className="answer-item set-answer-item">
              <Html className="item-content">
                {utils.getSolutionItemData(item.itemId, props.item.items)}
              </Html>

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

    <div className="sets-col col-md-7 col-sm-7 col-xs-7">
      <ul>
        {props.item.sets.map((set) =>
          <li key={`expected-set-id-${set.id}`}>
            <div className="set">
              <Html className="set-heading h5 mb-0">
                {set.data}
              </Html>

              <ul>
                { utils.getSetItems(set.id, props.item.solutions.associations).map(ass =>
                  <li key={`expected-association-${ass.itemId}-${ass.setId}`}>
                    <div className={classes('association answer-item set-answer-item', {
                      'selected-answer': ass.score > 0
                    })}>
                      <Html className="item-content">
                        {utils.getSolutionItemData(ass.itemId, props.item.items)}
                      </Html>

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
          </li>
        )}
      </ul>
    </div>
  </div>

SetExpectedAnswer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.arrayOf(T.object).isRequired,
    sets: T.arrayOf(T.object).isRequired,
    solutions: T.object,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  showScore: T.bool.isRequired
}

export {
  SetExpectedAnswer
}
