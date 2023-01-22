import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/competency/modals/competencies/store'
import {Competency as CompetencyType} from '#/plugin/competency/tools/evaluation/prop-types'
import {CompetencyCard} from '#/plugin/competency/tools/evaluation/data/components/competency-card'

const CompetenciesPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-atom"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.resetSelect}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_competency_list'],
          autoload: true
        }}
        definition={[
          {
            name: 'name',
            label: trans('name'),
            displayed: true,
            type: 'string',
            primary: true
          }, {
            name: 'description',
            label: trans('description'),
            displayed: true,
            type: 'html'
          }
        ]}
        card={CompetencyCard}
      />

      <Button
        label={props.confirmText}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

CompetenciesPickerModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(CompetencyType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

CompetenciesPickerModal.defaultProps = {
  title: trans('competencies.picker', {}, 'competency'),
  confirmText: trans('select', {}, 'actions')
}

export {
  CompetenciesPickerModal
}
