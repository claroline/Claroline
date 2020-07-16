import {PropTypes as T} from 'prop-types'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {constants} from '#/plugin/cursus/administration/cursus/constants'

const Course = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    parent: T.shape({ // This is a minimal Course
      id: T.string,
      code: T.string,
      name: T.string
    }),
    meta: T.shape({
      workspace: T.shape(WorkspaceTypes.propTypes),
      workspaceModel: T.shape(WorkspaceTypes.propTypes),
      tutorRoleName: T.string,
      learnerRoleName: T.string,
      icon: T.string,
      defaultSessionDuration: T.number,
      withSessionEvent: T.bool,
      order: T.number
    }),
    restrictions: T.shape({
      users: T.number
    }),
    registration: T.shape({
      publicRegistration: T.bool,
      publicUnregistration: T.bool,
      registrationValidation: T.bool,
      userValidation: T.bool,
      organizationValidation: T.bool
    })
  },
  defaultProps: {
    code: '',
    title: '',
    parent: null,
    meta: {
      withSessionEvent: true,
      order: constants.DEFAULT_ORDER
    },
    restrictions: {
      users: null
    },
    registration: {
      publicRegistration: false,
      publicUnregistration: false,
      registrationValidation: false,
      userValidation: false,
      organizationValidation: false
    }
  }
}

export {
  Course
}
