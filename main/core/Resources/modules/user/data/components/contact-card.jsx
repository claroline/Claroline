import React from 'react'
import {PropTypes as T} from 'prop-types'
import {UserCard} from '#/main/core/user/data/components/user-card'

const ContactCard = props =>
  <UserCard
    {...props.data}
  />

ContactCard.propTypes = {
  data: T.object.isRequired
}

export {
  ContactCard
}
