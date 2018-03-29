import React, {Component} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {IPv4} from '#/main/core/scaffolding/ip'

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
    disabled={props.disabled}
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
  disabled: T.bool.isRequired,
  value: T.oneOfType([T.string, T.number]),
  onChange: T.func.isRequired
}

/**
 * Renders an IP v4 input.
 */
class Ip extends Component {
  updatePart(index, partValue) {
    const valueParts = IPv4.parse(this.props.value)

    // update part
    valueParts[index] = partValue

    const newIp = IPv4.format(valueParts)

    // dispatch change to parent
    this.props.onChange(newIp)
  }

  render() {
    const placeholderParts = IPv4.parse(this.props.placeholder)
    const valueParts = IPv4.parse(this.props.value)

    return (
      <div id={this.props.id} className="ip-control">
        <IpPartInput
          id={`${this.props.id}-0`}
          size={this.props.size}
          placeholder={placeholderParts[0]}
          value={valueParts[0]}
          disabled={this.props.disabled}
          onChange={value => this.updatePart(0, value)}
        />

        <span className="dot">.</span>

        <IpPartInput
          id={`${this.props.id}-1`}
          size={this.props.size}
          placeholder={placeholderParts[1]}
          value={valueParts[1]}
          disabled={this.props.disabled}
          onChange={value => this.updatePart(1, value)}
        />

        <span className="dot">.</span>

        <IpPartInput
          id={`${this.props.id}-2`}
          size={this.props.size}
          placeholder={placeholderParts[2]}
          value={valueParts[2]}
          disabled={this.props.disabled}
          onChange={value => this.updatePart(2, value)}
        />

        <span className="dot">.</span>

        <IpPartInput
          id={`${this.props.id}-3`}
          size={this.props.size}
          placeholder={placeholderParts[3]}
          value={valueParts[3]}
          disabled={this.props.disabled}
          onChange={value => this.updatePart(3, value)}
        />
      </div>
    )
  }
}

implementPropTypes(Ip, FormFieldTypes, {
  value: T.string,
  placeholder: T.string,
  size: T.string
}, {
  value: '',
  placeholder: '127.0.0.1'
})

export {
  Ip
}
