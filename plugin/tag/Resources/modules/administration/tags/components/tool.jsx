import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {ToolPage} from '#/main/core/tool/containers/page'

import {TagForm} from '#/plugin/tag/administration/tags/components/form'
import {TagList} from '#/plugin/tag/administration/tags/components/list'

const TagsTool = (props) =>
  <ToolPage
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_tag', {}, 'tag'),
        target: '/new',
        primary: true
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: TagList
        }, {
          path: '/new',
          component: TagForm,
          exact: true,
          onEnter: () => props.openForm()
        }, {
          path: '/:id?',
          component: TagForm,
          onEnter: (params = {}) => props.openForm(params.id)
        }
      ]}
    />
  </ToolPage>

TagsTool.propTypes = {
  openForm: T.func.isRequired
}

export {
  TagsTool
}
