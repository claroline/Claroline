import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LinkButton} from '#/main/app/buttons'

import {route} from '#/main/core/user/routing'

const UserLink = (props) => {
  let displayName
  if (props.showUsername) {
    displayName = props.username
  } else {
    displayName = props.name
  }

  return (
    <LinkButton className={props.className} target={route(props)}>
      {displayName ?
        displayName : trans('unknown')
      }
    </LinkButton>
  )
}

UserLink.propTypes = {
  name: T.string,
  username: T.string,
  className: T.string,
  showUsername: T.bool
}

UserLink.defaultProps = {
  showUsername: false
}

export {
  UserLink
}
