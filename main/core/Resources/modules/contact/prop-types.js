import {PropTypes as T} from 'prop-types'

const UserType = {
  propTypes: {
    autoId: T.number.isRequired,
    id: T.string.isRequired,
    name: T.string.isRequired,
    firstName: T.string.isRequired,
    lastName: T.string.isRequired,
    username: T.string.isRequired,
    picture: T.shape({
      url: T.string.isRequired
    }),
    email: T.string.isRequired,
    meta: T.shape({
      publicUrl: T.string
    })
  }
}

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
    data: T.shape(OptionsDataType.propTypes).isRequired,
    user: T.shape(UserType.propTypes).isRequired
  }
}

const CategoryType = {
  propTypes: {
    id: T.number.isRequired,
    name: T.string.isRequired,
    order: T.number,
    user: T.shape(UserType.propTypes).isRequired
  }
}

const ContactType = {
  propTypes: {
    id: T.number.isRequired,
    user: T.shape(UserType.propTypes).isRequired,
    data: T.shape(UserType.propTypes).isRequired,
    categories: T.arrayOf(T.shape(CategoryType.propTypes))
  }
}

export {
  OptionsDataType,
  OptionsType,
  CategoryType,
  ContactType
}