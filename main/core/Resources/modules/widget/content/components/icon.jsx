import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {SvgIcon} from '#/main/app/icon/svg/components/icon'

const WidgetSourceIcon = props =>
  <SvgIcon
    className={classes('widget-icon', props.className)}
    path="bundles/clarolinecore/images/data-icons"
    name={props.type}
  />

WidgetSourceIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired
}

const WidgetContentIcon = props =>
  <SvgIcon
    className={classes('widget-icon', props.className)}
    path="bundles/clarolinecore/images/widget-icons"
    name={props.type}
  />

WidgetContentIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired
}

export {
  WidgetSourceIcon,
  WidgetContentIcon
}
