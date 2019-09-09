import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Apps} from '#/plugin/lti/integration/lti/components/apps'
import {App}  from '#/plugin/lti/integration/lti/components/app'

const LtiTool = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('lti', {}, 'integration'),
      target: `${props.path}/lti`
    }]}
    subtitle={trans('lti', {}, 'integration')}
    actions={[
      {
        name: 'lti-add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_lti_app', {}, 'lti'),
        target: `${props.path}/lti/form`,
        primary: true,
        hideLabel: true
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/lti',
          render: () => {
            const component = <Apps path={props.path} />

            return component
          },
          exact: true,
          onEnter: () => {}
        }, {
          path: '/lti/form/:id?',
          render: () => {
            const component = <App path={props.path} />

            return component
          },
          onEnter: (params) => {
            props.openForm(params.id || null)
          },
          onLeave: () => {
            props.resetForm()
          }
        }
      ]}
    />
  </ToolPage>

LtiTool.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  LtiTool
}
