import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ipDefinition} from '#/main/core/layout/data/types/ip'

// TODO : implement IP v6 input

/**
 * Renders an input for a part of an IP address.
 * This input only allow numbers from 0 to 255 or *.
 *
 * @param props
 * @constructor
 */
const IpPartInput = props =>
  <input
    id={props.id}
    type="text"
    className={classes('form-control', props.className, {[`input-${props.size}`]: !!props.size})}
    placeholder={props.placeholder}
    value={props.value}
    onChange={e => {
      // only allow number from 0 to 255 or *
      const regex = /^([0-9]{0,3}|[\\*])$/g
      if (regex.test(e.target.value)) {
        if ('*' !== e.target.value && 255 < e.target.value) {
          props.onChange(255)
        } else {
          props.onChange(e.target.value)
        }

        return true
      }

      return false
    }}
  />

IpPartInput.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  size: T.oneOf(['sm', 'lg']),
  placeholder: T.oneOfType([T.string, T.number]),
  value: T.oneOfType([T.string, T.number]),
  onChange: T.func.isRequired
}

/**
 * Renders an IP v4 input.
 */
class Ip extends Component {
  updatePart(index, partValue) {
    const valueParts = ipDefinition.parse(this.props.value)

    // update part
    valueParts[index] = partValue

    const newIp = ipDefinition.format(valueParts)

    // dispatch change to parent
    this.props.onChange(newIp)
  }

  render() {
    const placeholderParts = ipDefinition.parse(this.props.placeholder)
    const valueParts = ipDefinition.parse(this.props.value)

    return (
      <div id={this.props.id} className="ip-control">
        <IpPartInput
          id={`${this.props.id}-0`}
          size={this.props.size}
          placeholder={placeholderParts[0]}
          value={valueParts[0]}
          onChange={value => this.updatePart(0, value)}
        />

        <span className="dot">.</span>

        <IpPartInput
          id={`${this.props.id}-1`}
          size={this.props.size}
          placeholder={placeholderParts[1]}
          value={valueParts[1]}
          onChange={value => this.updatePart(1, value)}
        />

        <span className="dot">.</span>

        <IpPartInput
          id={`${this.props.id}-2`}
          size={this.props.size}
          placeholder={placeholderParts[2]}
          value={valueParts[2]}
          onChange={value => this.updatePart(2, value)}
        />

        <span className="dot">.</span>

        <IpPartInput
          id={`${this.props.id}-3`}
          size={this.props.size}
          placeholder={placeholderParts[3]}
          value={valueParts[3]}
          onChange={value => this.updatePart(3, value)}
        />
      </div>
    )
  }
}

Ip.propTypes = {
  id: T.string.isRequired,
  placeholder: T.string,
  value: T.string,
  onChange: T.func.isRequired,
  size: T.string
}

Ip.defaultProps = {
  placeholder: '127.0.0.1',
  value: ''
}

export {
  Ip
}
