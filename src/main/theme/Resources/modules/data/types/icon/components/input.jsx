import React, {Component, forwardRef} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {Menu} from '#/main/app/overlays/menu'
import {IconCollection} from '#/main/theme/icon/containers/collection'

const IconMenu = forwardRef((props, ref) =>
  <div {...omit(props, 'id', 'value', 'onChange', 'show', 'close')} ref={ref}>
    <IconCollection
      id={props.id}
      selected={props.value}
      onChange={props.onChange}
    />
  </div>
)

IconMenu.propTypes = {
  id: T.string,
  value: T.string,
  onChange: T.func.isRequired
}

class IconInput extends Component {
  constructor(props) {
    super(props)

    this.onInputChange = this.onInputChange.bind(this)
  }

  onInputChange(e) {
    this.props.onChange(e.target.value)
  }

  renderPickerButton(className) {
    return (
      <Button
        className={classes('btn btn-outline-secondary', className)}
        type={MENU_BUTTON}
        icon={`fa fa-fw fa-${this.props.value}`}
        label={trans('show-icons', {}, 'actions')}
        tooltip="right"
        size={this.props.size}
        disabled={this.props.disabled}
        menu={
          <Menu
            as={IconMenu}
            id={this.props.id}
            selected={this.props.value}
            onChange={this.props.onChange}
          />
        }
      />
    )
  }

  render() {
    if (this.props.hideInput) {
      return this.renderPickerButton(this.props.className)
    }

    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        {this.renderPickerButton('rounded-end-0')}

        <input
          id={this.props.id+'-input'}
          type="text"
          autoComplete={this.props.autoComplete}
          className="form-control"
          placeholder={this.props.placeholder}
          value={this.props.value || ''}
          disabled={this.props.disabled}
          onChange={this.onInputChange}
        />
      </div>
    )
  }
}

implementPropTypes(IconInput, DataInputTypes, {
  // more precise value type
  value: T.string,

  // custom options
  hideInput: T.bool
}, {
  hideInput: false
})

export {
  IconInput
}
