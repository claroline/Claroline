import {PropTypes as T} from 'prop-types'

import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {constants} from '#/plugin/cursus/constants'

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
      order: T.number
    }),
    restrictions: T.shape({
      users: T.number
    }),
    registration: T.shape({
      selfRegistration: T.bool,
      selfUnregistration: T.bool,
      validation: T.bool,
      mail: T.bool,
      userValidation: T.bool
    })
  },
  defaultProps: {
    code: '',
    title: '',
    parent: null,
    meta: {
      order: constants.DEFAULT_ORDER
    },
    restrictions: {
      users: null
    },
    registration: {
      selfRegistration: false,
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
    meta: T.shape({
      default: T.bool,
      course: T.shape(
        Course.propTypes
      ),
      workspace: T.shape(
        WorkspaceTypes.propTypes
      ),
      tutorRole: T.shape(
        RoleTypes.propTypes
      ),
      learnerRole: T.shape(
        RoleTypes.propTypes
      ),
      creationDate: T.string,
      order: T.number,
      color: T.string,
      certificated: T.bool
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
      selfUnregistration: T.bool,
      validation: T.bool,
      mail: T.bool,
      userValidation: T.bool,
      eventRegistrationType: T.number
    })
  },
  defaultProps: {
    meta: {
      default: false,
      order: constants.DEFAULT_ORDER,
      certificated: true
    },
    registration: {
      selfRegistration: false,
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
    meta: T.shape({
      type: T.number,
      session: T.shape(Session.propTypes),
      set: T.string
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
    name: '',
    meta: {
      type: constants.EVENT_TYPE_NONE
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
