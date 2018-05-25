import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {t} from '#/main/core/translation'
import {Heading} from '#/main/core/layout/components/heading'
import {Sections, Section} from '#/main/core/layout/components/sections'
import {getTypeOrDefault} from '#/main/core/data'
import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/core/data/details/prop-types'
import {createDetailsDefinition} from '#/main/core/data/details/utils'

// todo there are big c/c from Form component but I don't know if we can do better

const DataDetailsField = props => {
  const typeDef = getTypeOrDefault(props.type)

  return (
    <div className="form-group">
      {!props.hideLabel &&
        <label className="control-label" htmlFor={props.name}>{props.label}</label>
      }

      <div id={props.name}>
        {!props.data &&
          <span className="data-details-empty">{t('empty_value')}</span>
        }

        {props.data && (typeDef.components.details ?
          React.createElement(typeDef.components.details, merge({}, props.options, {
            id: props.name,
            label: props.label,
            hideLabel: props.hideLabel,
            data: props.data
          }))
          :
          typeDef.render ? typeDef.render(props.data, props.options || {}) : props.data
        )}
      </div>
    </div>
  )
}

DataDetailsField.propTypes = {
  data: T.any,
  name: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  hideLabel: T.bool,
  displayed: T.bool,
  options: T.object
}

const DataDetails = props => {
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createDetailsDefinition(props.sections)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <div className={classes('data-details', props.className)}>
      {props.title &&
        <Heading level={props.level} displayLevel={props.displayLevel}>
          {props.title}
        </Heading>
      }

      {primarySections.map(primarySection =>
        <div key={primarySection.id} className="panel panel-default">
          <div className="panel-body">
            <Heading level={hLevel} displayed={false}>
              {primarySection.title}
            </Heading>

            {primarySection.fields.map(field =>
              <DataDetailsField
                {...field}
                key={field.name}
                data={field.calculated ? field.calculated(props.data) : get(props.data, field.name)}
              />
            )}
          </div>
        </div>
      )}

      {0 !== otherSections.length &&
        <Sections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? openedSection.id : undefined}
        >
          {otherSections.map(section =>
            <Section
              key={section.id}
              id={section.id}
              icon={section.icon}
              title={section.title}
            >
              {section.fields.map(field =>
                <DataDetailsField
                  {...field}
                  key={field.name}
                  data={field.calculated ? field.calculated(props.data) : get(props.data, field.name)}
                />
              )}
            </Section>
          )}
        </Sections>
      }
    </div>
  )
}

DataDetails.propTypes = {
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired,
  children: T.node
}

DataDetails.defaultProps = {
  level: 2,
  data: {}
}

export {
  DataDetails
}
