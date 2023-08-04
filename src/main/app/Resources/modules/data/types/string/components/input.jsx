import React, {PureComponent} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

class StringInput extends PureComponent {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
  }

  onChange(e) {
    if (!this.props.maxLength || e.target.value.length <= this.props.maxLength) {
      this.props.onChange(e.target.value)
    }
  }

  render() {
    const commonProps = {
      id: this.props.id,
      className: classes('form-control', this.props.className, {
        [`form-control-${this.props.size}`]: !!this.props.size
      }),
      value: this.props.value || '',
      disabled: this.props.disabled,
      onChange: this.onChange,
      placeholder: this.props.placeholder,
      autoComplete: this.props.autoComplete
    }

    const charsTyped = this.props.value ? this.props.value.length : 0
    const minLength = this.props.minLength
    const maxLength = this.props.maxLength

    if (this.props.long) {
      return (
        <div>
          <textarea
            {...commonProps}
            rows={this.props.minRows}
          />
        </div>
      )
    }

    return (
      <div>
        <input
          {...commonProps}
          type="text"
        />

        {(minLength || maxLength) && charsTyped !== 0 &&
          <div className="form-text chars-remaining">
            {charsTyped} {
              trans('characters_typed', {}, 'platform')
            }
          </div>
        }

        <div className="form-text mb-2">
          {this.props.minLength &&
            <div className="form-text">
              {minLength} {trans('charsMin_length', {}, 'platform')}
            </div>
          }

          {this.props.maxLength &&
            <div className="form-text">
              {maxLength} {trans('charsMax_length', {}, 'platform')}
            </div>
          }
        </div>
      </div>
    )
  }
}

implementPropTypes(StringInput, DataInputTypes, {
  value: T.string,
  long: T.bool,
  minRows: T.number,
  minLength: T.number,
  maxLength: T.number
}, {
  value: '',
  long: false,
  minRows: 4
})

export {
  StringInput
}
