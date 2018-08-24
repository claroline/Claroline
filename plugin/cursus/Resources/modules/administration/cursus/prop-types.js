import {PropTypes as T} from 'prop-types'

import {Role as RoleType} from '#/main/core/user/prop-types'
import {User as UserType} from '#/main/core/user/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'

const Parameters = {
  propTypes: {
    disable_certificates: T.bool.isRequired,
    disable_invitations: T.bool.isRequired,
    disable_session_event_registration: T.bool.isRequired,
    display_user_events_in_desktop_agenda: T.bool.isRequired,
    enable_courses_profile_tab: T.bool.isRequired,
    enable_ws_in_courses_profile_tab: T.bool.isRequired,
    session_default_duration: T.number,
    session_default_total: T.number
  }
}

const Course = {
  propTypes: {
    id: T.string,
    code: T.string,
    title: T.string,
    description: T.string,
    meta: T.shape({
      workspace: T.shape(WorkspaceType.propTypes),
      workspaceModel: T.shape(WorkspaceType.propTypes),
      tutorRoleName: T.string,
      learnerRoleName: T.string,
      icon: T.string,
      defaultSessionDuration: T.number,
      withSessionEvent: T.bool,
      order: T.number
    }),
    restrictions: T.shape({
      maxUsers: T.number
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
    meta: {
      withSessionEvent: true,
      order: 500
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

const Cursus = {
  propTypes: {
    id: T.string,
    code: T.string,
    title: T.string,
    description: T.string,
    parent: T.object,
    meta: T.shape({
      course: T.shape(Course.propTypes),
      workspace: T.shape(WorkspaceType.propTypes),
      order: T.number,
      icon: T.string,
      blocking: T.bool,
      details: T.object
    }),
    structure: T.shape({
      root: T.number,
      lvl: T.number,
      lft: T.number,
      rgt: T.number
    })
  }
}

const Session = {
  propTypes: {
    id: T.string,
    name: T.string,
    description: T.string,
    meta: T.shape({
      type: T.number,
      course: T.shape(Course.propTypes),
      workspace: T.shape(WorkspaceType.propTypes),
      tutorRole: T.shape(RoleType.propTypes),
      learnerRole: T.shape(RoleType.propTypes),
      sessionStatus: T.number,
      defaultSession: T.bool,
      creationDate: T.string,
      order: T.number,
      color: T.string,
      total: T.number,
      certificated: T.bool
    }),
    restrictions: T.shape({
      maxUsers: T.number,
      dates: T.arrayOf(T.string)
    }),
    registration: T.shape({
      publicRegistration: T.bool,
      publicUnregistration: T.bool,
      registrationValidation: T.bool,
      userValidation: T.bool,
      organizationValidation: T.bool,
      eventRegistrationType: T.number
    })
  }
}

const SessionEventSet = {
  propTypes: {
    id: T.string,
    name: T.string,
    limit: T.number,
    meta: T.shape({
      session: T.shape(Session.propTypes)
    })
  }
}

const SessionEvent = {
  propTypes: {
    id: T.string,
    name: T.string,
    description: T.string,
    meta: T.shape({
      type: T.number,
      session: T.shape(Session.propTypes),
      set: T.shape(SessionEventSet.propTypes)
    }),
    restrictions: T.shape({
      maxUsers: T.number,
      dates: T.arrayOf(T.string)
    }),
    registration: T.shape({
      registrationType: T.number
    })
  }
}

const SessionEventComment = {
  propTypes: {
    id: T.string,
    content: T.string,
    user: T.shape(UserType.propTypes),
    meta: T.shape({
      sessionEvent: T.shape(SessionEvent.propTypes),
      creationDate: T.string,
      editionDate: T.string
    })
  }
}

export {
  Parameters,
  Cursus,
  Course,
  Session,
  SessionEvent,
  SessionEventSet,
  SessionEventComment
}