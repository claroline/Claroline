import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {ModalButton} from '#/main/app/button'

import {ResourceCard} from '#/main/core/resource/data/components/resource-card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/data/types/resource/prop-types'

const ResourceInput = props => !isEmpty(props.value) ?
  <ResourceCard
    data={props.data}
    actions={[
      {
        name: 'replace',
        type: 'modal',
        icon: 'fa fa-fw fa-recycle',
        label: trans('replace', {}, 'actions'),
        modal: []
      }, { // todo confirm
        name: 'delete',
        type: 'callback',
        icon: 'fa fa-fw fa-recycle',
        label: trans('delete', {}, 'actions'),
        dangerous: true,
        callback: () => props.onChange(null)
      }
    ]}
  /> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-folder"
    title={trans('no_resource')}
  >
    <ModalButton
      className="btn btn-resource-primary"
      primary={true}
      modal={[]}
      style={{
        marginTop: '10px' // todo
      }}
    >
      <span className="fa fa-fw fa-hand-pointer-o icon-with-text-right" />
      {trans('add_resource')}
    </ModalButton>
  </EmptyPlaceholder>

implementPropTypes(ResourceInput, FormFieldTypes, {
  value: T.shape(
    ResourceNodeTypes.propTypes
  )
}, {
  value: null
})

export {
  ResourceInput
}