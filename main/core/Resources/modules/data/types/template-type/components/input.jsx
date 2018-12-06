import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {TemplateTypeCard} from '#/main/core/administration/template/data/components/template-type-card'
import {TemplateType as TemplateTypeType} from '#/main/core/administration/template/prop-types'
import {MODAL_TEMPLATE_TYPES_PICKER} from '#/main/core/modals/template-types'

const TemplateTypeInput = props => {
  if (props.value) {
    return(
      <div>
        <TemplateTypeCard
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
              callback: () => props.onChange(null)
            }
          ]}
        />
        <ModalButton
          className="btn btn-template-types-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_TEMPLATE_TYPES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-file-alt icon-with-text-right" />
          {trans('select_a_template_type', {}, 'template')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-file-alt"
        title={trans('no_template_type', {}, 'template')}
      >
        <ModalButton
          className="btn btn-template-types-primary"
          primary={true}
          modal={[MODAL_TEMPLATE_TYPES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-file-alt icon-with-text-right" />
          {trans('select_a_template_type', {}, 'template')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(TemplateTypeInput, FormFieldTypes, {
  value: T.shape(TemplateTypeType.propTypes),
  picker: T.shape({
    title: T.string,
    confirmText: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('template_type_selector', {}, 'template'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  TemplateTypeInput
}
