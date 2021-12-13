import React, {Fragment} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {route} from '#/main/core/workspace/routing'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const WorkspacesButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={props.model ? trans('add_workspace_model') : trans('add_workspace')}
    disabled={props.disabled}
    modal={[MODAL_WORKSPACES, {
      url: props.model ? ['apiv2_workspace_list_model'] : ['apiv2_workspace_list_managed'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

WorkspacesButton.propTypes = {
  title: T.string,
  model: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const WorkspaceInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <WorkspaceCard
          data={props.value}
          size="xs"
          primaryAction={{
            type: LINK_BUTTON,
            label: trans('open', {}, 'actions'),
            target: route(props.value)
          }}
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <WorkspacesButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
          size={props.size}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-book"
      title={props.picker.model ? trans('no_workspace_model') : trans('no_workspace')}
      size={props.size}
    >
      <WorkspacesButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
        size={props.size}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(WorkspaceInput, DataInputTypes, {
  value: T.shape(WorkspaceType.propTypes),
  picker: T.shape({
    title: T.string,
    model: T.bool
  })
}, {
  value: null,
  picker: {
    model: false
  }
})

export {
  WorkspaceInput
}
