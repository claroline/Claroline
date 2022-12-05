import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {TemplateTypeCard} from '#/main/core/data/types/template-type/components/card'
import {TemplateType as TemplateTypeTypes} from '#/main/core/data/types/template-type/prop-types'
import {MODAL_TEMPLATE_TYPES} from '#/main/core/modals/template-types'

const TemplateTypeButton = (props) =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_template_type', {}, 'template')}
    disabled={props.disabled}
    modal={[MODAL_TEMPLATE_TYPES, {
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

TemplateTypeButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const TemplateTypeInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <TemplateTypeCard
          size="xs"
          data={props.value}
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <TemplateTypeButton
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
      title={trans('no_template_type', {}, 'template')}
      size={props.size}
    >
      <TemplateTypeButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
        size={props.size}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(TemplateTypeInput, DataInputTypes, {
  value: T.shape(
    TemplateTypeTypes.propTypes
  ),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {}
})

export {
  TemplateTypeInput
}
