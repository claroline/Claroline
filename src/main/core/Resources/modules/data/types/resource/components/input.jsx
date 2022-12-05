import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {route} from '#/main/core/resource/routing'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ResourceCard} from '#/main/core/resource/components/card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

// TODO : manage disabled state

const ResourceInput = props => {
  if (!isEmpty(props.value) && !props.embedded) {
    return (
      <ResourceCard
        data={props.value}
        size="xs"
        primaryAction={{
          type: LINK_BUTTON,
          label: trans('open', {}, 'actions'),
          target: route(props.value)
        }}
        actions={[
          {
            name: 'replace',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-recycle',
            label: trans('replace', {}, 'actions'),
            disabled: props.disabled,
            modal: [MODAL_RESOURCES, {
              title: props.picker.title,
              current: props.picker.current,
              root: props.picker.root,
              filters: props.picker.filters,
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('select', {}, 'actions'),
                callback: () => props.onChange(selected[0])
              })
            }]
          }, {
            name: 'delete',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            dangerous: true,
            disabled: props.disabled,
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
    return (
      <div id={props.id}>
        <ModalButton
          className="btn btn-sm btn-link"
          title={trans('delete')}
          dangerous={true}
          disabled={props.disabled}
          modal={[MODAL_CONFIRM, {
            dangerous: true,
            icon: 'fa fa-fw fa-trash',
            title: transChoice('resources_delete_confirm', 1, {}, 'resource'),
            question: transChoice('resources_delete_message', 1, {count: 1}, 'resource'),
            handleConfirm: () => props.onChange(null)
          }]}
        >
          <span>{trans('delete', {}, 'actions')}</span>
        </ModalButton>

        <ResourceEmbedded
          resourceNode={props.value}
        />
      </div>
    )
  }

  return (
    <ContentPlaceholder
      id={props.id}
      icon="fa fa-folder"
      title={trans('no_resource', {}, 'resource')}
      size={props.size}
    >
      <ModalButton
        className="btn btn-block"
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
        size={props.size}
        disabled={props.disabled}
      >
        <span className="fa fa-fw fa-plus icon-with-text-right" />
        {trans('add_resource', {}, 'resource')}
      </ModalButton>
    </ContentPlaceholder>
  )
}

implementPropTypes(ResourceInput, DataInputTypes, {
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
    filters: T.array
  })
}, {
  value: null,
  picker: {
    current: null,
    filters: [],
    root: null
  }
})

export {
  ResourceInput
}
