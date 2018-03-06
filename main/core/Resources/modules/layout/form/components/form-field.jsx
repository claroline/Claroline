import React from 'react'
import {PropTypes as T} from 'prop-types'

import {CheckboxesGroup} from '#/main/core/layout/form/components/group/checkboxes-group.jsx'
import {CountryGroup} from '#/main/core/layout/form/components/group/country-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {CascadeSelectGroup} from '#/main/core/layout/form/components/group/cascade-select-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group.jsx'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'
import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {EmailGroup} from '#/main/core/layout/form/components/group/email-group.jsx'
import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'
import {FileGroup} from '#/main/core/layout/form/components/group/file-group.jsx'

// deprecated
// only used by claco-form
// todo : rewrite claco-form to use form.jsx
export const FormField = props => {
  switch (props.type) {
    case 'checkboxes':
      return (
        <CheckboxesGroup
          id={props.controlId}
          label={props.label}
          noLabel={props.noLabel}
          options={props.choices || []}
          value={props.value}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    case 'radio':
      return (
        <RadiosGroup
          id={props.controlId}
          label={props.label}
          noLabel={props.noLabel}
          options={props.choices || []}
          value={props.value || ''}
          disabled={props.disabled}
          error={props.error}
          onChange={value => props.onChange(value)}
        />
      )
    case 'select':
      if (props.choices && props.choices.filter(c => c.parent).length > 0) {
        return (
          <CascadeSelectGroup
            controlId={props.controlId}
            label={props.label}
            noLabel={props.noLabel}
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
            filterChoices={props.filterChoices}
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
      return (
        <NumberGroup
          id={props.controlId}
          label={props.label}
          hideLabel={props.noLabel}
          value={props.value}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'email':
      return (
        <EmailGroup
          id={props.controlId}
          label={props.label}
          hideLabel={props.noLabel}
          value={props.value}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'rich_text':
      return (
        <HtmlGroup
          id={props.controlId}
          label={props.label}
          noLabel={props.noLabel}
          value={props.value}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'date':
      return (
        <DateGroup
          id={props.controlId}
          label={props.label}
          noLabel={props.noLabel}
          value={props.value !== undefined && props.value !== null ? props.value.date || props.value || '' : ''}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
        />
      )
    case 'file':
      return (
        <FileGroup
          id={props.controlId}
          label={props.label}
          noLabel={props.noLabel}
          value={props.value}
          types={props.types}
          max={props.max}
          multiple={true}
          disabled={props.disabled}
          error={props.error}
          onChange={props.onChange}
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
  filterChoices: T.func,
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
