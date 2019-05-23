import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {utils} from '#/plugin/exo/items/words/utils'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'

const Highlight = props => {
  return(
    <div>
      {utils.split(props.text, props.solutions, true, props.hasExpectedAnswers).map((el, key) =>
        <span key={key}>
          <span dangerouslySetInnerHTML={{__html: el.text}}/>{'\u00a0'}
          <span className={classes({
            'word-success': props.hasExpectedAnswers && el.score > 0,
            'word-danger': props.hasExpectedAnswers && el.score <= 0
          })}>
            <Feedback feedback={el.feedback} id={key}/>{'\u00a0'}
            {el.score !== null && props.showScore &&
              <SolutionScore score={el.score}/>
            }
          </span>
        </span>
      )}
    </div>
  )
}

Highlight.propTypes = {
  text: T.string.isRequired,
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  className: T.string
}

export {
  Highlight
}
