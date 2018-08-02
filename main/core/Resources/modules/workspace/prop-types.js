import {PropTypes as T} from 'prop-types'

import {ResourceNode} from '#/main/core/resource/prop-types'

const Workspace = {
  propTypes: {
    id: T.number,
    uuid: T.string,
    name: T.string,
    poster: T.string,
    meta: T.shape({
      slug: T.string,
      model: T.bool,
      personal: T.bool,
      usedStorage: T.number,
      totalUsers: T.number,
      totalResources: T.number
    }).isRequired,
    opening: T.shape({
      type: T.oneOf(['resource', 'tool']),
      target: T.oneOfType([T.string, T.shape(ResourceNode.propTypes)])
    }),
    display: T.shape({
      showBreadcrumbs: T.bool,
      showTools: T.bool,
      openResource: T.shape(
        ResourceNode.propTypes
      )
    }),
    registration: T.shape({
      validation: T.bool,
      selfRegistration: T.bool,
      selfUnregistration: T.bool
    }).isRequired,
    restrictions: T.shape({
      hidden: T.bool,
      maxUsers: T.number,
      maxResources: T.number,
      maxStorage: T.number,
      dates: T.arrayOf(T.string)
    }).isRequired,
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
      personal: false,
      usedStorage: 0,
      totalUsers: 0,
      totalResources: 0,
      forceLang: false
    },
    roles: [],
    opening: {
      type: 'tool',
      target: 'home'
    },
    display: {
      showBreadcrumbs: true,
      showTools: true,
      openResource: null
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

export {
  Workspace
}
