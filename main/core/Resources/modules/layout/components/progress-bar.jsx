import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ProgressBar = props =>
  <div className={classes('progress',
    props.className,
    props.size && `progress-${props.size}`
  )}>
    <div
      className={classes('progress-bar',
        props.type && `progress-bar-${props.type}`
      )}
      role="progressbar"
      aria-valuenow={props.value}
      aria-valuemin={0}
      aria-valuemax={100}
      style={{
        width: props.value+'%'
      }}
    >
      <span className="sr-only">{props.value}</span>
    </div>
  </div>

ProgressBar.propTypes = {
  className: T.string,
  value: T.number,
  size: T.oneOf(['xs']),
  type: T.oneOf(['success', 'info', 'warning', 'danger', 'user'])
}

ProgressBar.defaultProps = {
  value: 0
}

export {
  ProgressBar
}
