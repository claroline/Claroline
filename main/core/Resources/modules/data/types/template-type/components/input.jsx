import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {TemplateTypeCard} from '#/main/core/administration/template/data/components/template-type-card'
import {TemplateType as TemplateTypeType} from '#/main/core/administration/template/prop-types'
import {MODAL_TEMPLATE_TYPES} from '#/main/core/modals/template-types'

const TemplateTypeInput = props => {
  if (props.value) {
    return(
      <Fragment>
        <TemplateTypeCard
          data={props.value}
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
          className="btn btn-block"
          style={{marginTop: 10}}
          modal={[MODAL_TEMPLATE_TYPES, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-plus icon-with-text-right" />
          {trans('select_a_template_type', {}, 'template')}
        </ModalButton>
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      icon="fa fa-file-alt"
      title={trans('no_template_type', {}, 'template')}
    >
      <ModalButton
        className="btn btn-block"
        modal={[MODAL_TEMPLATE_TYPES, {
          title: props.picker.title,
          confirmText: props.picker.confirmText,
          selectAction: (selected) => ({
            type: CALLBACK_BUTTON,
            callback: () => props.onChange(selected[0])
          })
        }]}
      >
        <span className="fa fa-fw fa-plus icon-with-text-right" />
        {trans('select_a_template_type', {}, 'template')}
      </ModalButton>
    </EmptyPlaceholder>
  )
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
    title: trans('template_type_selector', {}, 'template')
  }
})

export {
  TemplateTypeInput
}
