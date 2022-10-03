import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/core/user/constants'

const User = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string.isRequired,
    firstName: T.string,
    lastName: T.string,
    username: T.string.isRequired,
    picture: T.string,
    thumbnail: T.string,
    meta: T.shape({
      created: T.string,
      lastActivity: T.string,
      description: T.string,
      personalWorkspace: T.bool
    }),
    restrictions: T.shape({
      disabled: T.bool,
      dates: T.arrayOf(T.string)
    }),
    permissions: T.shape({
      contact: T.bool.isRequired,
      edit: T.bool.isRequired,
      administrate: T.bool.isRequired,
      delete: T.bool.isRequired
    }),
    roles: T.arrayOf(T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired,
      translationKey: T.string.isRequired,
      type: T.number.isRequired
    }))
  },
  defaultProps: {
    permissions: {
      contact: false,
      edit: false,
      administrate: false,
      delete: false
    },
    restrictions: {
      disabled: false,
      dates: []
    }
  }
}

const Role = {
  propTypes: {
    id: T.string,
    name: T.string,
    translationKey: T.string.isRequired,
    type: T.number.isRequired,
    meta: T.shape({
      users: T.number,
      readOnly: T.bool
    }),
    restrictions: T.shape({
      maxUsers: T.number
    }),
    adminTools: T.object,
    desktopTools: T.object
  },
  defaultProps: {
    type: constants.ROLE_PLATFORM
  }
}

const Group = {
  propTypes: {
    id: T.string,
    name: T.string
  },
  defaultProps: {}
}

const Organization = {
  propTypes: {
    id: T.string,
    name: T.string,
    code: T.string,
    email: T.string,
    parent: T.shape({
      id: T.string.isRequired,
      name: T.string.isRequired
    }),
    restrictions: T.shape({
      public: T.bool,
      users: T.number
    })
  },
  defaultProps: {
    parent: null
  }
}

const Location = {
  propTypes: {
    id: T.string,
    name: T.string,
    meta: T.shape({
      type: T.number,
      description: T.string
    }),
    phone: T.string,
    address: T.shape({
      street1: T.string,
      street2: T.string,
      postalCode: T.string,
      city: T.string,
      state: T.string,
      country: T.string
    }),
    gps: T.shape({
      latitude: T.number,
      longitude: T.number
    })
  },
  defaultProps: {}
}

export {
  User,
  Role,
  Group,
  Organization,
  Location
}
