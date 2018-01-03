import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import moment from 'moment'

import {ErrorBlock} from '#/main/core/layout/form/components/error-block.jsx'
import {CheckboxesGroup} from '#/main/core/layout/form/components/group/checkboxes-group.jsx'
import {CountryGroup} from '#/main/core/layout/form/components/group/country-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {CascadeSelectGroup} from '#/main/core/layout/form/components/group/cascade-select-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {RadioGroup} from '#/main/core/layout/form/components/group/radio-group.jsx'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'
import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {EmailGroup} from '#/main/core/layout/form/components/group/email-group.jsx'
import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'
import {FileGroup} from '#/main/core/layout/form/components/group/file-group.jsx'

import {Radios} from '#/main/core/layout/form/components/field/radios.jsx'
import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes.jsx'
import {CascadeSelect} from '#/main/core/layout/form/components/field/cascade-select.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'
import {DatePicker} from '#/main/core/layout/form/components/field/date-picker.jsx'
import {File} from '#/main/core/layout/form/components/field/file.jsx'

const isCascadeSelect = choices => {
  return choices.filter(c => c.parent).length > 0
}

// deprecated
// only used by claco-form
// todo : rewrite claco-form to use form.jsx
export const FormField = props => {
  switch (props.type) {
    case 'checkboxes':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <Checkboxes
            groupName={props.controlId}
            inline={true}
            options={props.choices || []}
            checkedValues={props.value || []}
            disabled={props.disabled}
            error={props.error}
            onChange={value => props.onChange(value)}
          />
          {props.error &&
            <ErrorBlock text={props.error}/>
          }
        </div> :
        <CheckboxesGroup
          controlId={props.controlId}
          label={props.label}
          options={props.choices || []}
          checkedValues={props.value || []}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    case 'radio':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <Radios
            groupName={props.controlId}
            inline={true}
            options={props.choices || []}
            checkedValue={props.value || ''}
            disabled={props.disabled}
            onChange={value => props.onChange(value)}
          />
          {props.error &&
            <ErrorBlock text={props.error}/>
          }
        </div> :
        <RadioGroup
          id={props.controlId}
          label={props.label}
          options={props.choices || []}
          checkedValue={props.value || ''}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    case 'select':
      if (props.choices && isCascadeSelect(props.choices)) {
        return (props.noLabel ?
          <div className={classes({'has-error': props.error})}>
            <CascadeSelect
              options={props.choices || []}
              selectedValue={props.value || []}
              disabled={props.disabled}
              onChange={props.onChange}
            />
            {props.error &&
              <ErrorBlock text={props.error}/>
            }
          </div> :
          <CascadeSelectGroup
            controlId={props.controlId}
            label={props.label}
            options={props.choices || []}
            selectedValue={props.value || []}
            disabled={props.disabled}
            error={props.error}
            onChange={value => props.onChange(value)}
          />
        )
      } else {
        return (
          <SelectGroup
            id={props.controlId}
            label={props.label}
            hideLabel={props.noLabel}
            choices={props.choices || {}}
            value={props.value || ''}
            disabled={props.disabled}
            error={props.error}
            multiple={false}
            onChange={value => props.onChange(value)}
          />
        )
      }
    case 'country':
      return (
        <CountryGroup
          id={props.controlId}
          label={props.label}
          hideLabel={props.noLabel}
          value={props.value || ''}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    case 'text':
      return (
        <TextGroup
          id={props.controlId}
          label={props.label}
          hideLabel={props.noLabel}
          value={props.value || ''}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    case 'number':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <input
            id={props.controlId}
            type="number"
            className="form-control"
            value={props.value === null || isNaN(props.value) ? undefined : props.value}
            disabled={props.disabled}
            onChange={(e) => props.onChange(e.target.value)}
          />
          {props.error &&
            <ErrorBlock text={props.error}/>
          }
        </div> :
        <NumberGroup
          id={props.controlId}
          label={props.label}
          value={props.value === null ? undefined : props.value}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'email':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <input
            id={props.controlId}
            type="email"
            className="form-control"
            value={props.value || ''}
            disabled={props.disabled}
            onChange={(e) => props.onChange(e.target.value)}
          />
          {props.error &&
            <ErrorBlock text={props.error}/>
          }
        </div> :
        <EmailGroup
          controlId={props.controlId}
          label={props.label}
          value={props.value || undefined}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'rich_text':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <Textarea
            id={props.controlId}
            value={props.value || ''}
            minRows={2}
            disabled={props.disabled}
            onChange={props.onChange}
          />
          {props.error &&
            <ErrorBlock text={props.error}/>
          }
        </div> :
        <HtmlGroup
          id={props.controlId}
          label={props.label}
          value={props.value}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'date':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <DatePicker
            controlId={props.controlId}
            dateFormat="DD/MM/YYYY"
            minDate={moment.utc('1900-01-01T12:00:00')}
            value={props.value !== undefined && props.value !== null ? props.value.date || props.value || '' : ''}
            disabled={props.disabled}
            onChange={props.onChange}
          />
          {props.error &&
          <ErrorBlock text={props.error}/>
          }
        </div> :
        <DateGroup
          id={props.controlId}
          label={props.label}
          dateFormat="DD/MM/YYYY"
          minDate={moment.utc('1900-01-01T12:00:00')}
          value={props.value !== undefined && props.value !== null ? props.value.date || props.value || '' : ''}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'file':
      return (props.noLabel ?
        <div className={classes({'has-error': props.error})}>
          <File
            controlId={props.controlId}
            value={props.value || []}
            types={props.types || []}
            max={props.max}
            disabled={props.disabled}
            onChange={value => props.onChange(value)}
          />
          {props.error &&
            <ErrorBlock text={props.error}/>
          }
        </div> :
        <FileGroup
          controlId={props.controlId}
          label={props.label}
          value={props.value || []}
          types={props.types || []}
          max={props.max}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    default:
      return null
  }
}

FormField.propTypes = {
  controlId: T.string.isRequired,
  type: T.string.isRequired,
  label: T.string.isRequired,
  value: T.any,
  choices: T.array,
  noLabel: T.bool.isRequired,
  disabled: T.bool.isRequired,
  error: T.string,
  onChange: T.func.isRequired,
  min: T.number,
  max: T.number,
  types: T.array
}

FormField.defaultProps = {
  noLabel: false,
  disabled: false
}
