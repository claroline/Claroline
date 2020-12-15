import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'

class UrlDisplay extends Component {
  componentDidMount() {
    if (this.props.mode === 'redirect') {
      window.location.href = this.props.url

      return
    }

    if (this.props.mode === 'tab') {
      window.open(this.props.url,'_blank')
    }
  }

  render() {
    if ('iframe' === this.props.mode) {
      return (
        <div
          className="claro-iframe-content-container"
          style={this.props.ratio ? {
            position: 'relative',
            paddingBottom: `${this.props.ratio}%`
          } : {}}
        >
          <iframe className="claro-iframe" src={this.props.url} />
        </div>
      )
    }

    return (
      <Button
        type={URL_BUTTON}
        className="btn btn-block btn-emphasis component-container"
        target={this.props.url}
        style={{
          marginTop: 20 // FIXME
        }}
        label={trans('open', {}, 'actions')}
        primary={true}
      />
    )
  }
}

UrlDisplay.propTypes = {
  url: T.string.isRequired,
  mode: T.string.isRequired,
  ratio: T.number.isRequired
}

export {
  UrlDisplay
}
