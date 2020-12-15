import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {constants} from '#/main/app/icon/font/constants'
import {Icon} from '#/main/app/icon/font/components/icon'

const CompositeIcon = props =>
  <span
    role="presentation"
    aria-hidden={true}
    className={classes(constants.ICON_CLASS_PREFIX, `${constants.ICON_CLASS_PREFIX}-composite`, {
      [`${constants.ICON_CLASS_PREFIX}-fw`]: props.fixedWidth
    })}
  >
    <Icon className={`${constants.ICON_CLASS_PREFIX}-composite-primary`} name={props.primary} fixedWidth={props.fixedWidth} />
    <Icon className={`${constants.ICON_CLASS_PREFIX}-composite-secondary`} name={props.secondary} />
  </span>

CompositeIcon.propTypes = {
  primary: T.string.isRequired,
  secondary: T.string.isRequired,
  fixedWidth: T.bool
}

CompositeIcon.defaultProps = {
  fixedWidth: false
}

export {
  CompositeIcon
}
