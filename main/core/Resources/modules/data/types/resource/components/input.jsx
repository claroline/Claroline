import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ResourceCard} from '#/main/core/resource/components/card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

// TODO : manage disabled state

const ResourceInput = props => {
  if (!isEmpty(props.value) && !props.embedded) {
    return(
      <ResourceCard
        data={props.value}
        size="xs"
        actions={[
          {
            name: 'replace',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-recycle',
            label: trans('replace', {}, 'actions'),
            modal: [MODAL_RESOURCES, {
              title: props.picker.title,
              current: props.picker.current,
              root: props.picker.root,
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('select', {}, 'actions'),
                callback: () => props.onChange(selected[0])
              })
            }]
          }, {
            name: 'delete',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            dangerous: true,
            modal: [MODAL_CONFIRM, {
              title: transChoice('resources_delete_confirm', 1, {}, 'resource'),
              question: transChoice('resources_delete_message', 1, {count: 1}, 'resource'),
              handleConfirm: () => props.onChange(null)
            }]
          }
        ]}
      />
    )
  }
  else if (!isEmpty(props.value) && props.embedded) {
    return(
      <div id={props.id}>
        <ModalButton
          className="btn btn-sm btn-link"
          title={trans('delete')}
          dangerous={true}
          modal={[MODAL_CONFIRM, {
            dangerous: true,
            icon: 'fa fa-fw fa-trash-o',
            title: transChoice('resources_delete_confirm', 1, {}, 'resource'),
            question: transChoice('resources_delete_message', 1, {count: 1}, 'resource'),
            handleConfirm: () => props.onChange(null)
          }]}
        >
          <span>{trans('delete', {}, 'actions')}</span>
        </ModalButton>

        <ResourceEmbedded
          resourceNode={props.value}
          onResourceClose={props.onEmbeddedResourceClose}
        />
      </div>
    )
  }
  else {
    return (
      <EmptyPlaceholder
        id={props.id}
        size="lg"
        icon="fa fa-folder"
        title={trans('no_resource')}
      >
        <ModalButton
          className="btn"
          primary={true}
          modal={[MODAL_RESOURCES, {
            title: props.picker.title,
            current: props.picker.current,
            root: props.picker.root,
            filters: props.picker.filters,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              label: trans('select', {}, 'actions'),
              callback: () => props.onChange(selected[0])
            })
          }]}
          style={{
            marginTop: '10px' // todo
          }}
        >
          <span className="fa fa-fw fa-hand-pointer-o icon-with-text-right" />
          {trans('add_resource')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}


implementPropTypes(ResourceInput, FormFieldTypes, {
  value: T.shape(
    ResourceNodeTypes.propTypes
  ),
  embedded: T.bool,
  picker: T.shape({
    title: T.string,
    root: T.shape({
      slug: T.string.isRequired,
      name: T.string.isRequired
    }),
    current: T.shape({
      slug: T.string.isRequired,
      name: T.string.isRequired
    }),
    filters: T.object
  }),
  onEmbeddedResourceClose: T.func
}, {
  value: null,
  picker: {
    current: null,
    filters: {},
    root: null
  }
})

export {
  ResourceInput
}
