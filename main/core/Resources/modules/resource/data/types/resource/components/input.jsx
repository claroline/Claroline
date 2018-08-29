import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element'
import {ResourceEmbedded} from '#/main/core/resource/components/embedded'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {ResourceCard} from '#/main/core/resource/data/components/resource-card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/data/types/resource/prop-types'
import {MODAL_RESOURCE_EXPLORER} from '#/main/core/resource/modals/explorer'

const ResourceInput = props =>
  <div>
    {!isEmpty(props.value) && !props.embedded &&
      <ResourceCard
        data={props.value}
        actions={[
          {
            name: 'replace',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-recycle',
            label: trans('replace', {}, 'actions'),
            modal: [MODAL_RESOURCE_EXPLORER, {
              title: props.picker.title,
              current: props.picker.current,
              root: props.picker.root,
              selectAction: (selected) => ({
                type: 'callback',
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
              dangerous: true,
              icon: 'fa fa-fw fa-trash-o',
              title: trans('resources_delete_confirm'),
              question: trans('resource_delete_message'),
              handleConfirm: () => props.onChange(null)
            }]
          }
        ]}
      />
    }
    {!isEmpty(props.value) && props.embedded &&
      <div className="resource-preview">
        <TooltipElement
          id={`${props.value.id}-delete`}
          position="left"
          tip={trans('delete')}
        >
          <ModalButton
            className="btn btn-danger"
            title={trans('delete')}
            dangerous={true}
            modal={[MODAL_CONFIRM, {
              dangerous: true,
              icon: 'fa fa-fw fa-trash-o',
              title: trans('resources_delete_confirm'),
              question: trans('resource_delete_message'),
              handleConfirm: () => props.onChange(null)
            }]}
          >
            <span className="fa fa-fw fa-trash-o" />
          </ModalButton>
        </TooltipElement>
        <ResourceEmbedded
          resourceNode={props.value}
        />
      </div>
    }
    {isEmpty(props.value) &&
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-folder"
        title={trans('no_resource')}
      >
        <ModalButton
          className="btn btn-resource-primary"
          primary={true}
          modal={[MODAL_RESOURCE_EXPLORER, {
            title: props.picker.title,
            current: props.picker.current,
            root: props.picker.root,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
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
    }
  </div>


implementPropTypes(ResourceInput, FormFieldTypes, {
  value: T.shape(
    ResourceNodeTypes.propTypes
  ),
  embedded: T.bool,
  picker: T.shape({
    title: T.string,
    current: T.shape(ResourceNodeTypes.propTypes),
    root: T.shape(ResourceNodeTypes.propTypes)
  })
}, {
  value: null,
  picker: {
    title: trans('resource_picker'),
    current: null,
    root: null
  }
})

export {
  ResourceInput
}
