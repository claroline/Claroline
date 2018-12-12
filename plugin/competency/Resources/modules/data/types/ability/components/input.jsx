import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Ability as AbilityType} from '#/plugin/competency/administration/competency/prop-types'
import {MODAL_ABILITIES_PICKER} from '#/plugin/competency/modals/abilities'
import {AbilityCard} from '#/plugin/competency/administration/competency/data/components/ability-card'

const AbilityInput = props => {
  if (props.value) {
    return(
      <div>
        <AbilityCard
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
          className="btn btn-ability-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_ABILITIES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-graduation-cap icon-with-text-right" />
          {trans('ability.select', {}, 'competency')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-graduation-cap"
        title={trans('ability.none', {}, 'competency')}
      >
        <ModalButton
          className="btn btn-ability-primary"
          primary={true}
          modal={[MODAL_ABILITIES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-graduation-cap icon-with-text-right" />
          {trans('ability.select', {}, 'competency')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(AbilityInput, FormFieldTypes, {
  value: T.shape(AbilityType.propTypes),
  picker: T.shape({
    title: T.string,
    confirmText: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('ability.picker', {}, 'competency'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  AbilityInput
}
