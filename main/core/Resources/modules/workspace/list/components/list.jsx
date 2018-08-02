import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {WorkspaceList} from '#/main/core/workspace/list/components/workspace-list'
import {actions} from '#/main/core/workspace/list/actions'
import {trans} from '#/main/core/translation'
import {constants as listConst} from '#/main/app/content/list/constants'
import {currentUser} from '#/main/core/user/current'

import {PageContainer, PageHeader,PageContent} from '#/main/core/layout/page/index'

const WorkspacesList = props => {
  const definition = WorkspaceList.definition
  const defaultProps = props.parameters.workspace.list.default_properties

  definition.forEach(prop => {
    prop.displayed = defaultProps.indexOf(prop.name) > -1
  })

  return(
    <PageContainer>
      <PageHeader>
      </PageHeader>
      <PageContent>
        <ListData
          name="workspaces"
          fetch={{
            url: [props.url],
            autoload: true
          }}
          definition={definition}
          primaryAction={WorkspaceList.open}
          card={WorkspaceList.card}
          display={{
            current: props.parameters.workspace.list.default_mode,
            available: Object.keys(listConst.DISPLAY_MODES)
          }}
          actions={(rows) => [
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-book',
              label: trans('register'),
              displayed: currentUser && rows[0].registration.selfRegistration && !rows[0].permissions['open'] && !rows[0].registration.waitingForRegistration,
              scope: ['object'],
              callback: () => props.register(rows[0]),
              confirm: {
                title: trans('register'),
                message: rows[0].registration.validation ? trans('workspace_awaiting_validation'): trans('register_to_a_public_workspace')
              }
            },
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-book',
              label: trans('unregister'),
              dangerous: true,
              displayed: currentUser && rows[0].registration.selfUnregistration && rows[0].permissions['open'],
              scope: ['object'],
              callback: () => props.unregister(rows[0]),
              confirm: {
                title: trans('unregister'),
                message: trans('unregister_from_a_workspace')
              }
            }
          ]}
        />
      </PageContent>
    </PageContainer>
  )}

WorkspacesList.propTypes = {
  register: T.func.isRequired,
  unregister: T.func.isRequired,
  url: T.string.isRequired,
  parameters: T.object.isRequired
}

const Workspaces = connect(
  state => ({
    url: state.url,
    parameters: state.parameters
  }),
  dispatch => ({
    register(workspace) {
      dispatch(actions.register(workspace))
    },
    unregister(workspace) {
      dispatch(actions.unregister(workspace))
    }
  })
)(WorkspacesList)

export {
  Workspaces
}
