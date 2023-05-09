import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Alert} from '#/main/app/components/alert'

const ContentRestriction = props => {
  let title, help
  if (props.failed) {
    title = props.fail.title
    help = props.fail.help
  } else {
    title = props.success.title
    help = props.success.help
  }

  return (
    <Alert
      type={classes({
        'success': !props.failed,
        'warning': props.failed && props.onlyWarn,
        'danger': props.failed && !props.onlyWarn
      })}
      icon={props.icon}
      title={title}
    >
      {help}

      {props.failed && props.children}
    </Alert>
  )
}

ContentRestriction.propTypes = {
  icon: T.string.isRequired,
  success: T.shape({
    title: T.string.isRequired,
    help: T.string
  }).isRequired,
  fail: T.shape({
    title: T.string.isRequired,
    help: T.string
  }).isRequired,
  failed: T.bool.isRequired,
  onlyWarn: T.bool, // we only warn for restrictions that can be fixed
  children: T.node
}

ContentRestriction.defaultProps = {
  validated: false,
  onlyWarn: false
}

export {
  ContentRestriction
}
