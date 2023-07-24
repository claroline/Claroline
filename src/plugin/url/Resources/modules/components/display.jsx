import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {ContentIFrame} from '#/main/app/content/components/iframe'
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
        <ContentIFrame
          className="row"
          url={this.props.url}
          ratio={this.props.ratio}
        />
      )
    }

    return (
      <Button
        type={URL_BUTTON}
        className="btn btn-primary w-100 mt-3"
        target={this.props.url}
        label={trans('open', {}, 'actions')}
        primary={true}
        size="lg"
      />
    )
  }
}

UrlDisplay.propTypes = {
  url: T.string.isRequired,
  mode: T.string.isRequired,
  ratio: T.number
}

export {
  UrlDisplay
}
