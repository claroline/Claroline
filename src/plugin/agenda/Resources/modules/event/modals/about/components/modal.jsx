import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'

import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {getEvent} from '#/plugin/agenda/events'

class AboutModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customAbout: null
    }
  }

  componentDidMount() {
    getEvent(this.props.event.meta.type).then((eventApp) => {
      this.setState({customAbout: get(eventApp, 'components.about', null)})
    })
  }

  renderCustomAbout() {
    if (this.state.customAbout) {
      return createElement(this.state.customAbout, {
        event: this.props.event,
        reload: (event) => {
          this.props.reload(event)
          this.props.fadeModal()
        }
      })
    }

    return null
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'event', 'reload')}
        icon="fa fa-fw fa-circle-info"
        title={this.props.event.name}
        subtitle={trans('about')}
        poster={this.props.event.thumbnail ? this.props.event.thumbnail : undefined}
      >
        {this.renderCustomAbout()}
      </Modal>
    )
  }
}
  

AboutModal.propTypes = {
  event: T.shape(
    EventTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  AboutModal
}
