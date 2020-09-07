import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/core/workspace/utils'
import {WorkspaceCard} from '#/main/core/workspace/components/card'

const Workspaces = (props) => {
  const workspacesRefresher = {
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }

  return (
    <ListData
      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      definition={[
        {
          name: 'name',
          label: trans('name'),
          displayed: true,
          primary: true
        }, {
          name: 'code',
          label: trans('code'),
          displayed: true
        }, {
          name: 'meta.created',
          label: trans('creation_date'),
          type: 'date',
          alias: 'created',
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
          alias: 'hidden'
        }, {
          name: 'createdAfter',
          label: trans('created_after'),
          type: 'date',
          displayable: false
        }, {
          name: 'createdBefore',
          label: trans('created_before'),
          type: 'date',
          displayable: false
        }, {
          name: 'registration.selfRegistration',
          label: trans('public_registration'),
          type: 'boolean',
          alias: 'selfRegistration'
        }, {
          name: 'registration.selfUnregistration',
          label: trans('public_unregistration'),
          type: 'boolean',
          alias: 'selfUnregistration'
        }, {
          name: 'restrictions.maxStorage',
          label: trans('available_storage'),
          alias: 'maxStorage'
        }, {
          name: 'restrictions.maxResources',
          label: trans('max_amount_resources'),
          type: 'number',
          alias: 'maxResources'
        }, {
          name: 'restrictions.maxUsers',
          label: trans('workspace_max_users'),
          type: 'number',
          alias: 'maxUsers'
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          displayable: false,
          sortable: false,
          options: {
            objectClass: 'Claroline\\CoreBundle\\Entity\\Workspace\\Workspace'
          }
        }
      ].concat(props.customDefinition)}
      card={WorkspaceCard}

      primaryAction={(row) => getDefaultAction(row, workspacesRefresher, props.basePath, props.currentUser)}
      actions={(rows) => getActions(rows, workspacesRefresher, props.basePath, props.currentUser)}
    />
  )
}

Workspaces.propTypes = {
  basePath: T.string,
  currentUser: T.object,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // TODO : data list prop types
  })),
  invalidate: T.func.isRequired
}

Workspaces.defaultProps = {
  basePath: '',
  customDefinition: []
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
