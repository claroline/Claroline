import React, {Fragment} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {route} from '#/main/core/workspace/routing'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

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
      model: props.model,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

WorkspacesButton.propTypes = {
  title: T.string,
  model: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired
}

const WorkspaceInput = props => {
  if (props.value) {
    return(
      <Fragment>
        <WorkspaceCard
          data={props.value}
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
        />
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      icon="fa fa-book"
      title={props.picker.model ? trans('no_workspace_model') : trans('no_workspace')}
    >
      <WorkspacesButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </EmptyPlaceholder>
  )
}

implementPropTypes(WorkspaceInput, FormFieldTypes, {
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
