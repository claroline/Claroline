import {PropTypes as T} from 'prop-types'

import {User} from '#/main/core/user/prop-types'

const OptionsDataType = {
  propTypes: {
    show_all_my_contacts: T.bool,
    show_all_visible_users: T.bool,
    show_username: T.bool,
    show_mail: T.bool,
    show_phone: T.bool,
    show_picture: T.bool
  }
}

const OptionsType = {
  propTypes: {
    id: T.number.isRequired,
    data: T.shape(
      OptionsDataType.propTypes
    ).isRequired,
    user: T.shape(
      User.propTypes
    ).isRequired
  }
}

const CategoryType = {
  propTypes: {
    id: T.number.isRequired,
    name: T.string.isRequired,
    order: T.number,
    user: T.shape(
      User.propTypes
    ).isRequired
  }
}

const ContactType = {
  propTypes: {
    id: T.number.isRequired,
    user: T.shape(
      User.propTypes
    ).isRequired,
    data: T.shape(
      User.propTypes
    ).isRequired,
    categories: T.arrayOf(T.shape(
      CategoryType.propTypes
    ))
  }
}

export {
  OptionsDataType,
  OptionsType,
  CategoryType,
  ContactType
}