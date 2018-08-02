import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Group}   from '#/main/core/administration/user/group/components/group'
import {Groups}  from '#/main/core/administration/user/group/components/groups'
import {actions} from '#/main/core/administration/user/group/actions'

const GroupTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_group')}
      target="/groups/form"
      primary={true}
    />
  </PageActions>

const GroupTabComponent = props =>
  <Routes
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
  openForm: T.func.isRequired
}

const GroupTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('groups.current', id))
    }
  })
)(GroupTabComponent)

export {
  GroupTabActions,
  GroupTab
}
