import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {route} from '#/main/core/administration/routing'

import {TemplateCard} from '#/main/core/data/types/template/components/card'
import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'
import {MODAL_TEMPLATES} from '#/main/core/modals/templates'

const TemplateButton = (props) =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_template', {}, 'template')}
    disabled={props.disabled}
    modal={[MODAL_TEMPLATES, {
      title: props.title,
      filters: props.filters,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

TemplateButton.propTypes = {
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const TemplateInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <TemplateCard
          size="xs"
          data={props.value}
          primaryAction={{
            type: LINK_BUTTON,
            label: trans('open', {}, 'actions'),
            target: route('templates')+'/form/'+props.value.id
          }}
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

        <TemplateButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
          size={props.size}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-file-alt"
      title={trans('no_template', {}, 'template')}
      size={props.size}
    >
      <TemplateButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
        size={props.size}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(TemplateInput, DataInputTypes, {
  value: T.shape(
    TemplateTypes.propTypes
  ),
  picker: T.shape({
    title: T.string,
    confirmText: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('templates', {}, 'template')
  }
})

export {
  TemplateInput
}
