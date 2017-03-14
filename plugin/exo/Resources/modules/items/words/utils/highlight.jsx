import React, {PropTypes as T} from 'react'
import classes from 'classnames'

import {utils} from './utils'
import {Feedback} from '../../components/feedback-btn.jsx'
import {SolutionScore} from '../../components/score.jsx'

export const Highlight = props =>
  <div className={props.className}>
    {utils.split(props.text, props.solutions).map((el, key) =>
      <span key={key} className={classes('keyword', {
        'correct-answer': el.score > 0,
        'incorrect-answer': el.score < 1
      })}>
        <span dangerouslySetInnerHTML={{__html: el.text}}></span>
        <Feedback feedback={el.feedback} id={key} />
        {el.score !== null && props.showScore &&
          <SolutionScore score={el.score} />
        }
      </span>
    )}
  </div>

Highlight.propTypes = {
  text: T.string.isRequired,
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired,
  className: T.string
}
