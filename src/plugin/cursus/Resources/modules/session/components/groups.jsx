import React from 'react'
import {PropTypes as T} from 'prop-types'

import {RegistrationGroups} from '#/plugin/cursus/registration/components/groups'
import {Session as SessionTypes} from '#/plugin/cursus/prop-types'

const SessionGroups = (props) =>
  <RegistrationGroups {...props} />

SessionGroups.propTypes = {
  session: T.shape(
    SessionTypes.propTypes
  ).isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  unregisterUrl: T.oneOfType([T.string, T.array]).isRequired,
  actions: T.func,
  add: T.shape({
    // action types
  })
}

export {
  SessionGroups
}
