import {PropTypes as T} from 'prop-types'

import {Group} from '#/main/community/group/prop-types'
import {Role} from '#/main/community/role/prop-types'
import {User} from '#/main/community/user/prop-types'
import {Organization} from '#/main/community/organization/prop-types'

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
