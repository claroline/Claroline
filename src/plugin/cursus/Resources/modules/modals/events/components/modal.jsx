import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Event as EventTypes} from '#/plugin/cursus/prop-types'

import {selectors} from '#/plugin/cursus/modals/events/store'
import {EventList} from '#/plugin/cursus/event/components/list'
import {constants as listConst} from '#/main/app/content/list/constants'

class EventsModal extends Component {
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
        {...omit(this.props, 'url', 'selected', 'selectAction', 'reset', 'resetFilters')}
        icon="fa fa-fw fa-calendar-week"
        className="data-picker-modal"
        size="xl"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <EventList
          name={selectors.STORE_NAME}
          url={this.props.url}
          autoload={this.state.initialized}
          primaryAction={undefined}
          actions={undefined}
          delete={undefined}
          display={{
            current: listConst.DISPLAY_TABLE
          }}
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

EventsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.object),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(EventTypes.propTypes)).isRequired,
  reset: T.func.isRequired,
  resetFilters: T.func.isRequired
}

EventsModal.defaultProps = {
  url: ['apiv2_cursus_event_list'],
  title: trans('session_events', {}, 'cursus'),
  filters: []
}

export {
  EventsModal
}
