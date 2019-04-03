import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {utils} from '#/plugin/exo/items/words/utils'
import {Feedback} from '#/plugin/exo/items/components/feedback-btn'
import {SolutionScore} from '#/plugin/exo/components/score'

const Highlight = props => {
  return(
    <div>
      {utils.split(props.text, props.solutions).map((el, key) =>
        <span key={key}>
          <span dangerouslySetInnerHTML={{__html: el.text}}/>{'\u00a0'}
          <span className={classes({
            'word-success': el.score > 0,
            'word-danger': el.score <= 0
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
  className: T.string
}

export {
  Highlight
}
