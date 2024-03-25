import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {ProgressBar as BaseProgressBar} from 'react-bootstrap'
import {precision} from '#/main/app/intl/number'

const ProgressBar = forwardRef((props, ref) =>
  <BaseProgressBar
    {...omit(props, 'value', 'size', 'type', 'showLabel')}
    ref={ref}
    now={props.value}
    variant={props.type}
    className={classes(props.className, props.size && `progress-${props.size}`)}
    label={`${precision(props.value, 1)}%`}
    visuallyHidden={!props.showLabel}
  />
)

ProgressBar.propTypes = {
  className: T.string,
  value: T.number,
  size: T.oneOf(['xs']),
  type: T.oneOf(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'learning']),
  showLabel: T.bool
}

ProgressBar.defaultProps = {
  value: 0,
  showLabel: false
}

export {
  ProgressBar
}
