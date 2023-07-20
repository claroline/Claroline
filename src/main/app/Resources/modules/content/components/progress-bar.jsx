import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

const ProgressBar = props =>
  <div
    {...omit(props, 'value', 'size', 'type')}
    className={classes('progress',
      props.className,
      props.size && `progress-${props.size}`
    )}
  >
    <div
      className={classes('progress-bar',
        props.type && `progress-bar-${'user' === props.type ? 'secondary' : props.type}`
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
  size: T.oneOf(['xs', 'sm']),
  type: T.oneOf(['success', 'info', 'warning', 'danger', 'primary', 'secondary'])
}

ProgressBar.defaultProps = {
  value: 0
}

export {
  ProgressBar
}
