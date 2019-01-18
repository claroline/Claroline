import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {Apps} from '#/plugin/lti/administration/lti/components/apps'
import {App}  from '#/plugin/lti/administration/lti/components/app'

const LtiTool = props =>
  <ToolPage
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_lti_app', {}, 'lti'),
        target: '/form',
        primary: true
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/',
          component: Apps,
          exact: true
        }, {
          path: '/form/:id?',
          component: App,
          onEnter: (params) => props.openForm(params.id || null),
          onLeave: () => props.resetForm()
        }
      ]}
    />
  </ToolPage>

LtiTool.propTypes = {
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  LtiTool
}