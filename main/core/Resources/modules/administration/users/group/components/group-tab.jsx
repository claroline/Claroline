import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/core/administration/users/store'
import {Group}   from '#/main/core/administration/users/group/components/group'
import {Groups}  from '#/main/core/administration/users/group/components/groups'
import {actions} from '#/main/core/administration/users/group/store'

const GroupTabActionsComponent = (props) =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_group')}
      target={`${props.path}/groups/form`}
      primary={true}
    />
  </PageActions>

GroupTabActionsComponent.propTypes = {
  path: T.string.isRequired
}

const GroupTabComponent = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/groups',
        exact: true,
        component: Groups
      }, {
        path: '/groups/form/:id?',
        onEnter: (params) => props.openForm(params.id || null),
        component: Group
      }
    ]}
  />

GroupTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired
}

const GroupTabActions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(GroupTabActionsComponent)

const GroupTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open(baseSelectors.STORE_NAME+'.groups.current', id))
    }
  })
)(GroupTabComponent)

export {
  GroupTabActions,
  GroupTab
}
