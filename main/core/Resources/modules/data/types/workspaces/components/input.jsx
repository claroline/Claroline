import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {MODAL_WORKSPACES_PICKER} from '#/main/core/modals/workspaces'

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
                    props.picker.handleSelect ? props.onChange(props.picker.handleSelect(newValue)) : props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}
        <ModalButton
          className="btn btn-workspaces-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_WORKSPACES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                const newValue = props.value
                selected.forEach(workspace => {
                  const index = newValue.findIndex(u => u.id === workspace.id)

                  if (-1 === index) {
                    newValue.push(workspace)
                  }
                })
                props.picker.handleSelect ? props.onChange(props.picker.handleSelect(newValue)) : props.onChange(newValue)
              }
            })
          }]}
        >
          <span className="fa fa-fw fa-book-plus icon-with-text-right" />
          {trans('add_workspaces')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-books"
        title={trans('no_workspace')}
      >
        <ModalButton
          className="btn btn-workspaces-primary"
          primary={true}
          modal={[MODAL_WORKSPACES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.picker.handleSelect ? props.onChange(props.picker.handleSelect(selected)) : props.onChange(selected)
            })
          }]}
        >
          <span className="fa fa-fw fa-workspace-plus icon-with-text-right" />
          {trans('add_workspaces')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(WorkspacesInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(WorkspaceType.propTypes)),
  picker: T.shape({
    title: T.string,
    confirmText: T.string,
    handleSelect: T.func
  })
}, {
  value: null,
  picker: {
    title: trans('workspace_selector'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  WorkspacesInput
}
