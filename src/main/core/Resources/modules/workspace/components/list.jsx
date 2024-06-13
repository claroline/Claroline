import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/core/workspace/utils'
import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {WorkspaceIcon} from '#/main/app/contexts/workspace/components/icon'

const Workspaces = (props) => {
  const workspacesRefresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, workspacesRefresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, workspacesRefresher, props.path, props.currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true,
          render: (workspace) => (
            <div className="d-flex flex-direction-row gap-3 align-items-center">
              <WorkspaceIcon workspace={workspace} size="xs" />
              {workspace.name}
            </div>
          )
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          sortable: false,
          options: {long: true},
          //displayed: true
        }, {
          name: 'code',
          type: 'string',
          label: trans('code')
        }, {
          name: 'meta.created',
          label: trans('creation_date'),
          type: 'date',
          alias: 'createdAt',
          filterable: false
        }, {
          name: 'meta.updated',
          label: trans('modification_date'),
          type: 'date',
          alias: 'updatedAt',
          displayed: true,
          filterable: false
        }, {
          name: 'meta.creator',
          label: trans('creator'),
          type: 'user',
          alias: 'creator'
        },  {
          name: 'meta.personal',
          label: trans('personal_workspace'),
          type: 'boolean',
          alias: 'personal'
        }, {
          name: 'restrictions.hidden',
          label: trans('hidden'),
          type: 'boolean',
          alias: 'hidden',
          displayable: false
        }, {
          name: 'registration.waitingForRegistration',
          label: trans('pending'),
          type: 'boolean',
          filterable: false,
          sortable: false
        }, {
          name: 'registration.selfRegistration',
          label: trans('public_registration'),
          type: 'boolean',
          alias: 'selfRegistration'
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          displayable: true,
          sortable: false,
          options: {
            objectClass: 'Claroline\\CoreBundle\\Entity\\Workspace\\Workspace'
          }
        }, {
          name: 'organizations',
          type: 'organizations',
          label: trans('organizations'),
          displayable: false,
          displayed: false,
          sortable: false,
          filterable: true
        }
      ].concat(props.customDefinition)}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      card={WorkspaceCard}
    />
  )
}

Workspaces.propTypes = {
  path: T.string,
  currentUser: T.object,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  autoload: T.bool,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func,
  display: T.object,
  invalidate: T.func.isRequired,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

Workspaces.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const WorkspaceList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  dispatch => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Workspaces)

export {
  WorkspaceList
}
