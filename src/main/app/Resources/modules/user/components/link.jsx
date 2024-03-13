import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LinkButton} from '#/main/app/buttons'

import {route} from '#/main/community/user/routing'

const UserLink = (props) => {
  if (!props.user) {
    return (
      <span className={props.className}>{props.children}</span>
    )
  }

  return (
    <LinkButton className={props.className} target={route(props, props.path)}>
      {props.children}
    </LinkButton>
  )
}

UserLink.propTypes = {
  path: T.string,
  user: T.shape({
    username: T.string.isRequired
  }),
  className: T.string
}

export {
  UserLink
}
