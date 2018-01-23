import React from 'react'

import {t} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/page/containers/tabbed-page.jsx'
import {
  ContactsTab,
  ContactsTabActions,
  VisibleUsersTab,
  VisibleUsersTabActions
} from '#/main/core/contact/tool/components/contacts-tab.jsx'

export const ContactsTool = () =>
  <TabbedPageContainer
    redirect={[
      {from: '/', exact: true, to: '/contacts'}
    ]}

    tabs={[
      {
        icon: 'fa fa-fw fa-address-book',
        title: t('my_contacts'),
        path: '/contacts',
        actions: ContactsTabActions,
        content: ContactsTab
      },
      {
        icon: 'fa fa-fw fa-user',
        title: t('all_visible_users'),
        path: '/users',
        actions: VisibleUsersTabActions,
        content: VisibleUsersTab
      }
    ]}
  />