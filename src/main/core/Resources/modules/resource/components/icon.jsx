import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ThemeIcon} from '#/main/theme/components/icon'

const ResourceIcon = props =>
  <ThemeIcon
    className={classes('resource-icon', props.className)}
    mimeType={props.mimeType}
    set="resources"
  />

ResourceIcon.propTypes = {
  className: T.string,
  mimeType: T.string.isRequired
}

export {
  ResourceIcon
}
