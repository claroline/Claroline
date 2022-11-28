import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {route} from '#/main/community/user/routing'
import {ContactCard} from '#/plugin/message/data/components/contact-card'
import {ToolParameters as ToolParametersTypes} from '#/plugin/message/tools/messaging/prop-types'
import {selectors} from '#/plugin/message/tools/messaging/store'

const ContactsComponent = props =>
  <ListData
    name={`${selectors.STORE_NAME}.contacts`}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    fetch={{
      url: ['apiv2_contact_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: route(row.data)
    })}
    delete={{
      url: ['apiv2_contact_delete_bulk']
    }}
    definition={[
      {
        name: 'data.username',
        type: 'username',
        alias: 'username',
        label: trans('username'),
        primary: true
      }, {
        name: 'data.lastName',
        type: 'string',
        alias: 'lastName',
        label: trans('last_name'),
        displayed: true
      }, {
        name: 'data.firstName',
        type: 'string',
        alias: 'firstName',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'data.email',
        type: 'string',
        alias: 'email',
        label: trans('email'),
        displayed: props.options.show_mail
      }, {
        name: 'data.phone',
        type: 'string',
        alias: 'phone',
        label: trans('phone'),
        displayed: props.options.show_phone
      }, {
        name: 'group',
        type: 'string',
        label: trans('group'),
        displayed: false,
        displayable: false,
        filterable: true
      }
    ]}
    card={ContactCard}
  />

ContactsComponent.propTypes = {
  options: T.shape(
    ToolParametersTypes.propTypes
  )
}

const Contacts = connect(
  () => ({
    options: {
      show_mail: true,
      show_phone: true
    }
  })
)(ContactsComponent)

export {
  Contacts
}
