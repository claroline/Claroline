import React, {Component, PropTypes as T} from 'react'
import {TwitterPicker} from 'react-color'

export class ColorPicker extends Component {
  constructor(props) {
    super(props)
    this.state = {
      open: false
    }
  }

  render() {
    return (
      <span className="color-picker" id={this.props.id}>
        <span
          role="button"
          style={{
            position: 'relative',
            backgroundColor: this.props.color
          }}
          onClick={() => this.setState({open: !this.state.open})}
        >
          {this.state.open &&
            <span style={{
              position: 'absolute',
              left: '-14px',
              top: '30px'
            }}>
              <TwitterPicker
                color={this.props.color}
                onChangeComplete={color => {
                  this.setState({open: false})
                  this.props.onPick(color)
                }}
              />
            </span>
          }
        </span>
      </span>
    )
  }
}

ColorPicker.propTypes = {
  color: T.string.isRequired,
  onPick: T.func.isRequired,
  id: T.string
}
