import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Checkbox} from '#/main/core/layout/form/components/field/checkbox.jsx'

/**
 * Renders an activable fieldset.
 */
class ActivableSet extends Component {
  constructor(props) {
    super(props)

    this.state = {
      activated: props.activated
    }

    this.toggle = this.toggle.bind(this)
  }

  toggle(checked) {
    this.setState({activated: checked})
    this.props.onChange(checked)
  }

  render() {
    return (
      <div className="activable-set">
        <Checkbox
          id={this.props.id}
          label={this.props.label}
          labelChecked={this.props.labelActivated}
          checked={this.state.activated}
          onChange={this.toggle}
        />

        {this.state.activated &&
          <div className="sub-fields">
            {this.props.children}
          </div>
        }
      </div>
    )
  }
}

ActivableSet.propTypes = {
  id: T.string.isRequired,
  label: T.string.isRequired,
  labelActivated: T.string,
  activated: T.bool,
  onChange: T.func,
  children: T.any.isRequired
}

ActivableSet.defaultProps = {
  activated: false,
  onChange: () => true
}

export {
  ActivableSet
}
