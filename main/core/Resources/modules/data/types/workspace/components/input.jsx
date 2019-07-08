import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

const WorkspacesButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-book"
    label={trans('select_a_workspace')}
    primary={true}
    modal={[MODAL_WORKSPACES, {
      url: ['apiv2_workspace_list_managed'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

WorkspacesButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const WorkspaceInput = props => {
  if (props.value) {
    return(
      <div>
        <WorkspaceCard
          data={props.value}
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <WorkspacesButton
          {...props.picker}
          onChange={props.onChange}
        />
      </div>
    )
  }

  return (
    <EmptyPlaceholder
      size="lg"
      icon="fa fa-book"
      title={trans('no_workspace')}
    >
      <WorkspacesButton
        {...props.picker}
        onChange={props.onChange}
      />
    </EmptyPlaceholder>
  )
}

implementPropTypes(WorkspaceInput, FormFieldTypes, {
  value: T.shape(WorkspaceType.propTypes),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  WorkspaceInput
}
