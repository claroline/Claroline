import React, {PropTypes as T} from 'react'
import {utils} from './utils'
import {Feedback} from '../../components/feedback-btn.jsx'
import {SolutionScore} from '../../components/score.jsx'
import classes from 'classnames'

export const Highlight = props => {
  return(
    <div>
      {utils.split(props.text, props.solutions).map((el, key) =>
        <span key={key} className={classes({
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
  )
}

Highlight.propTypes = {
  text: T.string.isRequired,
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired
}
