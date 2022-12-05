import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Ability as AbilityType} from '#/plugin/competency/tools/evaluation/prop-types'
import {MODAL_ABILITIES_PICKER} from '#/plugin/competency/modals/abilities'
import {AbilityCard} from '#/plugin/competency/tools/evaluation/data/components/ability-card'

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
              icon: 'fa fa-fw fa-trash',
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
          <span className="fa fa-fw fa-atom icon-with-text-right" />
          {trans('ability.select', {}, 'competency')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-atom"
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
          <span className="fa fa-fw fa-atom icon-with-text-right" />
          {trans('ability.select', {}, 'competency')}
        </ModalButton>
      </ContentPlaceholder>
    )
  }
}

implementPropTypes(AbilityInput, DataInputTypes, {
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
