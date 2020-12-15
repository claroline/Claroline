import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {constants} from '#/main/app/icon/font/constants'

const Icon = props =>
  <span
    role="presentation"
    aria-hidden={true}
    className={classes(constants.ICON_CLASS_PREFIX, `${constants.ICON_CLASS_PREFIX}-${props.name}`, {
      [`${constants.ICON_CLASS_PREFIX}-fw`]: props.fixedWidth
    }, props.className)}
  />

Icon.propTypes = {
  name: T.string.isRequired,
  className: T.string,
  fixedWidth: T.bool
}

Icon.defaultProps = {
  fixedWidth: false
}

export {
  Icon
}
