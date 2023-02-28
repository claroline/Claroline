import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/prop-types'

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

const ResourceComment = {
  propTypes: {
    id: T.string,
    content: T.string,
    user: T.object,
    creationDate: T.string,
    editionDate: T.string
  }
}

const ResourceNode = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    autoId: T.number,
    slug: T.string,
    thumbnail: T.string,
    poster: T.string,

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
      className: T.string,
      mimeType: T.string.isRequired,
      active: T.bool,
      published: T.bool.isRequired,
      description: T.string,
      views: T.number,
      creator: T.shape(
        User.propTypes
      ),
      created: T.string,
      updated: T.string,
      commentsActivated: T.bool,
      authors: T.string,
      license: T.string
    }),

    /**
     * Display configuration.
     */
    display: T.shape({
      fullscreen: T.bool.isRequired,
      showIcon: T.bool
    }),

    /**
     * Access restrictions.
     */
    restrictions: T.shape({
      dates: T.arrayOf(T.string),
      code: T.string,
      allowedIps: T.arrayOf(T.string)
    }),

    /**
     * Access rights.
     */
    rights: T.arrayOf(T.shape({
      name: T.string.isRequired,
      translationKey: T.string.isRequired,
      permissions: T.object.isRequired
    })),

    /**
     * Notifications configuration.
     */
    notifications: T.shape({
      enabled: T.bool
    }),

    comments: T.arrayOf(T.shape(ResourceComment.propTypes))
  },
  defaultProps: {
    meta: {
      published: false,
      active: true,
      views: 0
    },
    display: {
      fullscreen: false,
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

export {
  ResourceType,
  ResourceComment,
  ResourceNode
}
