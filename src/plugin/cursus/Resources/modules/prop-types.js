import {PropTypes as T} from 'prop-types'

import {Role as RoleTypes} from '#/main/community/prop-types'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {constants} from '#/plugin/cursus/constants'

const Course = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    plainDescription: T.string,
    parent: T.shape({ // This is a minimal Course
      id: T.string,
      code: T.string,
      name: T.string
    }),
    meta: T.shape({
      workspace: T.shape(WorkspaceTypes.propTypes),
      workspaceModel: T.shape(WorkspaceTypes.propTypes),
      duration: T.number
    }),
    display: T.shape({
      order: T.number,
      hideSessions: T.bool
    }),
    restrictions: T.shape({
      active: T.bool,
      users: T.number
    }),
    registration: T.shape({
      selfRegistration: T.bool,
      autoRegistration: T.bool,
      selfUnregistration: T.bool,
      validation: T.bool,
      mail: T.bool,
      userValidation: T.bool,
      tutorRole: T.shape(
        RoleTypes.propTypes
      ),
      learnerRole: T.shape(
        RoleTypes.propTypes
      ),
      // custom registration form using FieldFacets
      form: T.array
    })
  },
  defaultProps: {
    meta: {
      duration: 1
    },
    display: {
      order: 1,
      hideSessions: false
    },
    restrictions: {
      users: null
    },
    registration: {
      selfRegistration: false,
      autoRegistration: false,
      selfUnregistration: false,
      validation: false,
      mail: false,
      userValidation: false
    }
  }
}

const Session = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    plainDescription: T.string,
    course: T.shape(
      Course.propTypes
    ),
    workspace: T.shape(
      WorkspaceTypes.propTypes
    ),
    meta: T.shape({
      default: T.bool
    }),
    display: T.shape({
      order: T.number
    }),
    restrictions: T.shape({
      users: T.number,
      dates: T.arrayOf(T.string)
    }),
    participants: T.shape({
      tutors: T.number,
      learners: T.number,
      pending: T.number
    }),
    registration: T.shape({
      selfRegistration: T.bool,
      autoRegistration: T.bool,
      selfUnregistration: T.bool,
      validation: T.bool,
      mail: T.bool,
      userValidation: T.bool,
      eventRegistrationType: T.number,
      tutorRole: T.shape(
        RoleTypes.propTypes
      ),
      learnerRole: T.shape(
        RoleTypes.propTypes
      )
    })
  },
  defaultProps: {
    meta: {
      default: false
    },
    display: {
      order: 1
    },
    registration: {
      selfRegistration: false,
      autoRegistration: false,
      selfUnregistration: false,
      validation: false,
      mail: false,
      userValidation: false,
      eventRegistrationType: constants.REGISTRATION_AUTO
    },
    participants: {
      tutors: 0,
      learners: 0
    }
  }
}

const Event = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    session: T.shape(
      Session.propTypes
    ),
    meta: T.shape({
      type: T.string.isRequired
    }),
    restrictions: T.shape({
      users: T.number,
      dates: T.arrayOf(T.string)
    }),
    registration: T.shape({
      registrationType: T.number
    })
  },
  defaultProps: {
    meta: {
      type: 'training_event'
    },
    registration: {
      registrationType: constants.REGISTRATION_AUTO
    }
  }
}

export {
  Course,
  Session,
  Event
}
