import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/rooms/store'
import {RoomCard} from '#/main/core/data/types/room/components/card'

class RoomsModal extends Component {
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
        {...omit(this.props, 'url', 'filters', 'selected', 'selectAction', 'reset', 'resetFilters')}
        icon="fa fa-fw fa-door-open"
        className="data-picker-modal"
        bsSize="lg"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <ListData
          name={selectors.STORE_NAME}
          fetch={{
            url: this.props.url,
            autoload: this.state.initialized
          }}
          definition={[
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code')
            }, {
              name: 'description',
              type: 'html',
              label: trans('description'),
              displayed: true
            }, {
              name: 'capacity',
              type: 'number',
              label: trans('capacity'),
              displayed: true
            }
          ]}
          card={RoomCard}
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

RoomsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.object).isRequired,
  resetFilters: T.func.isRequired,
  reset: T.func.isRequired
}

RoomsModal.defaultProps = {
  url: ['apiv2_location_room_list'],
  title: trans('rooms'),
  filters: []
}

export {
  RoomsModal
}
