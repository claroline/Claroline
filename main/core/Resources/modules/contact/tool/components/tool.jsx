import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {Contacts, ContactsActions} from '#/main/core/contact/tool/components/contacts.jsx'
import {VisibleUsers, VisibleUsersActions} from '#/main/core/contact/tool/components/visible-users.jsx'

const Tool = () =>
  <TabbedPageContainer
    title={trans('my_contacts', {}, 'tools')}
    tabs={[
      {
        icon: 'fa fa-address-book',
        title: trans('my_contacts'),
        path: '/',
        exact: true,
        actions: ContactsActions,
        content: Contacts
      }, {
        icon: 'fa fa-users',
        title: trans('all_visible_users'),
        path: '/users',
        actions: VisibleUsersActions,
        content: VisibleUsers
      }
    ]}
  />

export {
  Tool
}
