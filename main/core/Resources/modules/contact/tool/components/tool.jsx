import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/page/containers/tabbed-page.jsx'

import {Contacts, ContactsActions} from '#/main/core/contact/tool/components/contacts.jsx'
import {VisibleUsers, VisibleUsersActions} from '#/main/core/contact/tool/components/visible-users.jsx'

const Tool = () =>
  <TabbedPageContainer
    tabs={[
      {
        icon: 'fa fa-fw fa-address-book',
        title: trans('my_contacts'),
        path: '/',
        exact: true,
        actions: ContactsActions,
        content: Contacts
      }, {
        icon: 'fa fa-fw fa-users',
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
