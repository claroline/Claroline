import React, {Component} from 'react'
import classes from 'classnames'
import Overlay from 'react-bootstrap/lib/Overlay'
import {TwitterPicker} from 'react-color'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

class ColorPicker extends Component {
  constructor(props) {
    super(props)
    this.state = {
      open: this.props.autoOpen
    }
  }

  render() {
    return (
      <span className="color-picker" id={this.props.id}>
        <button
          className={classes('btn', this.props.className)}
          role="button"
          type="button"
          ref="target"
          onClick={() => this.setState({open: !this.state.open})}
        >
          <span
            className={classes('fa fa-fw',
              {'fa-font': this.props.forFontColor},
              {'fa-paint-brush': !this.props.forFontColor}
            )}
          />
          <span
            className="color-indicator"
            style={{
              backgroundColor: this.props.value
            }}
          />
        </button>

        <Overlay
          show={this.state.open}
          onHide={() => this.setState({open: false})}
          placement="bottom"
          container={this}
          target={this.refs.target}
          rootClose={true}
        >
          <TwitterPicker
            color={this.props.value}
            colors={this.props.colors}
            onChangeComplete={color => {
              this.setState({open: false})
              this.props.onChange(color)
            }}
          />
        </Overlay>
      </span>
    )
  }
}

implementPropTypes(ColorPicker, FormFieldTypes, {
  // more precise value type
  value: T.string,
  colors: T.arrayOf(T.string),
  forFontColor: T.bool,
  autoOpen: T.bool
}, {
  colors: [
    '#FF6900',
    '#FCB900',
    '#7BDCB5',
    '#00D084',
    '#8ED1FC',
    '#0693E3',
    '#ABB8C3',
    '#EB144C',
    '#FFFFFF',
    '#000000'
  ],
  forFontColor: false,
  autoOpen: false
})

export {
  ColorPicker
}
