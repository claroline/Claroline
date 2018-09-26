import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/core/translation'
import {selectors} from '#/main/core/modals/groups/store'
import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {Group as GroupType} from '#/main/core/user/prop-types'

const GroupsPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      className="groups-picker-modal"
      icon="fa fa-fw fa-users"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_group_list_managed'],
          autoload: true
        }}
        definition={GroupList.definition}
        card={GroupList.card}
        display={props.display}
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

GroupsPickerModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(GroupType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

GroupsPickerModal.defaultProps = {
  title: trans('groups_picker'),
  confirmText: trans('select', {}, 'actions')
}

export {
  GroupsPickerModal
}
