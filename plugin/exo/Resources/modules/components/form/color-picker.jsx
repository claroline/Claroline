import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {TwitterPicker} from 'react-color'
import Overlay from 'react-bootstrap/lib/Overlay'

export class ColorPicker extends Component {
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
              backgroundColor: this.props.color
            }}
          />
        </button>

        <Overlay
          show={this.state.open}
          onHide={() => this.setState({ open: false })}
          placement="bottom"
          container={this}
          target={this.refs.target}
          rootClose={true}
        >
          <TwitterPicker
            color={this.props.color}
            colors={this.props.colors}
            onChangeComplete={color => {
              this.setState({open: false})
              this.props.onPick(color)
            }}
          />
        </Overlay>
      </span>
    )
  }
}

ColorPicker.defaultProps = {
  forFontColor: false,
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
  autoOpen: false
}

ColorPicker.propTypes = {
  color: T.string.isRequired,
  onPick: T.func.isRequired,
  id: T.string,
  colors: T.arrayOf(T.string),
  forFontColor: T.bool,
  className: T.string,
  autoOpen: T.bool
}
