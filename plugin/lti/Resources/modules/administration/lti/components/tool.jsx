import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {Toolbar} from '#/main/app/action/components/toolbar'

import {Apps} from '#/plugin/lti/administration/lti/components/apps'
import {App}  from '#/plugin/lti/administration/lti/components/app'

const LtiTool = props =>
  <Fragment>
    <Toolbar
      className="page-actions"
      actions={[
        {
          name: 'lti-add',
          type: LINK_BUTTON,
          icon: 'fa fa-plus',
          target: '/lti/form',
          primary: true,
          hideLabel: true
        }
      ]}
    />
    <Routes
      routes={[
        {
          path: '/lti',
          component: Apps,
          exact: true,
          onEnter: () => {}
        }, {
          path: '/lti/form/:id?',
          component: App,
          onEnter: (params) => {
            props.openForm(params.id || null)
          },
          onLeave: () => {
            props.resetForm()
          }
        }
      ]}
    />
  </Fragment>

LtiTool.propTypes = {
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

export {
  LtiTool
}
