import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {trans} from '#/main/core/translation'
import {actions} from '#/main/core/user/contact/store'
import {OptionsType} from '#/main/core/user/contact/prop-types'
import {UserCard} from '#/main/core/user/data/components/user-card'

const VisibleUsersComponent = props =>
  <ListData
    name="visibleUsers"
    primaryAction={(row) => ({
      type: URL_BUTTON,
      target: ['claro_user_profile', {'publicUrl': row.meta.publicUrl}]
    })}
    fetch={{
      url: ['apiv2_visible_users_list'],
      autoload: true
    }}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    actions={(rows) => [
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-address-book-o',
        label: trans('add_contact'),
        callback: () => props.createContacts(rows.map(r => r.id))
      }
    ]}
    definition={[
      {
        name: 'username',
        type: 'username',
        label: trans('username'),
        displayed: props.options.data.show_username,
        primary: props.options.data.show_username
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
        displayed: true,
        primary: !props.options.data.show_username
      }, {
        name: 'firstName',
        type: 'string',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'email',
        type: 'string',
        label: trans('email'),
        displayed: props.options.data.show_mail
      }, {
        name: 'phone',
        type: 'string',
        label: trans('phone'),
        displayed: props.options.data.show_phone
      }, {
        name: 'groupName',
        type: 'string',
        label: trans('group'),
        displayed: false,
        displayable: false,
        filterable: true
      }
    ]}
    card={UserCard}
  />

VisibleUsersComponent.propTypes = {
  options: T.shape(OptionsType.propTypes),
  createContacts: T.func.isRequired
}

const VisibleUsers = connect(
  (state) => ({
    options: formSelect.data(formSelect.form(state, 'options'))
  }),
  (dispatch) => ({
    createContacts: users => dispatch(actions.createContacts(users))
  })
)(VisibleUsersComponent)

export {
  VisibleUsers
}
