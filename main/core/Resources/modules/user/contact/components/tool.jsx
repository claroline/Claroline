import React from 'react'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {Contacts} from '#/main/core/user/contact/components/contacts'
import {VisibleUsers} from '#/main/core/user/contact/components/visible-users'
import {Parameters} from '#/main/core/user/contact/components/parameters'

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
        content: Parameters
      }
    ]}
  />

export {
  Tool
}
