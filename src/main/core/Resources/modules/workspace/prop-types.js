import {PropTypes as T} from 'prop-types'

import {ResourceNode} from '#/main/core/resource/prop-types'
import {User} from '#/main/core/user/prop-types'

const Workspace = {
  propTypes: {
    id: T.string,
    autoId: T.number,
    name: T.string,
    slug: T.string,
    poster: T.shape({
      url: T.string
    }),
    contactEmail: T.string,
    meta: T.shape({
      description: T.string,
      creator: T.shape(
        User.propTypes
      ),
      model: T.bool,
      personal: T.bool
    }).isRequired,
    opening: T.shape({
      type: T.oneOf(['resource', 'tool']),
      target: T.oneOfType([T.string, T.shape(ResourceNode.propTypes)])
    }),
    display: T.shape({
      showProgression: T.bool,
      showMenu: T.bool
    }),
    breadcrumb: T.shape({
      displayed: T.bool
    }),
    registration: T.shape({
      validation: T.bool,
      selfRegistration: T.bool,
      selfUnregistration: T.bool
    }),
    restrictions: T.shape({
      hidden: T.bool,
      dates: T.arrayOf(T.string)
    }),
    notifications: T.shape({
      enabled: T.bool
    }),
    roles: T.arrayOf(T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired,
      translationKey: T.string.isRequired
    }))
  },
  defaultProps: {
    meta: {
      model: false,
      personal: false
    },
    roles: [],
    opening: {
      type: 'tool',
      target: 'home'
    },
    display: {
      showProgression: true,
      showMenu: true
    },
    breadcrumb: {
      displayed: true,
      items: ['desktop', 'workspaces', 'current', 'tool']
    },
    registration: {
      validation: false,
      selfRegistration: false,
      selfUnregistration: false
    },
    restrictions: {
      hidden: false,
      dates: []
    },
    notifications: {
      enabled: false
    }
  }
}

const UserEvaluation = {
  propTypes: {
    id: T.string.isRequired,
    date: T.String,
    status: T.string,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    progression: T.number,
    progressionMax: T.number,
    user: T.shape(
      User.propTypes
    ),
    workspace: T.shape(
      Workspace.propTypes
    )
  },
  defaultProps: {

  }
}

export {
  Workspace,
  UserEvaluation
}
