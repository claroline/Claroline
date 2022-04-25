import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {ToolPage} from '#/main/core/tool/containers/page'

import {TagForm} from '#/plugin/tag/tools/tags/components/form'
import {TagList} from '#/plugin/tag/tools/tags/components/list'

const TagsTool = (props) =>
  <ToolPage
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add-tag', {}, 'actions'),
        target: `${props.path}/new`,
        primary: true,
        displayed: props.canCreate
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          exact: true,
          render: () => (
            <TagList path={props.path} />
          )
        }, {
          path: '/new',
          component: TagForm,
          exact: true,
          onEnter: () => props.openForm(),
          disabled: !props.canCreate
        }, {
          path: '/:id?',
          component: TagForm,
          onEnter: (params = {}) => props.openForm(params.id)
        }
      ]}
    />
  </ToolPage>

TagsTool.propTypes = {
  path: T.string.isRequired,
  canCreate: T.bool.isRequired,
  openForm: T.func.isRequired
}

export {
  TagsTool
}
