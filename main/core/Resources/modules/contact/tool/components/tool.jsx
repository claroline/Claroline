import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {Contacts} from '#/main/core/contact/tool/components/contacts.jsx'
import {VisibleUsers} from '#/main/core/contact/tool/components/visible-users.jsx'
import {Parameters, ParametersActions} from '#/main/core/contact/tool/components/parameters.jsx'

const Tool = () =>
  <TabbedPageContainer
    title={trans('my_contacts', {}, 'tools')}
    tabs={[
      {
        icon: 'fa fa-address-book',
        title: trans('my_contacts'),
        path: '/',
        exact: true,
        content: Contacts
      }, {
        icon: 'fa fa-users',
        title: trans('all_visible_users'),
        path: '/users',
        content: VisibleUsers
      }, {
        icon: 'fa fa-cog',
        title: trans('parameters'),
        path: '/parameters',
        onlyIcon: true,
        actions: ParametersActions,
        content: Parameters
      }
    ]}
  />

export {
  Tool
}
