import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const ProgressBar = props =>
  <div
    {...omit(props, 'value', 'size', 'type')}
    role="progressbar"
    aria-valuenow={props.value}
    aria-valuemin={0}
    aria-valuemax={100}
    className={classes('progress',
      props.className,
      props.size && `progress-${props.size}`
    )}
  >
    <div
      className={classes('progress-bar',
        props.type && `bg-${'user' === props.type ? 'secondary' : props.type}`
      )}
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
  type: T.oneOf(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'learning'])
}

ProgressBar.defaultProps = {
  value: 0
}

export {
  ProgressBar
}
