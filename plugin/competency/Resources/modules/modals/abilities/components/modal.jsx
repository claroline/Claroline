import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/competency/modals/abilities/store'
import {Ability as AbilityType} from '#/plugin/competency/administration/competency/prop-types'
import {AbilityList} from '#/plugin/competency/administration/competency/framework/components/ability-list'

const AbilitiesPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-graduation-cap"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.resetSelect}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_ability_list'],
          autoload: true
        }}
        definition={AbilityList.definition}
        card={AbilityList.card}
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

AbilitiesPickerModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(AbilityType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

AbilitiesPickerModal.defaultProps = {
  title: trans('abilities.picker', {}, 'competency'),
  confirmText: trans('select', {}, 'actions')
}

export {
  AbilitiesPickerModal
}
