import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const FlashcardProgressBar = props =>
  <div
    {...omit(props, 'value', 'size', 'type')}
    role="progressbar"
    aria-valuenow={props.value}
    aria-valuemin={0}
    aria-valuemax={100}
    className={classes('flashcard-progress',
      props.className,
      props.size && `flashcard-progress-${props.size}`
    )}
  >
    <div
      className={classes('flashcard-progress-bar',
        props.type && `bg-${'user' === props.type ? 'secondary' : props.type}`
      )}
      style={{
        width: props.value+'%'
      }}
    >
      <span className="sr-only">{props.value}</span>
    </div>
  </div>


FlashcardProgressBar.propTypes = {
  className: T.string,
  value: T.number,
  size: T.oneOf(['sm']),
  type: T.oneOf(['learning'])
}

FlashcardProgressBar.defaultProps = {
  value: 0
}

export {
  FlashcardProgressBar
}
