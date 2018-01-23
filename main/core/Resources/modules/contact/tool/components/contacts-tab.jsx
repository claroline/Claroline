import React from 'react'

import {Routes} from '#/main/core/router'
import {Contacts, ContactsActions} from '#/main/core/contact/tool/components/contacts.jsx'
import {VisibleUsers, VisibleUsersActions} from '#/main/core/contact/tool/components/visible-users.jsx'

const ContactsTabActions = () =>
  <Routes
    routes={[
      {
        path: '/contacts',
        exact: true,
        component: ContactsActions
      }
    ]}
  />

const ContactsTabComponent = () =>
  <Routes
    routes={[
      {
        path: '/contacts',
        exact: true,
        component: Contacts
      }
    ]}
  />

const VisibleUsersTabActions = () =>
  <Routes
    routes={[
      {
        path: '/users',
        exact: true,
        component: VisibleUsersActions
      }
    ]}
  />

const VisibleUsersTabComponent = () =>
  <Routes
    routes={[
      {
        path: '/users',
        exact: true,
        component: VisibleUsers
      }
    ]}
  />

export {
  ContactsTabActions,
  ContactsTabComponent as ContactsTab,
  VisibleUsersTabActions,
  VisibleUsersTabComponent as VisibleUsersTab
}
