import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'
import {constants as listConst} from '#/main/core/data/list/constants'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'

import {select} from '#/main/core/contact/tool/selectors'
import {OptionsType} from '#/main/core/contact/prop-types'
import {actions} from '#/main/core/contact/tool/actions'
import {MODAL_CONTACTS_OPTIONS_FORM} from '#/main/core/contact/tool/components/modal/contacts-options-form.jsx'

const ContactsActionsComponent = props =>
  <PageActions>
    <PageAction
      id="contact-add"
      icon="fa fa-fw fa-plus"
      title={t('add_contacts')}
      action={props.pickUsers}
      primary={true}
    />

    <PageAction
      id="options-edit"
      icon="fa fa-fw fa-cog"
      title={t('configure')}
      action={() => props.configure(props.options)}
    />
  </PageActions>

ContactsActionsComponent.propTypes = {
  options: T.object.isRequired,
  pickUsers: T.func.isRequired,
  configure: T.func.isRequired
}

const ContactsComponent = props =>
  <DataListContainer
    name="contacts"
    open={{
      action: (row) => generateUrl('claro_user_profile', {'publicUrl': row.data.meta.publicUrl})
    }}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    fetch={{
      url: ['apiv2_contact_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_contact_delete_bulk']
    }}
    actions={[
      {
        icon: 'fa fa-fw fa-eye',
        label: t('show_profile'),
        action: (rows) => window.location = generateUrl('claro_user_profile', {'publicUrl': rows[0].data.meta.publicUrl}),
        context: 'row'
      },
      {
        icon: 'fa fa-fw fa-envelope-o',
        label: t('send_message'),
        action: (rows) => {
          window.location = `${generateUrl('claro_message_show', {'message': 0})}?${rows.map(c => `userIds[]=${c.data.autoId}`).join('&')}`
        }
      }
    ]}
    definition={[
      {
        name: 'data.username',
        type: 'username',
        label: t('username'),
        displayed: props.options.data.show_username,
        primary: props.options.data.show_username
      }, {
        name: 'data.lastName',
        type: 'string',
        label: t('last_name'),
        displayed: true,
        primary: !props.options.data.show_username
      }, {
        name: 'data.firstName',
        type: 'string',
        label: t('first_name'),
        displayed: true
      }, {
        name: 'data.email',
        type: 'string',
        label: t('email'),
        displayed: props.options.data.show_mail
      }, {
        name: 'data.phone',
        type: 'string',
        label: t('phone'),
        displayed: props.options.data.show_phone
      }
    ]}
    card={row => ({
      icon: <UserAvatar picture={row.data.picture} alt={true}/>,
      title: row.data.username,
      subtitle: row.data.firstName + ' ' + row.data.lastName,
      contentText: '',
      footer:
        <span>
        </span>,
      footerLong:
        <span>
        </span>
    })}
  />

ContactsComponent.propTypes = {
  options: T.shape(OptionsType.propTypes)
}

function mapStateToProps(state) {
  return {
    options: select.options(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    pickUsers: () => {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: t('add_contacts'),
        confirmText: t('add_contact'),
        name: 'users.picker',
        definition: [
          {
            name: 'username',
            type: 'username',
            label: t('username'),
            displayed: true
          }, {
            name: 'lastName',
            type: 'string',
            label: t('last_name'),
            displayed: true
          }, {
            name: 'firstName',
            type: 'string',
            label: t('first_name'),
            displayed: true
          }
        ],
        card: row => ({
          icon: <UserAvatar picture={row.data.picture} alt={true}/>,
          title: row.data.username,
          subtitle: row.data.firstName + ' ' + row.data.lastName
        }),
        fetch: {
          url: ['apiv2_visible_users_list', {picker: 1}],
          autoload: true
        },
        handleSelect: selected => {
          dispatch(actions.createContacts(selected))
        }
      }))
    },
    configure: options => {
      dispatch(modalActions.showModal(MODAL_CONTACTS_OPTIONS_FORM, {
        data: options,
        save: options => dispatch(actions.saveOptions(options))
      }))
    }
  }
}

const Contacts = connect(mapStateToProps, {})(ContactsComponent)
const ContactsActions = connect(mapStateToProps, mapDispatchToProps)(ContactsActionsComponent)

export {
  Contacts,
  ContactsActions
}
