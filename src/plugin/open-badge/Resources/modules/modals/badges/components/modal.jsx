import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import badgeSource from '#/plugin/open-badge/data/sources/badges'
import {selectors} from '#/plugin/open-badge/modals/badges/store'

class BadgesModal extends Component {
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
        {...omit(this.props, 'url', 'selected', 'selectAction', 'reset', 'resetFilters', 'filters')}
        icon="fa fa-fw fa-trophy"
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
          definition={badgeSource.parameters.definition}
          card={badgeSource.parameters.card}
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

BadgesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(
    T.object
  ).isRequired,
  resetFilters: T.func.isRequired,
  reset: T.func.isRequired
}

BadgesModal.defaultProps = {
  url: ['apiv2_badge-class_list'],
  title: trans('badges', {}, 'badge'),
  filters: []
}

export {
  BadgesModal
}
