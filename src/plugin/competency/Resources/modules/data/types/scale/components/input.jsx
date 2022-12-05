import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Scale as ScaleType} from '#/plugin/competency/tools/evaluation/prop-types'
import {MODAL_COMPETENCY_SCALES_PICKER} from '#/plugin/competency/modals/scales'
import {ScaleCard} from '#/plugin/competency/tools/evaluation/data/components/scale-card'

const ScaleInput = props => {
  if (props.value) {
    return(
      <div>
        <ScaleCard
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
          className="btn btn-scale-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_COMPETENCY_SCALES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-arrow-up icon-with-text-right" />
          {trans('scale.select', {}, 'competency')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-arrow-up"
        title={trans('scale.none', {}, 'competency')}
      >
        <ModalButton
          className="btn btn-scale-primary"
          primary={true}
          modal={[MODAL_COMPETENCY_SCALES_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-arrow-up icon-with-text-right" />
          {trans('scale.select', {}, 'competency')}
        </ModalButton>
      </ContentPlaceholder>
    )
  }
}

implementPropTypes(ScaleInput, DataInputTypes, {
  value: T.shape(ScaleType.propTypes),
  picker: T.shape({
    title: T.string,
    confirmText: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('scale.picker', {}, 'competency'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  ScaleInput
}
