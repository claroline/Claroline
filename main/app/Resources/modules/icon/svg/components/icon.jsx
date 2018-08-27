import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/core/scaffolding/asset'

const SvgIcon = props =>
  <svg className={classes('svg-icon', props.className)}>
    <use xlinkHref={`${asset(props.path)}.svg#${props.name}`} />
  </svg>

SvgIcon.propTypes = {
  className: T.string,
  path: T.string.isRequired,
  name: T.string.isRequired
}

export {
  SvgIcon
}
