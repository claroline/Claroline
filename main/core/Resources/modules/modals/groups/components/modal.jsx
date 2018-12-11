import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/groups/store'
import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {Group as GroupType} from '#/main/core/user/prop-types'

const GroupsPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-users"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={GroupList.definition}
        card={GroupList.card}
        display={props.display}
      />

      <Button
        label={trans('select', {}, 'actions')}
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
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(GroupType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

GroupsPickerModal.defaultProps = {
  url: ['apiv2_group_list_registerable'], //apiv2_group_list_registerable = filter by current user organizations
  title: trans('groups_picker')
}

export {
  GroupsPickerModal
}
