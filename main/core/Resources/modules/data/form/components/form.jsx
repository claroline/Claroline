import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {t} from '#/main/core/translation'

import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {SubSet} from '#/main/core/layout/form/components/fieldset/sub-set.jsx'
import {ToggleableSet} from '#/main/core/layout/form/components/fieldset/toggleable-set.jsx'

import {createFormDefinition} from '#/main/core/data/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/core/data/form/prop-types'
import {FormField} from '#/main/core/data/form/components/field.jsx'

const AdvancedSection = props =>
  <ToggleableSet
    showText={props.showText}
    hideText={props.hideText}
  >
    {props.fields.map(field =>
      <FormField
        key={field.name}
        {...field}
      />
    )}
  </ToggleableSet>

AdvancedSection.propTypes = {
  showText: T.string,
  hideText: T.string,
  fields: T.array.isRequired
}

AdvancedSection.defaultProps = {
  showText: t('show_advanced_options'),
  hideText: t('hide_advanced_options')
}

const FormWrapper = props => props.embedded ?
  <fieldset className={classes('form data-form', props.className)}>
    {props.children}
  </fieldset>
  :
  <form action="#" className={classes('form data-form', props.className)}>
    {props.children}
  </form>

FormWrapper.propTypes = {
  className: T.string,
  embedded: T.bool,
  children: T.node.isRequired
}

FormWrapper.defaultProps = {
  embedded: false
}

class Form extends Component {
  constructor(props) {
    super(props)

    this.warnPendingChanges = this.warnPendingChanges.bind(this)
  }

  warnPendingChanges(e) {
    if (this.props.pendingChanges) {
      // note: this is supposed to be the text displayed in the browser built-in
      // popup (see https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onbeforeunload#Example)
      // but it doesn't seem to be actually used in modern browsers. We use it
      // here because a string is needed anyway.
      e.returnValue = t('unsaved_changes_warning')

      return e.returnValue
    }
  }

  componentDidMount() {
    window.addEventListener('beforeunload', this.warnPendingChanges)
  }

  componentWillUnmount() {
    // todo warn also here
    // if client route has changed, it will not trigger before unload
    window.removeEventListener('beforeunload', this.warnPendingChanges)
  }

  renderFields(fields) {
    let rendered = []

    fields.map(field => {
      rendered.push(
        <FormField
          {...field}
          key={field.name}
          value={undefined !== field.calculated ? field.calculated : get(this.props.data, field.name)}
          disabled={this.props.disabled || field.disabled}
          validating={this.props.validating}
          error={get(this.props.errors, field.name)}
          updateProp={this.props.updateProp}
          setErrors={this.props.setErrors}
        />
      )

      if (field.linked && 0 !== field.linked.length) {
        rendered.push(
          <SubSet key={`${field.name}-subset`}>
            {this.renderFields(field.linked)}
          </SubSet>
        )
      }
    })

    return rendered
  }

  render() {
    const hLevel = this.props.level + (this.props.title ? 1 : 0)
    const sections = createFormDefinition(this.props.sections)

    const primarySection = 1 === sections.length ? sections[0] : sections.find(section => section.primary)
    const otherSections = sections.filter(section => section !== primarySection)
    const openedSection = otherSections.find(section => section.defaultOpened)

    return (
      <FormWrapper embedded={this.props.embedded} className={this.props.className}>
        {this.props.title &&
          React.createElement('h'+this.props.level, {}, this.props.title)
        }

        {primarySection &&
          <div className="form-primary-section panel panel-default">
            <fieldset className="panel-body">
              {React.createElement('h'+hLevel, {
                className: 'sr-only'
              }, primarySection.title)}

              {this.renderFields(primarySection.fields)}

              {primarySection.advanced &&
                <AdvancedSection {...primarySection.advanced} />
              }
            </fieldset>
          </div>
        }

        {0 !== otherSections.length &&
          <FormSections
            level={hLevel}
            defaultOpened={openedSection ? openedSection.id : undefined}
          >
            {otherSections.map(section =>
              <FormSection
                key={section.id}
                id={section.id}
                icon={section.icon}
                title={section.title}
                errors={this.props.errors}
                validating={this.props.validating}
              >
                {this.renderFields(section.fields)}

                {section.advanced &&
                  <AdvancedSection {...section.advanced} />
                }
              </FormSection>
            )}
          </FormSections>
        }

        {this.props.children &&
          <hr />
        }

        {this.props.children}
      </FormWrapper>
    )
  }
}

Form.propTypes = {
  /**
   * Is the form embed into another ?
   *
   * Permits to know if we use a <main> or a <section> tag.
   */
  embedded: T.bool,
  level: T.number,
  title: T.string,
  data: T.object,
  errors: T.object,
  validating: T.bool,
  pendingChanges: T.bool,
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  className: T.string,
  disabled: T.bool,
  children: T.node
}

Form.defaultProps = {
  disabled: false,
  data: {},
  level: 2,
  errors: {},
  validating: false,
  pendingChanges: false
}

export {
  Form
}