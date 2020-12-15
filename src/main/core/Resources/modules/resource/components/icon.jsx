import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {icon} from '#/main/app/config'

const ResourceIcon = props =>
  <img className={classes('resource-icon', props.className)} src={icon(props.mimeType)} />

ResourceIcon.propTypes = {
  className: T.string,
  mimeType: T.string.isRequired
}

export {
  ResourceIcon
}
