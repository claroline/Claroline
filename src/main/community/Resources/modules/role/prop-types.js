import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/community/constants'

const Role = {
  propTypes: {
    id: T.string,
    name: T.string,
    translationKey: T.string.isRequired,
    type: T.number.isRequired,
    meta: T.shape({
      readOnly: T.bool,
      personalWorkspaceCreationEnabled: T.bool
    }),
    permissions: T.shape({
      open: T.bool,
      edit: T.bool,
      administrate: T.bool,
      delete: T.bool
    }),
    adminTools: T.object,
    desktopTools: T.object
  },
  defaultProps: {
    type: constants.ROLE_PLATFORM
  }
}

export {
  Role
}
