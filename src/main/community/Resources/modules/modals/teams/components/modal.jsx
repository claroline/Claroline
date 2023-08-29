import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Team as TeamTypes} from '#/main/community/prop-types'
import {TeamList} from '#/main/community/team/components/list'

import {selectors} from '#/main/community/modals/teams/store'

class TeamsModal extends Component {
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
        icon="fa fa-fw fa-user-group"
        {...omit(this.props, 'url', 'selected', 'selectAction', 'reset', 'resetFilters', 'filters')}
        className="data-picker-modal"
        size="xl"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <TeamList
          name={selectors.STORE_NAME}
          url={this.props.url}
          autoload={this.state.initialized}
          primaryAction={undefined}
          actions={undefined}
        />

        <Button
          label={trans('select', {}, 'actions')}
          {...selectAction}
          className="modal-btn"
          variant="btn"
          size="lg"
          primary={true}
          disabled={0 === this.props.selected.length}
          onClick={this.props.fadeModal}
        />
      </Modal>
    )
  }
}

TeamsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // list filter types
  })),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  selected: T.arrayOf(
    T.shape(TeamTypes.propTypes)
  ).isRequired,
  reset: T.func.isRequired,
  resetFilters: T.func.isRequired
}

TeamsModal.defaultProps = {
  url: ['apiv2_team_list'],
  title: trans('teams'),
  filters: []
}

export {
  TeamsModal
}
