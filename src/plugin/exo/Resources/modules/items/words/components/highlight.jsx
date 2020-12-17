import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {utils} from '#/plugin/exo/items/words/utils'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'

const Highlight = props =>
  <div className={props.className}>
    {utils.split(props.text, props.contentType, props.solutions, true, props.hasExpectedAnswers).map((el, key) =>
      <span key={key} className={classes({
        'correct-answer': props.hasExpectedAnswers && el.score > 0,
        'incorrect-answer': props.hasExpectedAnswers && el.score <= 0,
        'selected-answer': !props.hasExpectedAnswers
      })}>
        <span dangerouslySetInnerHTML={{__html: el.text}} />

        <Feedback feedback={el.feedback} id={key}/>{'\u00a0'}
        {el.score !== null && props.showScore &&
          <SolutionScore score={el.score}/>
        }
      </span>
    )}
  </div>

Highlight.propTypes = {
  text: T.string.isRequired,
  contentType: T.string.isRequired,
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  className: T.string
}

export {
  Highlight
}
