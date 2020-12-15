import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'

class Player extends Component {
  componentDidMount() {
    if (this.props.url.mode === 'redirect') {
      window.location.href = this.props.url.url

      return
    }

    if (this.props.url.mode === 'tab') {
      window.open(this.props.url.url,'_blank')
    }
  }

  render() {
    if ('iframe' === this.props.url.mode) {
      return (
        <div
          className="claro-iframe-content-container"
          style={this.props.url.ratio ?
            {
              position: 'relative',
              paddingBottom: `${this.props.url.ratio}%`
            } :
            {}
          }
        >
          <iframe
            className="claro-iframe"
            src={this.props.url.url}
          />
        </div>
      )
    }

    return (
      <Button
        type={URL_BUTTON}
        className="btn btn-block btn-emphasis component-container"
        target={this.props.url.url}
        style={{
          marginTop: 20 // FIXME
        }}
        label={trans('open', {}, 'actions')}
        primary={true}
      />
    )
  }
}

Player.propTypes = {
  url: T.shape({
    id: T.number.isRequired,
    url: T.string.isRequired,
    mode: T.string.isRequired,
    ratio: T.number.isRequired
  }).isRequired
}

export {
  Player
}
