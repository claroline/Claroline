import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'

import {RegistrationUsers} from '#/plugin/cursus/registration/components/users'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {formatField} from '#/main/app/content/form/parameters/utils'

const SessionUsers = (props) => {
  let customDefinition = []
  if (get(props.course, 'registration.form')) {
    get(props.course, 'registration.form').map(formSection => {
      customDefinition = customDefinition.concat(formSection.fields)
    })
  }

  return (
    <RegistrationUsers
      {...props}
      customDefinition={customDefinition
        .map(field => formatField(field, customDefinition, 'data', hasPermission('register', props.session)))
      }
    />
  )
}

SessionUsers.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
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
  SessionUsers
}
