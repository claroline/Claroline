import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource/routing'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ResourceCard} from '#/main/core/resource/components/card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            dangerous: true,
            disabled: props.disabled,
            callback: () => props.onChange(null)
          }
        ]}
      />
    )
  }
  else if (!isEmpty(props.value) && props.embedded) {
    return (
      <div id={props.id} className="position-relative">
        <Button
          className="position-absolute bottom-100 end-0 text-lowercase"
          variant="btn-text"
          type={CALLBACK_BUTTON}
          label={trans('delete', {}, 'actions')}
          dangerous={true}
          size="sm"
          disabled={props.disabled}
          callback={() => props.onChange(null)}
        />

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
      <Button
        className="btn btn-outline-primary w-100 mt-2"
        type={MODAL_BUTTON}
        icon="fa fa-fw fa-plus"
        label={trans('add_resource', {}, 'resource')}
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
        size={props.size}
        disabled={props.disabled}
      />
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
