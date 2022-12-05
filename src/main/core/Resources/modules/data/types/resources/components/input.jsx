import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource/routing'
import {ResourceCard} from '#/main/core/resource/components/card'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'

const ResourcesButton = props =>
  <Button
    type={MODAL_BUTTON}
    className="btn btn-block"
    icon="fa fa-fw fa-plus"
    label={trans('add_resources', {}, 'resource')}
    modal={[MODAL_RESOURCES, {
      title: props.title,
      current: props.current,
      root: props.root,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
    style={{
      marginTop: '10px' // todo
    }}
    size={props.size}
    disabled={props.disabled}
  />

ResourcesButton.propTypes = {
  title: T.string,
  root: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  current: T.shape({
    slug: T.string.isRequired,
    name: T.string.isRequired
  }),
  onChange: T.func.isRequired,
  size: T.string,
  disabled: T.bool
}

const ResourcesInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <Fragment>
        {props.value.map(resource =>
          <ResourceCard
            key={`resource-card-${resource.id}`}
            data={resource}
            size="xs"
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: route(resource)
            }}
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                disabled: props.disabled,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(r => r.id === resource.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <ResourcesButton
          {...props.picker}
          size={props.size}
          disabled={props.disabled}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(resource => {
              const index = newValue.findIndex(r => r.id === resource.id)

              if (-1 === index) {
                newValue.push(resource)
              }
            })
            props.onChange(newValue)
          }}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      id={props.id}
      icon="fa fa-folder"
      title={trans('no_resource', {}, 'resource')}
      size={props.size}
    >
      <ResourcesButton
        {...props.picker}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}


implementPropTypes(ResourcesInput, DataInputTypes, {
  value: T.arrayOf(T.shape(
    ResourceNodeTypes.propTypes
  )),
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
  })
}, {
  value: null,
  picker: {
    current: null,
    root: null,
    filters: {}
  }
})

export {
  ResourcesInput
}
