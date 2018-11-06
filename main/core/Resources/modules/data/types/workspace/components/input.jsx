import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {MODAL_WORKSPACES_PICKER} from '#/main/core/modals/workspaces'

const WorkspaceInput = props => {
  if (props.value) {
    return(
      <div>
        <WorkspaceCard
          data={props.value}
          size="sm"
          orientation="col"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              callback: () => props.picker.handleSelect ?
                props.onChange(props.picker.handleSelect(null)) :
                props.onChange(null)
            }
          ]}
        />
        <ModalButton
          className="btn btn-workspaces-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_WORKSPACES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.picker.handleSelect ?
                props.onChange(props.picker.handleSelect(selected[0])) :
                props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-book icon-with-text-right" />
          {trans('select_a_workspace')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-book"
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
              callback: () => props.picker.handleSelect ?
                props.onChange(props.picker.handleSelect(selected[0])) :
                props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-book icon-with-text-right" />
          {trans('select_a_workspace')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(WorkspaceInput, FormFieldTypes, {
  value: T.shape(WorkspaceType.propTypes),
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
  WorkspaceInput
}
