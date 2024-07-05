import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {PickerModal} from '#/main/app/data/modals/picker/components/modal'

import {selectors} from '#/main/core/modals/workspaces/store'
import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {WorkspaceIcon} from '#/main/app/contexts/workspace/components/icon'

const WorkspacesModal = props => {
  return (
    <PickerModal
      {...omit(props)}
      icon="fa fa-fw fa-book"
      name={selectors.STORE_NAME}
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
          options: {long: true}
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
        }, {
          name: 'restrictions.hidden',
          label: trans('hidden'),
          type: 'boolean',
          alias: 'hidden',
          displayable: false
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
      ]}
      card={WorkspaceCard}
    />
  )
}

WorkspacesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  multiple: T.bool,
  // from modal
  fadeModal: T.func.isRequired
}

WorkspacesModal.defaultProps = {
  url: ['apiv2_workspace_list_managed'],
  title: trans('workspaces'),
  multiple: true
}

export {
  WorkspacesModal
}
