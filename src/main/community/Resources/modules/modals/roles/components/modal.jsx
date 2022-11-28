import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {constants} from '#/main/community/constants'
import {Role as RoleTypes} from '#/main/community/prop-types'
import {RoleList} from '#/main/community/role/components/list'

import {selectors} from '#/main/community/modals/roles/store'

class RolesModal extends Component {
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
        icon="fa fa-fw fa-id-badge"
        {...omit(this.props, 'url', 'selected', 'selectAction', 'reset', 'resetFilters', 'filters')}
        className="data-picker-modal"
        bsSize="lg"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <RoleList
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

RolesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // list filter types
  })),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(
    T.shape(RoleTypes.propTypes)
  ).isRequired,
  resetFilters: T.func.isRequired,
  reset: T.func.isRequired
}

RolesModal.defaultProps = {
  url: ['apiv2_role_list'],
  title: trans('roles'),
  filters: [
    {property: 'type', value: constants.ROLE_PLATFORM}
  ]
}

export {
  RolesModal
}
