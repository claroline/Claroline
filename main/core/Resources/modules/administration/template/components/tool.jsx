import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Templates} from '#/main/core/administration/template/components/templates'
import {Template} from '#/main/core/administration/template/components/template'

const TemplateTool = (props) =>
  <ToolPage
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_a_template'),
        target: `${props.path}/form`,
        primary: true,
        exact: true
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          component: Templates,
          exact: true
        }, {
          path: '/form/:id?',
          component: Template,
          onEnter: (params) => props.openForm(props.defaultLocale, params.id || null),
          onLeave: () => props.resetForm(props.defaultLocale)
        }
      ]}
    />
  </ToolPage>

TemplateTool.propTypes = {
  path: T.string.isRequired,
  defaultLocale: T.string,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  TemplateTool
}