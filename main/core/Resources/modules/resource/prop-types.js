import {PropTypes as T} from 'prop-types'

const ResourceType = {
  propTypes: {
    name: T.string.isRequired,
    class: T.string.isRequired,
    actions: T.arrayOf(T.shape({
      name: T.string.isRequired,
      scope: T.arrayOf(T.oneOf(['object', 'collection'])),
      group: T.string,
      permission: T.string.isRequired
    }))
  },
  defaultProps: {
    actions: []
  }
}

const ResourceNode = {
  propTypes: {
    id: T.string.isRequired,
    autoId: T.number.isRequired, // for retro-compatibility with old api, will be removed in future.
    name: T.string.isRequired,
    description: T.string,
    poster: T.shape({
      url: T.string
    }),

    /**
     * The workspace to which the resource belongs
     */
    workspace: T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired
    }),

    /**
     * Metadata.
     */
    meta: T.shape({
      type: T.string.isRequired,
      mimeType: T.string.isRequired,
      published: T.bool.isRequired,
      views: T.number,
      creator: T.shape({

      })
    }).isRequired,

    /**
     * Display configuration.
     */
    display: T.shape({
      fullscreen: T.bool.isRequired,
      showIcon: T.bool
    }).isRequired,

    /**
     * Access restrictions.
     */
    restrictions: T.shape({
      dates: T.arrayOf(T.string),
      code: T.string,
      allowedIps: T.arrayOf(T.string)
    }).isRequired,

    /**
     * Access rights.
     */
    rights: T.shape({
      // rights of the current user
      current: T.shape({
        edit: T.bool,
        administrate: T.bool,
        export: T.bool,
        delete: T.bool
      }),
      // all rights configuration
      all: T.shape({
        permissions: T.object
      })
    }),

    /**
     * Notifications configuration.
     */
    notifications: T.shape({
      enabled: T.bool
    })
  },
  defaultProps: {
    meta: {
      published: false,
      views: 0
    },
    display: {
      showIcon: true
    },
    restrictions: {
      dates: []
    },
    notifications: {
      enabled: false
    }
  }
}

const UserEvaluation = {
  propTypes: {
    id: T.number.isRequired,
    date: T.string,
    status: T.string.isRequired,
    duration: T.number,
    score: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    progression: T.number
  }
}

export {
  ResourceType,
  ResourceNode,
  UserEvaluation
}
