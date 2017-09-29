import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Modal from 'react-bootstrap/lib/Modal'

import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'

class GenericTypePicker extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentType: props.types[0],
      currentName: props.types[0].name,
      currentDesc: props.types[0].description
    }
  }

  handleItemMouseOver(type) {
    this.setState({
      currentType: type,
      currentName: type.name,
      currentDesc: type.description
    })
  }

  render() {
    return (
      <BaseModal
        {...this.props}
        className="generic-type-picker"
      >
        <Modal.Body>
          <div className="types-list" role="listbox">
            {this.props.types.map((type, index) =>
              <div
                key={`type-${index}`}
                className={classes('type-entry', {'selected': this.state.currentType === type})}
                role="option"
                onMouseOver={() => this.handleItemMouseOver(type)}
                onClick={() => this.props.handleSelect(type)}
              >
                {typeof type.icon === 'string' ?
                  <span className={classes('type-icon', type.icon)} /> :
                  type.icon
                }
              </div>
            )}
          </div>

          <div className="type-desc">
            <span className="type-name">{this.state.currentName}</span>

            {this.state.currentDesc &&
              <p>{this.state.currentDesc}</p>
            }
          </div>
        </Modal.Body>
      </BaseModal>
    )
  }
}

GenericTypePicker.propTypes = {
  types: T.arrayOf(T.shape({
    name: T.string.isRequired,
    icon: T.node.isRequired, // either a FontAwesome class string or a custom icon component
    description: T.string
  })).isRequired,
  handleSelect: T.func.isRequired
}

export {
  GenericTypePicker
}
