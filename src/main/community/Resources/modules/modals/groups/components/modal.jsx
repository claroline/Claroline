import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Group as GroupTypes} from '#/main/community/prop-types'
import {GroupList} from '#/main/community/group/components/list'

import {selectors} from '#/main/community/modals/groups/store'

class GroupsModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      initialized: false
    }
  }

  render() {
    const selectAction = this.props.selectAction(this.props.selected)

    return (
      <Modal
        icon="fa fa-fw fa-users"
        {...omit(this.props, 'url', 'selected', 'selectAction', 'reset', 'resetFilters', 'filters')}
        className="data-picker-modal"
        bsSize="lg"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <GroupList
          name={selectors.STORE_NAME}
          url={this.props.url}
          autoload={this.state.initialized}
          primaryAction={undefined}
          actions={undefined}
        />

        <Button
          label={trans('select', {}, 'actions')}
          {...selectAction}
          className="modal-btn btn"
          primary={true}
          disabled={0 === this.props.selected.length}
          onClick={this.props.fadeModal}
        />
      </Modal>
    )
  }
}


GroupsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // list filter types
  })),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(GroupTypes.propTypes)).isRequired,
  reset: T.func.isRequired,
  resetFilters: T.func.isRequired
}

GroupsModal.defaultProps = {
  url: ['apiv2_group_list'],
  title: trans('groups'),
  filters: []
}

export {
  GroupsModal
}
