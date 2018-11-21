import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {MODAL_WORKSPACES_PICKER} from '#/main/core/modals/workspaces'

const WorkspacesButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-book-plus"
    label={trans('add_workspaces')}
    primary={true}
    modal={[MODAL_WORKSPACES_PICKER, {
      url: ['apiv2_administrated_list'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
  />

WorkspacesButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const WorkspacesInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <div>
        {props.value.map(workspace =>
          <WorkspaceCard
            key={`workspace-card-${workspace.id}`}
            data={workspace}
            size="sm"
            orientation="col"
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(u => u.id === workspace.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <WorkspacesButton
          {...props.picker}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(workspace => {
              const index = newValue.findIndex(u => u.id === workspace.id)

              if (-1 === index) {
                newValue.push(workspace)
              }
            })

            props.onChange(newValue)
          }}
        />
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-books"
        title={trans('no_workspace')}
      >
        <WorkspacesButton
          {...props.picker}
          onChange={props.onChange}
        />
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(WorkspacesInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(WorkspaceType.propTypes)),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('workspace_selector')
  }
})

export {
  WorkspacesInput
}
